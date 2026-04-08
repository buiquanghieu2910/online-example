<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ExamActivityLog;
use App\Models\UserExam;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Throwable;

class MonitorController extends Controller
{
    private const TIMER_REDIS_CONNECTION = 'cache';

    public function activeAttempts(Request $request): JsonResponse
    {
        $teacherId = $request->user()->id;
        $validated = $request->validate([
            'class_id' => ['nullable', 'integer'],
            'exam_id' => ['nullable', 'integer'],
            'student_id' => ['nullable', 'integer'],
        ]);

        $scopedQuery = UserExam::query()
            ->where('status', 'in_progress')
            ->whereHas('exam.schoolClass.teachers', fn ($query) => $query->where('users.id', $teacherId))
            ->with([
                'user:id,name,username',
                'exam:id,title,duration,class_id',
                'exam.schoolClass:id,name',
            ]);

        $attemptsForFilters = (clone $scopedQuery)->get();
        $filters = [
            'classes' => $attemptsForFilters
                ->filter(fn (UserExam $attempt) => $attempt->exam?->schoolClass)
                ->map(fn (UserExam $attempt) => [
                    'id' => $attempt->exam->schoolClass->id,
                    'name' => $attempt->exam->schoolClass->name,
                ])->unique('id')->sortBy('name')->values(),
            'exams' => $attemptsForFilters
                ->filter(fn (UserExam $attempt) => $attempt->exam)
                ->map(fn (UserExam $attempt) => [
                    'id' => $attempt->exam->id,
                    'title' => $attempt->exam->title,
                ])->unique('id')->sortBy('title')->values(),
            'students' => $attemptsForFilters
                ->filter(fn (UserExam $attempt) => $attempt->user)
                ->map(fn (UserExam $attempt) => [
                    'id' => $attempt->user->id,
                    'name' => $attempt->user->name,
                    'username' => $attempt->user->username,
                ])->unique('id')->sortBy('name')->values(),
        ];

        $query = (clone $scopedQuery);
        if (! empty($validated['class_id'])) {
            $query->whereHas('exam', fn ($q) => $q->where('class_id', $validated['class_id']));
        }
        if (! empty($validated['exam_id'])) {
            $query->where('exam_id', $validated['exam_id']);
        }
        if (! empty($validated['student_id'])) {
            $query->where('user_id', $validated['student_id']);
        }

        $attempts = $query
            ->with([
                'user:id,name,username',
                'exam:id,title,duration,class_id',
                'exam.schoolClass:id,name',
                'latestActivityLog',
            ])
            ->orderByDesc('started_at')
            ->get();

        $items = $attempts->map(function (UserExam $attempt) {
            $remaining = $this->resolveTimeRemaining($attempt);

            return [
                'user_exam_id' => $attempt->id,
                'exam' => [
                    'id' => $attempt->exam?->id,
                    'title' => $attempt->exam?->title,
                    'class_name' => $attempt->exam?->schoolClass?->name,
                ],
                'student' => [
                    'id' => $attempt->user?->id,
                    'name' => $attempt->user?->name,
                    'username' => $attempt->user?->username,
                ],
                'started_at' => optional($attempt->started_at)->toDateTimeString(),
                'time_remaining' => $remaining,
                'latest_event' => $attempt->latestActivityLog?->event,
                'latest_event_at' => optional($attempt->latestActivityLog?->created_at)->toDateTimeString(),
            ];
        })->values();

        return response()->json([
            'data' => [
                'summary' => [
                    'active_attempts' => $items->count(),
                    'active_students' => $items->pluck('student.id')->filter()->unique()->count(),
                    'expiring_soon' => $items->where('time_remaining', '<=', 300)->count(),
                ],
                'filters' => $filters,
                'items' => $items,
            ],
        ]);
    }

    public function timeline(Request $request, UserExam $userExam): JsonResponse
    {
        $teacherId = $request->user()->id;
        $isAllowed = UserExam::query()
            ->whereKey($userExam->id)
            ->whereHas('exam.schoolClass.teachers', fn ($query) => $query->where('users.id', $teacherId))
            ->exists();

        if (! $isAllowed) {
            abort(403);
        }

        $userExam->load([
            'user:id,name,username',
            'exam:id,title',
        ]);

        $logs = ExamActivityLog::query()
            ->where('user_exam_id', $userExam->id)
            ->orderBy('created_at')
            ->get(['id', 'event', 'meta', 'ip_address', 'user_agent', 'created_at']);

        return response()->json([
            'data' => [
                'attempt' => [
                    'user_exam_id' => $userExam->id,
                    'student' => [
                        'id' => $userExam->user?->id,
                        'name' => $userExam->user?->name,
                        'username' => $userExam->user?->username,
                    ],
                    'exam' => [
                        'id' => $userExam->exam?->id,
                        'title' => $userExam->exam?->title,
                    ],
                    'started_at' => optional($userExam->started_at)->toDateTimeString(),
                    'completed_at' => optional($userExam->completed_at)->toDateTimeString(),
                    'status' => $userExam->status,
                ],
                'timeline' => $logs,
            ],
        ]);
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
}
