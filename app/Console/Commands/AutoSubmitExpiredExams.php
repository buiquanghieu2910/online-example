<?php

namespace App\Console\Commands;

use App\Models\ExamActivityLog;
use App\Models\UserExam;
use App\Services\IExamTakingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Throwable;

class AutoSubmitExpiredExams extends Command
{
    protected $signature = 'exams:auto-submit-expired';
    protected $description = 'Automatically submit in-progress exams that have reached time limit';

    private const TIMER_REDIS_CONNECTION = 'cache';

    public function __construct(private IExamTakingService $examTakingService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $submittedCount = 0;

        UserExam::query()
            ->where('status', 'in_progress')
            ->with(['exam:id,duration', 'userAnswers:id,user_exam_id,question_id,answer_id,essay_answer'])
            ->orderBy('id')
            ->chunkById(100, function ($attempts) use (&$submittedCount) {
                foreach ($attempts as $attempt) {
                    if ($this->resolveTimeRemaining($attempt) > 0) {
                        continue;
                    }

                    $freshAttempt = UserExam::query()->find($attempt->id);
                    if (! $freshAttempt || $freshAttempt->status !== 'in_progress') {
                        continue;
                    }

                    $answers = [];
                    foreach ($attempt->userAnswers as $userAnswer) {
                        if ($userAnswer->essay_answer !== null && $userAnswer->essay_answer !== '') {
                            $answers[$userAnswer->question_id] = $userAnswer->essay_answer;
                            continue;
                        }

                        if ($userAnswer->answer_id) {
                            $answers[$userAnswer->question_id] = $userAnswer->answer_id;
                        }
                    }

                    $completed = $this->examTakingService->submitExam($freshAttempt, $answers);
                    $this->forgetTimer($freshAttempt);
                    $this->logAutoSubmit($completed);
                    $submittedCount++;
                }
            });

        $this->info("Auto-submitted attempts: {$submittedCount}");

        return self::SUCCESS;
    }

    private function resolveTimeRemaining(UserExam $attempt): int
    {
        $maxSeconds = max(0, (int) ($attempt->exam?->duration ?? 0) * 60);

        try {
            $value = Redis::connection(self::TIMER_REDIS_CONNECTION)->get($this->timerKey($attempt));
            $remainingFromRedis = $this->parseRemainingFromRedis($value, $maxSeconds);
            if ($remainingFromRedis !== null) {
                return $remainingFromRedis;
            }
        } catch (Throwable) {
            // Fallback below.
        }

        if ($attempt->remaining_seconds !== null) {
            return max(0, min((int) $attempt->remaining_seconds, $maxSeconds));
        }

        if (! $attempt->started_at) {
            return $maxSeconds;
        }

        return max(0, (int) ($maxSeconds - now()->diffInSeconds($attempt->started_at)));
    }

    private function timerKey(UserExam $attempt): string
    {
        return "exam_timer:user:{$attempt->user_id}:exam:{$attempt->exam_id}:attempt:{$attempt->id}";
    }

    private function forgetTimer(UserExam $attempt): void
    {
        try {
            Redis::connection(self::TIMER_REDIS_CONNECTION)->del($this->timerKey($attempt));
        } catch (Throwable) {
            // No-op.
        }
    }

    private function parseRemainingFromRedis(mixed $value, int $maxSeconds): ?int
    {
        if ($value === null) {
            return null;
        }

        if (is_numeric($value)) {
            return max(0, min((int) $value, $maxSeconds));
        }

        $decoded = json_decode((string) $value, true);
        if (! is_array($decoded) || ! array_key_exists('remaining', $decoded)) {
            return null;
        }

        $remaining = (int) $decoded['remaining'];
        $syncedAt = isset($decoded['synced_at']) ? (int) $decoded['synced_at'] : now()->timestamp;
        $elapsed = max(0, now()->timestamp - $syncedAt);

        return max(0, min($remaining - $elapsed, $maxSeconds));
    }

    private function logAutoSubmit(UserExam $attempt): void
    {
        try {
            ExamActivityLog::create([
                'user_exam_id' => $attempt->id,
                'user_id' => $attempt->user_id,
                'exam_id' => $attempt->exam_id,
                'event' => 'exam_auto_submitted',
                'meta' => ['source' => 'scheduler', 'reason' => 'time_expired'],
            ]);
        } catch (Throwable) {
            // Do not block command flow if logging fails.
        }
    }
}
