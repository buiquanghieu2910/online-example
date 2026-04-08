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
            ->with('exam:id,title,pass_score')
            ->get()
            ->groupBy('exam_id')
            ->map(fn ($attempts) => $attempts->sortByDesc('completed_at')->first())
            ->sortByDesc('completed_at')
            ->values()
            ->map(fn (UserExam $attempt) => $this->toSummaryPayload($attempt));

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
            'exam:id,title,duration,pass_score',
            'userAnswers.question:id,question_text,question_type,points',
            'userAnswers.answer:id,answer_text,is_correct',
        ]);

        $totalPoints = (float) $userExam->userAnswers
            ->sum(fn ($item) => (float) ($item->question?->points ?? 0));
        $correctCount = (int) $userExam->userAnswers
            ->filter(fn ($item) => in_array($item->question?->question_type, ['multiple_choice', 'true_false'], true))
            ->filter(fn ($item) => (bool) $item->is_correct)
            ->count();

        return response()->json([
            'data' => [
                ...$userExam->toArray(),
                'pass_score' => (float) ($userExam->exam?->pass_score ?? 0),
                'is_passed' => $this->resolvePassed($userExam),
                'total_points' => $totalPoints,
                'correct_count' => $correctCount,
                'question_count' => (int) $userExam->userAnswers->count(),
            ],
        ]);
    }

    private function toSummaryPayload(UserExam $attempt): array
    {
        return [
            'id' => $attempt->id,
            'score' => $attempt->score,
            'grading_status' => $attempt->grading_status,
            'completed_at' => optional($attempt->completed_at)->toDateTimeString(),
            'exam' => [
                'id' => $attempt->exam?->id,
                'title' => $attempt->exam?->title,
                'pass_score' => (float) ($attempt->exam?->pass_score ?? 0),
            ],
            'is_passed' => $this->resolvePassed($attempt),
        ];
    }

    private function resolvePassed(UserExam $attempt): ?bool
    {
        if ($attempt->score === null) {
            return null;
        }

        $passScore = (float) ($attempt->exam?->pass_score ?? 0);

        return (float) $attempt->score >= $passScore;
    }
}
