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
            // Check if there's a new attempt ready (not_started or in_progress)
            $newAttempt = \App\Models\UserExam::where('user_id', Auth::id())
                ->where('exam_id', $exam->id)
                ->whereIn('status', ['not_started', 'in_progress'])
                ->first();
            
            // If there's a new attempt, don't show latest completed
            if ($newAttempt) {
                $exam->latestAttempt = null;
                $exam->hasNewAttempt = true;
            } else {
                // Otherwise, show latest completed attempt
                $exam->latestAttempt = \App\Models\UserExam::where('user_id', Auth::id())
                    ->where('exam_id', $exam->id)
                    ->where('status', 'completed')
                    ->orderBy('completed_at', 'desc')
                    ->orderBy('created_at', 'desc')
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

        $questions = $exam->questions()->with('answers')->orderBy('order')->get();
        $timeRemaining = (int) ($exam->duration * 60 - now()->diffInSeconds($userExam->started_at));
        
        if ($timeRemaining <= 0) {
            return redirect()->route('student.exams.submit', $id);
        }

        return view('student.exams.take', compact('exam', 'userExam', 'questions', 'timeRemaining'));
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
