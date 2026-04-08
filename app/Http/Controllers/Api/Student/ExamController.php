<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamActivityLog;
use App\Models\Question;
use App\Models\UserExam;
use App\Services\IExamService;
use App\Services\IExamTakingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Throwable;

class ExamController extends Controller
{
    private const TIMER_TTL_SECONDS = 604800;
    private const TIMER_REDIS_CONNECTION = 'cache';

    public function __construct(
        private IExamService $examService,
        private IExamTakingService $examTakingService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $exams = $this->examService->getAssignedExamsForUser($user->id);

        $items = $exams->map(function (Exam $exam) use ($user) {
            $inProgressAttempt = UserExam::where('user_id', $user->id)
                ->where('exam_id', $exam->id)
                ->where('status', 'in_progress')
                ->latest('id')
                ->first();

            $notStartedAttempt = UserExam::where('user_id', $user->id)
                ->where('exam_id', $exam->id)
                ->where('status', 'not_started')
                ->latest('id')
                ->first();

            $latestCompletedAttempt = UserExam::where('user_id', $user->id)
                ->where('exam_id', $exam->id)
                ->where('status', 'completed')
                ->orderByDesc('completed_at')
                ->orderByDesc('id')
                ->first();

            return [
                'id' => $exam->id,
                'title' => $exam->title,
                'description' => $exam->description,
                'duration' => $exam->duration,
                'is_active' => (bool) $exam->is_active,
                'in_progress_attempt_id' => $inProgressAttempt?->id,
                'has_new_attempt' => (bool) $notStartedAttempt,
                'latest_completed_attempt_id' => $latestCompletedAttempt?->id,
                'latest_score' => $latestCompletedAttempt?->score,
                'grading_status' => $latestCompletedAttempt?->grading_status,
            ];
        })->values();

        return response()->json(['data' => $items]);
    }

    public function show(Request $request, Exam $exam): JsonResponse
    {
        if (! $exam->is_active || ! $exam->assignedUsers()->where('users.id', $request->user()->id)->exists()) {
            return response()->json(['message' => 'Kỳ thi không khả dụng.'], 403);
        }

        $activeAttempt = $this->examTakingService->getActiveExam($request->user(), $exam);

        return response()->json([
            'data' => [
                'id' => $exam->id,
                'title' => $exam->title,
                'description' => $exam->description,
                'duration' => $exam->duration,
                'active_attempt_id' => $activeAttempt?->id,
            ],
        ]);
    }

    public function start(Request $request, Exam $exam): JsonResponse
    {
        if (! $exam->is_active || ! $exam->assignedUsers()->where('users.id', $request->user()->id)->exists()) {
            return response()->json(['message' => 'Kỳ thi không khả dụng.'], 403);
        }

        $userExam = $this->examTakingService->startExam($request->user(), $exam);
        $this->logExamEvent($request, $userExam, 'exam_started', [
            'status' => $userExam->status,
        ]);

        return response()->json([
            'data' => [
                'user_exam_id' => $userExam->id,
                'redirect' => '/app/student/exams/' . $exam->id . '/take',
            ],
        ]);
    }

    public function take(Request $request, Exam $exam): JsonResponse
    {
        $userExam = $this->examTakingService->getActiveExam($request->user(), $exam);
        if (! $userExam) {
            return response()->json(['message' => 'Không tìm thấy bài thi đang thực hiện.'], 404);
        }

        if ($userExam->status === 'not_started') {
            $userExam->update([
                'status' => 'in_progress',
                'started_at' => now(),
                'remaining_seconds' => $userExam->remaining_seconds ?? $this->maxDurationSeconds($exam),
            ]);
            $userExam = $userExam->fresh();
        } elseif (! $userExam->started_at) {
            $userExam->update(['started_at' => now()]);
            $userExam = $userExam->fresh();
        }

        $questions = $exam->questions()->with('answers')->orderBy('order')->get();
        $savedAnswers = $userExam->userAnswers()->pluck('answer_id', 'question_id')->toArray();
        $savedEssayAnswers = $userExam->userAnswers()->whereNotNull('essay_answer')->pluck('essay_answer', 'question_id')->toArray();

        $timeRemaining = $this->getTimeRemaining($userExam, $exam);
        $this->logExamEvent($request, $userExam, 'exam_opened', [
            'time_remaining' => $timeRemaining,
        ]);

        return response()->json([
            'data' => [
                'exam' => [
                    'id' => $exam->id,
                    'title' => $exam->title,
                    'duration' => $exam->duration,
                ],
                'user_exam_id' => $userExam->id,
                'time_remaining' => $timeRemaining,
                'questions' => $questions,
                'saved_answers' => $savedAnswers,
                'saved_essay_answers' => $savedEssayAnswers,
            ],
        ]);
    }

    public function attemptStatus(Request $request, Exam $exam): JsonResponse
    {
        if (! $exam->assignedUsers()->where('users.id', $request->user()->id)->exists()) {
            return response()->json(['message' => 'Kỳ thi không khả dụng.'], 403);
        }

        $activeAttempt = $this->examTakingService->getActiveExam($request->user(), $exam);
        if ($activeAttempt) {
            return response()->json([
                'data' => [
                    'status' => 'in_progress',
                    'user_exam_id' => $activeAttempt->id,
                    'time_remaining' => $this->getTimeRemaining($activeAttempt, $exam),
                ],
            ]);
        }

        $latestCompletedAttempt = UserExam::query()
            ->where('user_id', $request->user()->id)
            ->where('exam_id', $exam->id)
            ->where('status', 'completed')
            ->orderByDesc('completed_at')
            ->orderByDesc('id')
            ->first();

        if ($latestCompletedAttempt) {
            return response()->json([
                'data' => [
                    'status' => 'completed',
                    'user_exam_id' => $latestCompletedAttempt->id,
                    'redirect' => '/app/student/results/' . $latestCompletedAttempt->id,
                ],
            ]);
        }

        return response()->json([
            'data' => [
                'status' => 'not_found',
            ],
        ], 404);
    }

    public function syncTimer(Request $request, Exam $exam): JsonResponse
    {
        $validated = $request->validate([
            'time_remaining' => ['required', 'integer', 'min:0'],
        ]);

        $userExam = $this->examTakingService->getActiveExam($request->user(), $exam);
        if (! $userExam) {
            return response()->json(['message' => 'Không tìm thấy bài thi đang thực hiện.'], 404);
        }

        $currentRemaining = $this->getTimeRemaining($userExam, $exam);
        $clientRemaining = min($validated['time_remaining'], $this->maxDurationSeconds($exam));
        $nextRemaining = min($clientRemaining, $currentRemaining);
        $this->persistTimeRemaining($userExam, $nextRemaining);

        return response()->json([
            'message' => 'Đã đồng bộ thời gian.',
            'data' => ['time_remaining' => $nextRemaining],
        ]);
    }

    public function autosave(Request $request, Exam $exam): JsonResponse
    {
        $userExam = $this->examTakingService->getActiveExam($request->user(), $exam);
        if (! $userExam) {
            return response()->json(['message' => 'Không tìm thấy bài thi.'], 404);
        }

        $answers = $request->input('answers', []);

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
        }

        $this->logExamEvent($request, $userExam, 'exam_autosaved', [
            'answered_count' => count(array_filter($answers, fn ($answer) => $answer !== null && $answer !== '')),
        ]);

        return response()->json(['message' => 'Đã lưu tạm bài làm.']);
    }

    public function submit(Request $request, Exam $exam): JsonResponse
    {
        $userExam = $this->examTakingService->getActiveExam($request->user(), $exam);
        if (! $userExam) {
            return response()->json(['message' => 'Không tìm thấy bài thi đang thực hiện.'], 404);
        }

        $answers = $request->input('answers', []);
        $completedExam = $this->examTakingService->submitExam($userExam, $answers);
        $this->forgetTimer($userExam);
        $this->logExamEvent($request, $completedExam, 'exam_submitted', [
            'status' => $completedExam->status,
            'score' => $completedExam->score,
            'grading_status' => $completedExam->grading_status,
        ]);

        return response()->json([
            'message' => 'Nộp bài thành công.',
            'data' => [
                'result_id' => $completedExam->id,
                'redirect' => '/app/student/results/' . $completedExam->id,
            ],
        ]);
    }

    private function timerKey(UserExam $userExam): string
    {
        return "exam_timer:user:{$userExam->user_id}:exam:{$userExam->exam_id}:attempt:{$userExam->id}";
    }

    private function maxDurationSeconds(Exam $exam): int
    {
        return max(0, (int) $exam->duration * 60);
    }

    private function getTimeRemaining(UserExam $userExam, Exam $exam): int
    {
        $maxDurationSeconds = $this->maxDurationSeconds($exam);

        try {
            $value = Redis::connection(self::TIMER_REDIS_CONNECTION)->get($this->timerKey($userExam));
            $remainingFromRedis = $this->parseRemainingFromRedis($value, $maxDurationSeconds);
            if ($remainingFromRedis !== null) {
                // Migrate old numeric timer payloads to snapshot format.
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

    private function forgetTimer(UserExam $userExam): void
    {
        try {
            Redis::connection(self::TIMER_REDIS_CONNECTION)->del($this->timerKey($userExam));
        } catch (Throwable) {
            // No-op.
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

    private function logExamEvent(Request $request, UserExam $userExam, string $event, array $meta = []): void
    {
        try {
            ExamActivityLog::create([
                'user_exam_id' => $userExam->id,
                'user_id' => $userExam->user_id,
                'exam_id' => $userExam->exam_id,
                'event' => $event,
                'meta' => $meta ?: null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } catch (Throwable) {
            // Do not block the exam flow if logging fails.
        }
    }
}
