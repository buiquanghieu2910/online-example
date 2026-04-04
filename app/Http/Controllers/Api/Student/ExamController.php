<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Question;
use App\Models\UserExam;
use App\Services\IExamService;
use App\Services\IExamTakingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamController extends Controller
{
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
        $userExam = $this->examTakingService->startExam($request->user(), $exam);

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
            $userExam->update(['status' => 'in_progress', 'started_at' => now()]);
            $userExam = $userExam->fresh();
        } elseif (! $userExam->started_at) {
            $userExam->update(['started_at' => now()]);
            $userExam = $userExam->fresh();
        }

        $questions = $exam->questions()->with('answers')->orderBy('order')->get();
        $savedAnswers = $userExam->userAnswers()->pluck('answer_id', 'question_id')->toArray();
        $savedEssayAnswers = $userExam->userAnswers()->whereNotNull('essay_answer')->pluck('essay_answer', 'question_id')->toArray();

        $timeRemaining = (int) ($exam->duration * 60 - now()->diffInSeconds($userExam->started_at));

        return response()->json([
            'data' => [
                'exam' => [
                    'id' => $exam->id,
                    'title' => $exam->title,
                    'duration' => $exam->duration,
                ],
                'user_exam_id' => $userExam->id,
                'time_remaining' => max($timeRemaining, 0),
                'questions' => $questions,
                'saved_answers' => $savedAnswers,
                'saved_essay_answers' => $savedEssayAnswers,
            ],
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

        return response()->json([
            'message' => 'Nộp bài thành công.',
            'data' => [
                'result_id' => $completedExam->id,
                'redirect' => '/app/student/results/' . $completedExam->id,
            ],
        ]);
    }
}

