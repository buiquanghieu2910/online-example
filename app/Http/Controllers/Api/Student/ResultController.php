<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\UserExam;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $results = UserExam::query()
            ->where('user_id', $request->user()->id)
            ->where('status', 'completed')
            ->with('exam:id,title')
            ->get()
            ->groupBy('exam_id')
            ->map(fn ($attempts) => $attempts->sortByDesc('completed_at')->first())
            ->sortByDesc('completed_at')
            ->values();

        return response()->json(['data' => $results]);
    }

    public function show(Request $request, UserExam $userExam): JsonResponse
    {
        if ($userExam->user_id !== $request->user()->id) {
            abort(403);
        }

        $latestAttempt = UserExam::query()
            ->where('user_id', $request->user()->id)
            ->where('exam_id', $userExam->exam_id)
            ->where('status', 'completed')
            ->orderByDesc('completed_at')
            ->orderByDesc('created_at')
            ->first();

        if ($latestAttempt && $latestAttempt->id !== $userExam->id) {
            return response()->json(['message' => 'Không tìm thấy kết quả bài thi.'], 404);
        }

        $userExam->load([
            'exam:id,title,duration',
            'userAnswers.question:id,question_text,question_type,points',
            'userAnswers.answer:id,answer_text,is_correct',
        ]);

        return response()->json(['data' => $userExam]);
    }
}

