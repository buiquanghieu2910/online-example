<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\IExamService;
use App\Services\IExamTakingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExamController extends Controller
{
    public function __construct(
        private IExamService $examService,
        private IExamTakingService $examTakingService
    ) {}

    public function index()
    {
        $exams = $this->examService->getAssignedExamsForUser(Auth::id());
        
        // Get latest attempt for each exam
        foreach ($exams as $exam) {
            // Check if there's an in-progress attempt
            $inProgressAttempt = \App\Models\UserExam::where('user_id', Auth::id())
                ->where('exam_id', $exam->id)
                ->where('status', 'in_progress')
                ->first();
            
            // Check if there's a new attempt ready (not_started)
            $notStartedAttempt = \App\Models\UserExam::where('user_id', Auth::id())
                ->where('exam_id', $exam->id)
                ->where('status', 'not_started')
                ->first();
            
            if ($inProgressAttempt) {
                $exam->inProgressAttempt = $inProgressAttempt;
                $exam->latestAttempt = null;
                $exam->hasNewAttempt = false;
            } elseif ($notStartedAttempt) {
                $exam->inProgressAttempt = null;
                $exam->latestAttempt = null;
                $exam->hasNewAttempt = true;
            } else {
                // Otherwise, show latest completed attempt
                $exam->inProgressAttempt = null;
                $exam->latestAttempt = \App\Models\UserExam::where('user_id', Auth::id())
                    ->where('exam_id', $exam->id)
                    ->where('status', 'completed')
                    ->orderBy('id', 'desc')
                    ->first();
                $exam->hasNewAttempt = false;
            }
        }
        
        return view('student.exams.index', compact('exams'));
    }

    public function show(int $id)
    {
        $exam = $this->examService->getExamById($id);
        
        if (!$exam || !$exam->is_active) {
            return redirect()->route('student.exams.index')
                ->with('error', 'Kỳ thi không khả dụng.');
        }

        // Check if user is assigned to this exam
        if (!$exam->assignedUsers->contains(Auth::id())) {
            return redirect()->route('student.exams.index')
                ->with('error', 'Bạn không có quyền truy cập kỳ thi này.');
        }

        // Check if user has an active exam in progress
        $userExam = $this->examTakingService->getActiveExam(Auth::user(), $exam);

        if ($userExam) {
            return redirect()->route('student.exams.take', $id);
        }

        // Check if user has a new attempt ready (not_started)
        $notStartedAttempt = \App\Models\UserExam::where('user_id', Auth::id())
            ->where('exam_id', $id)
            ->where('status', 'not_started')
            ->first();

        // If no new attempt available, check if user has completed this exam
        if (!$notStartedAttempt) {
            $latestCompletedAttempt = \App\Models\UserExam::where('user_id', Auth::id())
                ->where('exam_id', $id)
                ->where('status', 'completed')
                ->orderBy('completed_at', 'desc')
                ->first();

            if ($latestCompletedAttempt) {
                return redirect()->route('student.results.show', $latestCompletedAttempt->id)
                    ->with('info', 'Bạn đã hoàn thành bài thi này.');
            }
        }

        return view('student.exams.show', compact('exam'));
    }

    public function start(int $id)
    {
        $exam = $this->examService->getExamById($id);
        $userExam = $this->examTakingService->startExam(Auth::user(), $exam);

        return redirect()->route('student.exams.take', $id);
    }

    public function take(int $id)
    {
        $exam = $this->examService->getExamWithQuestions($id);
        $userExam = $this->examTakingService->getActiveExam(Auth::user(), $exam);

        if (!$userExam) {
            return redirect()->route('student.exams.index')
                ->with('error', 'Không tìm thấy bài thi đang thực hiện.');
        }

        // Ensure exam is in progress and started_at is set
        if ($userExam->status === 'not_started') {
            $userExam->update([
                'status' => 'in_progress',
                'started_at' => now()
            ]);
            $userExam = $userExam->fresh();
        } elseif (!$userExam->started_at) {
            $userExam->update(['started_at' => now()]);
            $userExam = $userExam->fresh();
        }

        $questions = $exam->questions()->with('answers')->orderBy('order')->get();
        
        // Load saved answers
        $savedAnswers = $userExam->userAnswers()->pluck('answer_id', 'question_id')->toArray();
        $savedEssayAnswers = $userExam->userAnswers()->whereNotNull('essay_answer')->pluck('essay_answer', 'question_id')->toArray();
        
        $timeRemaining = (int) ($exam->duration * 60 - now()->diffInSeconds($userExam->started_at));
        
        if ($timeRemaining <= 0) {
            return redirect()->route('student.exams.submit', $id);
        }

        return view('student.exams.take', compact('exam', 'userExam', 'questions', 'timeRemaining', 'savedAnswers', 'savedEssayAnswers'));
    }

    public function autosave(Request $request, int $id)
    {
        $exam = $this->examService->getExamById($id);
        $userExam = $this->examTakingService->getActiveExam(Auth::user(), $exam);

        if (!$userExam) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy bài thi.'], 404);
        }

        $answers = $request->input('answers', []);
        
        // Save answers without completing the exam
        foreach ($answers as $questionId => $answer) {
            if ($answer !== null && $answer !== '') {
                $answerData = [];
                
                // Check question type
                $question = \App\Models\Question::find($questionId);
                if (!$question) continue;
                
                if ($question->question_type === 'essay') {
                    $answerData['essay_answer'] = $answer;
                } else {
                    $answerData['answer_id'] = $answer;
                    // Check if answer is correct for auto-grading later
                    $answerModel = $question->answers()->find($answer);
                    if ($answerModel) {
                        $answerData['is_correct'] = $answerModel->is_correct;
                    }
                }
                
                $userExam->userAnswers()->updateOrCreate(
                    ['question_id' => $questionId],
                    $answerData
                );
            }
        }

        return response()->json(['success' => true, 'message' => 'Đã lưu tự động.']);
    }

    public function submit(Request $request, int $id)
    {
        $exam = $this->examService->getExamById($id);
        $userExam = $this->examTakingService->getActiveExam(Auth::user(), $exam);

        if (!$userExam) {
            return redirect()->route('student.exams.index')
                ->with('error', 'Không tìm thấy bài thi đang thực hiện.');
        }

        $answers = $request->input('answers', []);
        $completedExam = $this->examTakingService->submitExam($userExam, $answers);

        return redirect()->route('student.results.show', $completedExam->id)
            ->with('success', 'Nộp bài thi thành công!');
    }
}
