<?php

namespace App\Services\Impl;

use App\Models\ExamActivityLog;
use App\Models\UserExam;
use App\Services\IExamMonitoringService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Redis;
use Throwable;

class ExamMonitoringServiceImpl implements IExamMonitoringService
{
    private const TIMER_REDIS_CONNECTION = 'cache';

    public function getActiveAttemptsForAdmin(array $filters = []): array
    {
        $scopedQuery = UserExam::query()
            ->where('status', 'in_progress')
            ->with([
                'user:id,name,username',
                'exam:id,title,duration,class_id',
                'exam.schoolClass:id,name',
            ]);

        return $this->buildActiveAttemptsPayload($scopedQuery, $filters);
    }

    public function getActiveAttemptsForTeacher(int $teacherId, array $filters = []): array
    {
        $scopedQuery = UserExam::query()
            ->where('status', 'in_progress')
            ->whereHas('exam.schoolClass.teachers', fn ($query) => $query->where('users.id', $teacherId))
            ->with([
                'user:id,name,username',
                'exam:id,title,duration,class_id',
                'exam.schoolClass:id,name',
            ]);

        return $this->buildActiveAttemptsPayload($scopedQuery, $filters);
    }

    public function getTimelineForAdmin(int $userExamId): ?array
    {
        $attempt = UserExam::query()
            ->with(['user:id,name,username', 'exam:id,title'])
            ->find($userExamId);

        if (! $attempt) {
            return null;
        }

        return $this->buildTimelinePayload($attempt);
    }

    public function getTimelineForTeacher(int $teacherId, int $userExamId): ?array
    {
        $attempt = UserExam::query()
            ->whereKey($userExamId)
            ->whereHas('exam.schoolClass.teachers', fn ($query) => $query->where('users.id', $teacherId))
            ->with(['user:id,name,username', 'exam:id,title'])
            ->first();

        if (! $attempt) {
            return null;
        }

        return $this->buildTimelinePayload($attempt);
    }

    private function buildActiveAttemptsPayload(Builder $scopedQuery, array $filters): array
    {
        $attemptsForFilters = (clone $scopedQuery)->get();
        $filterPayload = [
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
        if (! empty($filters['class_id'])) {
            $query->whereHas('exam', fn ($q) => $q->where('class_id', (int) $filters['class_id']));
        }
        if (! empty($filters['exam_id'])) {
            $query->where('exam_id', (int) $filters['exam_id']);
        }
        if (! empty($filters['student_id'])) {
            $query->where('user_id', (int) $filters['student_id']);
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

        return [
            'summary' => [
                'active_attempts' => $items->count(),
                'active_students' => $items->pluck('student.id')->filter()->unique()->count(),
                'expiring_soon' => $items->where('time_remaining', '<=', 300)->count(),
            ],
            'filters' => $filterPayload,
            'items' => $items,
        ];
    }

    private function buildTimelinePayload(UserExam $attempt): array
    {
        $logs = ExamActivityLog::query()
            ->where('user_exam_id', $attempt->id)
            ->orderBy('created_at')
            ->get(['id', 'event', 'meta', 'ip_address', 'user_agent', 'created_at']);

        return [
            'attempt' => [
                'user_exam_id' => $attempt->id,
                'student' => [
                    'id' => $attempt->user?->id,
                    'name' => $attempt->user?->name,
                    'username' => $attempt->user?->username,
                ],
                'exam' => [
                    'id' => $attempt->exam?->id,
                    'title' => $attempt->exam?->title,
                ],
                'started_at' => optional($attempt->started_at)->toDateTimeString(),
                'completed_at' => optional($attempt->completed_at)->toDateTimeString(),
                'status' => $attempt->status,
            ],
            'timeline' => $logs,
        ];
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
