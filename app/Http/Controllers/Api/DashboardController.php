<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamActivityLog;
use App\Models\Question;
use App\Models\SchoolClass;
use App\Models\User;
use App\Models\UserAnswer;
use App\Models\UserExam;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function overview(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role === 'student') {
            return response()->json([
                'data' => [
                    'stats' => [
                        ['key' => 'assigned_exams', 'label' => 'Bài thi được giao', 'value' => $user->assignedExams()->count()],
                        ['key' => 'completed', 'label' => 'Đã hoàn thành', 'value' => UserExam::query()->where('user_id', $user->id)->where('status', 'completed')->count()],
                        ['key' => 'in_progress', 'label' => 'Đang làm', 'value' => UserExam::query()->where('user_id', $user->id)->where('status', 'in_progress')->count()],
                    ],
                ],
            ]);
        }

        $validated = $request->validate([
            'days' => ['nullable', 'integer', 'in:7,14,30,60'],
            'class_id' => ['nullable', 'integer'],
            'exam_id' => ['nullable', 'integer'],
        ]);

        $days = (int) ($validated['days'] ?? 14);
        $fromDate = now()->startOfDay()->subDays($days - 1);

        $classOptions = $this->classOptions($user);
        $examOptions = $this->examOptions($user, $validated['class_id'] ?? null);
        $allowedClassIds = $classOptions->pluck('id')->all();
        $allowedExamIds = $examOptions->pluck('id')->all();

        $classId = isset($validated['class_id']) && in_array((int) $validated['class_id'], $allowedClassIds, true)
            ? (int) $validated['class_id']
            : null;
        $examId = isset($validated['exam_id']) && in_array((int) $validated['exam_id'], $allowedExamIds, true)
            ? (int) $validated['exam_id']
            : null;

        $stats = $this->buildStats($user, $classId, $examId);
        $alerts = $this->buildAlerts($user, $classId, $examId);
        $trend = $this->buildTrend($user, $fromDate, $days, $classId, $examId);
        $questionQuality = $this->buildQuestionQuality($user, $fromDate, $classId, $examId);

        return response()->json([
            'data' => [
                'stats' => $stats,
                'alerts' => $alerts,
                'trend' => $trend,
                'question_quality' => $questionQuality,
                'filters' => [
                    'days' => $days,
                    'class_id' => $classId,
                    'exam_id' => $examId,
                    'classes' => $classOptions->map(fn ($item) => ['id' => $item->id, 'name' => $item->name])->values(),
                    'exams' => $examOptions->map(fn ($item) => ['id' => $item->id, 'title' => $item->title])->values(),
                ],
            ],
        ]);
    }

    private function classOptions(User $user)
    {
        if ($user->role === 'admin') {
            return SchoolClass::query()->orderBy('name')->get(['id', 'name']);
        }

        return $user->teachingClasses()->orderBy('classes.name')->get(['classes.id as id', 'classes.name']);
    }

    private function examOptions(User $user, ?int $classId)
    {
        $query = Exam::query()->orderBy('title');

        if ($user->role === 'teacher') {
            $teacherClassIds = $user->teachingClasses()->pluck('classes.id');
            $query->whereIn('class_id', $teacherClassIds);
        }

        if ($classId) {
            $query->where('class_id', $classId);
        }

        return $query->get(['id', 'title', 'class_id']);
    }

    private function buildStats(User $user, ?int $classId, ?int $examId): array
    {
        if ($user->role === 'admin') {
            return [
                ['key' => 'users', 'label' => 'Người dùng', 'value' => User::query()->count()],
                ['key' => 'classes', 'label' => 'Lớp học', 'value' => SchoolClass::query()->count()],
                ['key' => 'exams', 'label' => 'Bài thi', 'value' => $this->scopedExamQuery($user, $classId, $examId)->count()],
            ];
        }

        return [
            ['key' => 'classes', 'label' => 'Lớp đang dạy', 'value' => $user->teachingClasses()->count()],
            ['key' => 'students', 'label' => 'Học sinh phụ trách', 'value' => $user->students()->count()],
            ['key' => 'exams', 'label' => 'Bài thi đang quản lý', 'value' => $this->scopedExamQuery($user, $classId, $examId)->count()],
        ];
    }

    private function buildAlerts(User $user, ?int $classId, ?int $examId): array
    {
        $pendingGrading = $this->scopedUserExamQuery($user, $classId, $examId)
            ->where('status', 'completed')
            ->where('grading_status', 'pending_review')
            ->count();

        $activeAttempts = $this->scopedUserExamQuery($user, $classId, $examId)
            ->where('status', 'in_progress')
            ->count();

        $expiringSoon = $this->scopedUserExamQuery($user, $classId, $examId)
            ->where('status', 'in_progress')
            ->whereNotNull('remaining_seconds')
            ->where('remaining_seconds', '<=', 300)
            ->count();

        $autoSubmittedToday = $this->scopedExamActivityQuery($user, $classId, $examId)
            ->where('event', 'exam_auto_submitted')
            ->whereDate('created_at', now()->toDateString())
            ->count();

        $alerts = [];

        if ($pendingGrading > 0) {
            $alerts[] = [
                'key' => 'pending_grading',
                'label' => 'Bài chờ chấm',
                'value' => $pendingGrading,
                'severity' => 'warn',
                'route' => $user->role === 'admin' ? '/app/admin/grading/pending' : '/app/teacher/exams',
            ];
        }

        if ($expiringSoon > 0) {
            $alerts[] = [
                'key' => 'expiring_soon',
                'label' => 'Ca thi sắp hết giờ',
                'value' => $expiringSoon,
                'severity' => 'danger',
                'route' => $user->role === 'admin' ? '/app/admin/monitor' : '/app/teacher/monitor',
            ];
        }

        if ($activeAttempts > 0) {
            $alerts[] = [
                'key' => 'active_attempts',
                'label' => 'Học sinh đang thi',
                'value' => $activeAttempts,
                'severity' => 'info',
                'route' => $user->role === 'admin' ? '/app/admin/monitor' : '/app/teacher/monitor',
            ];
        }

        if ($autoSubmittedToday > 0) {
            $alerts[] = [
                'key' => 'auto_submitted',
                'label' => 'Tự nộp trong hôm nay',
                'value' => $autoSubmittedToday,
                'severity' => 'secondary',
                'route' => $user->role === 'admin' ? '/app/admin/monitor' : '/app/teacher/monitor',
            ];
        }

        return $alerts;
    }

    private function buildTrend(User $user, Carbon $fromDate, int $days, ?int $classId, ?int $examId): array
    {
        $rows = $this->scopedUserExamQuery($user, $classId, $examId)
            ->join('exams', 'exams.id', '=', 'user_exams.exam_id')
            ->where('user_exams.status', 'completed')
            ->whereDate('user_exams.completed_at', '>=', $fromDate->toDateString())
            ->selectRaw('DATE(user_exams.completed_at) as date')
            ->selectRaw('COUNT(user_exams.id) as attempts')
            ->selectRaw('AVG(user_exams.score) as avg_score')
            ->selectRaw('SUM(CASE WHEN user_exams.score IS NOT NULL AND user_exams.score >= exams.pass_score THEN 1 ELSE 0 END) as passed')
            ->groupBy(DB::raw('DATE(user_exams.completed_at)'))
            ->orderBy(DB::raw('DATE(user_exams.completed_at)'))
            ->get()
            ->keyBy('date');

        $series = [];
        $maxAttempts = 0;
        for ($i = 0; $i < $days; $i++) {
            $date = $fromDate->copy()->addDays($i)->toDateString();
            $row = $rows->get($date);

            $attempts = (int) ($row->attempts ?? 0);
            $avgScore = $row && $row->avg_score !== null ? round((float) $row->avg_score, 2) : null;
            $passRate = $attempts > 0 ? round(((int) ($row->passed ?? 0) * 100) / $attempts, 2) : null;

            $maxAttempts = max($maxAttempts, $attempts);
            $series[] = [
                'date' => $date,
                'label' => Carbon::parse($date)->format('d/m'),
                'attempts' => $attempts,
                'avg_score' => $avgScore,
                'pass_rate' => $passRate,
            ];
        }

        return [
            'series' => $series,
            'max_attempts' => $maxAttempts,
        ];
    }

    private function buildQuestionQuality(User $user, Carbon $fromDate, ?int $classId, ?int $examId): array
    {
        $rows = UserAnswer::query()
            ->join('questions', 'questions.id', '=', 'user_answers.question_id')
            ->join('exams', 'exams.id', '=', 'questions.exam_id')
            ->join('user_exams', 'user_exams.id', '=', 'user_answers.user_exam_id')
            ->leftJoin('classes', 'classes.id', '=', 'exams.class_id')
            ->when($user->role === 'teacher', function ($query) use ($user) {
                $query->join('class_teacher', 'class_teacher.class_id', '=', 'exams.class_id')
                    ->where('class_teacher.teacher_id', $user->id);
            })
            ->whereIn('questions.question_type', ['multiple_choice', 'true_false'])
            ->where('user_exams.status', 'completed')
            ->whereDate('user_exams.completed_at', '>=', $fromDate->toDateString())
            ->when($classId, fn ($query) => $query->where('exams.class_id', $classId))
            ->when($examId, fn ($query) => $query->where('exams.id', $examId))
            ->groupBy('questions.id', 'questions.question_text', 'exams.title', 'classes.name')
            ->selectRaw('questions.id as question_id')
            ->selectRaw('questions.question_text')
            ->selectRaw('exams.title as exam_title')
            ->selectRaw('classes.name as class_name')
            ->selectRaw('COUNT(user_answers.id) as attempts')
            ->selectRaw('SUM(CASE WHEN user_answers.is_correct = true THEN 1 ELSE 0 END) as correct_count')
            ->selectRaw('SUM(CASE WHEN user_answers.answer_id IS NULL THEN 1 ELSE 0 END) as blank_count')
            ->havingRaw('COUNT(user_answers.id) >= 5')
            ->get()
            ->map(function ($row) {
                $attempts = max(1, (int) $row->attempts);

                return [
                    'question_id' => (int) $row->question_id,
                    'question_text' => $row->question_text,
                    'exam_title' => $row->exam_title,
                    'class_name' => $row->class_name,
                    'attempts' => (int) $row->attempts,
                    'correct_rate' => round(((int) $row->correct_count * 100) / $attempts, 2),
                    'blank_rate' => round(((int) $row->blank_count * 100) / $attempts, 2),
                ];
            });

        return [
            'hardest' => $rows->sortBy('correct_rate')->take(5)->values(),
            'easiest' => $rows->sortByDesc('correct_rate')->take(5)->values(),
            'most_blank' => $rows->sortByDesc('blank_rate')->take(5)->values(),
        ];
    }

    private function scopedExamQuery(User $user, ?int $classId, ?int $examId): Builder
    {
        return Exam::query()
            ->when($user->role === 'teacher', function ($query) use ($user) {
                $query->whereHas('schoolClass.teachers', fn ($q) => $q->where('users.id', $user->id));
            })
            ->when($classId, fn ($query) => $query->where('class_id', $classId))
            ->when($examId, fn ($query) => $query->where('id', $examId));
    }

    private function scopedUserExamQuery(User $user, ?int $classId, ?int $examId): Builder
    {
        return UserExam::query()
            ->when($user->role === 'teacher', function ($query) use ($user) {
                $query->whereHas('exam.schoolClass.teachers', fn ($q) => $q->where('users.id', $user->id));
            })
            ->when($classId, fn ($query) => $query->whereHas('exam', fn ($q) => $q->where('class_id', $classId)))
            ->when($examId, fn ($query) => $query->where('exam_id', $examId));
    }

    private function scopedExamActivityQuery(User $user, ?int $classId, ?int $examId): Builder
    {
        return ExamActivityLog::query()
            ->when($user->role === 'teacher', function ($query) use ($user) {
                $query->whereHas('exam.schoolClass.teachers', fn ($q) => $q->where('users.id', $user->id));
            })
            ->when($classId, fn ($query) => $query->whereHas('exam', fn ($q) => $q->where('class_id', $classId)))
            ->when($examId, fn ($query) => $query->where('exam_id', $examId));
    }
}
