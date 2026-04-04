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
        $userExams = UserExam::query()
            ->where('exam_id', $exam->id)
            ->where('status', 'completed')
            ->where('grading_status', 'pending_review')
            ->with('user:id,name,username')
            ->orderByDesc('completed_at')
            ->get();

        return response()->json([
            'data' => [
                'exam' => ['id' => $exam->id, 'title' => $exam->title],
                'user_exams' => $userExams,
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
}


