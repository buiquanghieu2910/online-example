<?php

namespace App\Services\Impl;

use App\Models\Exam;
use App\Models\ExamActivityLog;
use App\Models\Question;
use App\Models\UserExam;
use App\Services\IExamSessionService;
use Illuminate\Support\Facades\Redis;
use Throwable;

class ExamSessionServiceImpl implements IExamSessionService
{
    private const TIMER_TTL_SECONDS = 604800;
    private const TIMER_REDIS_CONNECTION = 'cache';

    public function getTimeRemaining(UserExam $userExam, Exam $exam): int
    {
        $maxDurationSeconds = $this->maxDurationSeconds($exam);

        try {
            $value = Redis::connection(self::TIMER_REDIS_CONNECTION)->get($this->timerKey($userExam));
            $remainingFromRedis = $this->parseRemainingFromRedis($value, $maxDurationSeconds);
            if ($remainingFromRedis !== null) {
                if (is_numeric($value)) {
                    $this->persistTimeRemaining($userExam, $remainingFromRedis);
                }

                return $remainingFromRedis;
            }

            if ($userExam->remaining_seconds !== null) {
                $fallback = max(0, min((int) $userExam->remaining_seconds, $maxDurationSeconds));
            } else {
                $fallback = $userExam->started_at
                    ? (int) ($maxDurationSeconds - now()->diffInSeconds($userExam->started_at))
                    : $maxDurationSeconds;
                $fallback = max(0, min($fallback, $maxDurationSeconds));
            }

            $this->persistTimeRemaining($userExam, $fallback);

            return $fallback;
        } catch (Throwable) {
            if ($userExam->remaining_seconds !== null) {
                return max(0, min((int) $userExam->remaining_seconds, $maxDurationSeconds));
            }

            if (! $userExam->started_at) {
                return $maxDurationSeconds;
            }

            return max(0, (int) ($maxDurationSeconds - now()->diffInSeconds($userExam->started_at)));
        }
    }

    public function syncTimer(UserExam $userExam, Exam $exam, int $clientRemaining): int
    {
        $currentRemaining = $this->getTimeRemaining($userExam, $exam);
        $safeClientRemaining = min(max(0, $clientRemaining), $this->maxDurationSeconds($exam));
        $nextRemaining = min($safeClientRemaining, $currentRemaining);
        $this->persistTimeRemaining($userExam, $nextRemaining);

        return $nextRemaining;
    }

    public function autosaveAnswers(UserExam $userExam, array $answers): int
    {
        $savedCount = 0;

        foreach ($answers as $questionId => $answer) {
            if ($answer === null || $answer === '') {
                continue;
            }

            $question = Question::find($questionId);
            if (! $question) {
                continue;
            }

            $answerData = [];
            if ($question->question_type === 'essay') {
                $answerData['essay_answer'] = $answer;
            } else {
                $answerData['answer_id'] = $answer;
                $answerModel = $question->answers()->find($answer);
                if ($answerModel) {
                    $answerData['is_correct'] = $answerModel->is_correct;
                }
            }

            $userExam->userAnswers()->updateOrCreate(['question_id' => $questionId], $answerData);
            $savedCount++;
        }

        return $savedCount;
    }

    public function forgetTimer(UserExam $userExam): void
    {
        try {
            Redis::connection(self::TIMER_REDIS_CONNECTION)->del($this->timerKey($userExam));
        } catch (Throwable) {
            // No-op.
        }
    }

    public function logExamEvent(
        UserExam $userExam,
        string $event,
        array $meta = [],
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): void {
        try {
            ExamActivityLog::create([
                'user_exam_id' => $userExam->id,
                'user_id' => $userExam->user_id,
                'exam_id' => $userExam->exam_id,
                'event' => $event,
                'meta' => $meta ?: null,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ]);
        } catch (Throwable) {
            // Do not block the exam flow if logging fails.
        }
    }

    private function timerKey(UserExam $userExam): string
    {
        return "exam_timer:user:{$userExam->user_id}:exam:{$userExam->exam_id}:attempt:{$userExam->id}";
    }

    private function maxDurationSeconds(Exam $exam): int
    {
        return max(0, (int) $exam->duration * 60);
    }

    private function persistTimeRemaining(UserExam $userExam, int $timeRemaining): void
    {
        $safeRemaining = max(0, $timeRemaining);
        $snapshot = json_encode([
            'remaining' => $safeRemaining,
            'synced_at' => now()->timestamp,
        ]);

        try {
            Redis::connection(self::TIMER_REDIS_CONNECTION)->setex(
                $this->timerKey($userExam),
                self::TIMER_TTL_SECONDS,
                $snapshot !== false ? $snapshot : $safeRemaining
            );
        } catch (Throwable) {
            // Gracefully fall back to DB-only timing if Redis is unavailable.
        }

        if ((int) ($userExam->remaining_seconds ?? -1) !== $safeRemaining) {
            UserExam::query()->whereKey($userExam->id)->update(['remaining_seconds' => $safeRemaining]);
            $userExam->remaining_seconds = $safeRemaining;
        }
    }

    private function parseRemainingFromRedis(mixed $value, int $maxDurationSeconds): ?int
    {
        if ($value === null) {
            return null;
        }

        if (is_numeric($value)) {
            return max(0, min((int) $value, $maxDurationSeconds));
        }

        $decoded = json_decode((string) $value, true);
        if (! is_array($decoded) || ! array_key_exists('remaining', $decoded)) {
            return null;
        }

        $remaining = (int) $decoded['remaining'];
        $syncedAt = isset($decoded['synced_at']) ? (int) $decoded['synced_at'] : now()->timestamp;
        $elapsed = max(0, now()->timestamp - $syncedAt);

        return max(0, min($remaining - $elapsed, $maxDurationSeconds));
    }
}
