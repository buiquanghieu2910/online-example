<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\UserExam;
use App\Services\IExamTakingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GradingController extends Controller
{
    public function __construct(private IExamTakingService $examTakingService)
    {
    }

    public function pending(): JsonResponse
    {
        $exams = Exam::query()
            ->whereHas('userExams', function ($query) {
                $query->where('status', 'completed')
                    ->where('grading_status', 'pending_review');
            })
            ->withCount([
                'userExams as pending_count' => function ($query) {
                    $query->where('status', 'completed')->where('grading_status', 'pending_review');
                },
            ])
            ->orderByDesc('pending_count')
            ->get(['id', 'title']);

        return response()->json(['data' => $exams]);
    }

    public function examUsers(Exam $exam): JsonResponse
    {
        $students = $exam->assignedUsers()
            ->where('users.role', 'student')
            ->orderBy('users.name')
            ->get(['users.id', 'users.name', 'users.username']);

        $studentIds = $students->pluck('id')->all();
        $latestAttemptIds = UserExam::query()
            ->where('exam_id', $exam->id)
            ->whereIn('user_id', $studentIds)
            ->selectRaw('MAX(id) as id, user_id')
            ->groupBy('user_id')
            ->pluck('id')
            ->all();

        $latestAttempts = UserExam::query()
            ->whereIn('id', $latestAttemptIds)
            ->get()
            ->keyBy('user_id');

        $attemptCounts = UserExam::query()
            ->where('exam_id', $exam->id)
            ->whereIn('user_id', $studentIds)
            ->selectRaw('user_id, COUNT(*) as attempts_count')
            ->groupBy('user_id')
            ->pluck('attempts_count', 'user_id');

        $items = $students->map(function ($student) use ($latestAttempts, $attemptCounts) {
            $latestAttempt = $latestAttempts->get($student->id);

            return [
                'user' => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'username' => $student->username,
                ],
                'latest_user_exam_id' => $latestAttempt?->id,
                'status' => $latestAttempt?->status ?? 'not_started',
                'grading_status' => $latestAttempt?->grading_status,
                'score' => $latestAttempt?->score,
                'started_at' => optional($latestAttempt?->started_at)?->toDateTimeString(),
                'completed_at' => optional($latestAttempt?->completed_at)?->toDateTimeString(),
                'attempts_count' => (int) ($attemptCounts[$student->id] ?? 0),
                'can_grade' => $latestAttempt?->status === 'completed' && $latestAttempt?->grading_status === 'pending_review',
                'can_reset' => $latestAttempt?->status === 'completed',
            ];
        })->values();

        return response()->json([
            'data' => [
                'exam' => ['id' => $exam->id, 'title' => $exam->title],
                'users' => $items,
                'pending_count' => $items->where('can_grade', true)->count(),
            ],
        ]);
    }

    public function show(UserExam $userExam): JsonResponse
    {
        $userExam->load([
            'user:id,name,username',
            'exam:id,title',
            'userAnswers.question:id,exam_id,question_text,question_type,points',
            'userAnswers.answer:id,answer_text,is_correct',
        ]);

        return response()->json(['data' => $userExam]);
    }

    public function submit(Request $request, UserExam $userExam): JsonResponse
    {
        $rules = [
            'grades' => ['required', 'array'],
        ];

        foreach ($request->input('grades', []) as $questionId => $value) {
            $question = $userExam->exam->questions()->find($questionId);
            if (! $question) {
                continue;
            }

            $rules['grades.' . $questionId . '.score'] = ['required', 'numeric', 'min:0', 'max:' . $question->points];
            $rules['grades.' . $questionId . '.feedback'] = ['nullable', 'string'];
        }

        $validated = $request->validate($rules);

        $result = $this->examTakingService->gradeEssayAnswers($userExam->id, $validated['grades']);

        if (! $result) {
            return response()->json(['message' => 'Không thể lưu kết quả chấm bài.'], 422);
        }

        return response()->json(['message' => 'Lưu điểm chấm bài thành công.']);
    }

    public function reset(UserExam $userExam): JsonResponse
    {
        $result = $this->examTakingService->resetExamForUser($userExam->id);

        if (! $result) {
            return response()->json(['message' => 'Không thể cho học sinh làm lại bài thi.'], 422);
        }

        return response()->json(['message' => 'Đã cho phép học sinh làm lại bài thi.']);
    }
}
