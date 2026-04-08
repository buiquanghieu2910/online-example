<?php

namespace App\Services\Impl;

use App\Models\Exam;
use App\Models\User;
use App\Models\UserExam;
use App\Repositories\IExamRepository;
use App\Repositories\IUserExamRepository;
use App\Services\IExamTakingService;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ExamTakingServiceImpl implements IExamTakingService
{
    public function __construct(
        private IUserExamRepository $userExamRepository,
        private IExamRepository $examRepository
    ) {}

    public function startExam(User $user, Exam $exam): UserExam
    {
        // Check if user already has an active exam (in_progress or not_started)
        $activeExam = $this->userExamRepository->getActiveExamForUser($user->id, $exam->id);
        
        if ($activeExam) {
            // If it's not_started, update to in_progress with started_at
            if ($activeExam->status === 'not_started') {
                $this->userExamRepository->update($activeExam->id, [
                    'started_at' => Carbon::now(),
                    'remaining_seconds' => $activeExam->remaining_seconds ?? max(0, (int) $exam->duration * 60),
                    'status' => 'in_progress',
                ]);
                return $activeExam->fresh();
            }
            return $activeExam;
        }

        // Create new user exam
        return $this->userExamRepository->create([
            'user_id' => $user->id,
            'exam_id' => $exam->id,
            'started_at' => Carbon::now(),
            'remaining_seconds' => max(0, (int) $exam->duration * 60),
            'status' => 'in_progress',
            'grading_status' => 'auto_graded',
        ]);
    }

    public function getActiveExam(User $user, Exam $exam): ?UserExam
    {
        return $this->userExamRepository->getActiveExamForUser($user->id, $exam->id);
    }

    public function submitExam(UserExam $userExam, array $answers): UserExam
    {
        $exam = $this->examRepository->getExamWithQuestions($userExam->exam_id);
        $hasEssayQuestion = false;

        // Save user answers
        foreach ($exam->questions as $question) {
            $answerData = [];
            
            if ($question->question_type === 'essay') {
                $hasEssayQuestion = true;
                $answerData['essay_answer'] = $answers[$question->id] ?? null;
                $answerData['is_correct'] = null; // Will be graded by admin
            } else {
                $answerId = $answers[$question->id] ?? null;
                $answerData['answer_id'] = $answerId;
                
                // Auto-grade multiple choice
                if ($answerId) {
                    $answer = $question->answers()->find($answerId);
                    $answerData['is_correct'] = $answer ? $answer->is_correct : false;
                }
            }
            
            $userExam->userAnswers()->updateOrCreate(
                ['question_id' => $question->id],
                $answerData
            );
        }

        // Determine grading status
        $gradingStatus = $hasEssayQuestion ? 'pending_review' : 'auto_graded';
        
        // Calculate score (only for multiple choice questions if has essay)
        $score = $hasEssayQuestion ? null : $this->calculateScore($userExam);

        // Update user exam
        $this->userExamRepository->update($userExam->id, [
            'completed_at' => Carbon::now(),
            'remaining_seconds' => 0,
            'score' => $score,
            'status' => 'completed',
            'grading_status' => $gradingStatus,
        ]);

        return $userExam->fresh();
    }

    public function calculateScore(UserExam $userExam): float
    {
        $exam = $this->examRepository->getExamWithQuestions($userExam->exam_id);
        
        if (!$exam || $exam->questions->isEmpty()) {
            return 0;
        }

        $totalScore = 0;

        foreach ($exam->questions as $question) {
            $userAnswer = $userExam->userAnswers()->where('question_id', $question->id)->first();
            
            if ($question->question_type === 'multiple_choice' || $question->question_type === 'true_false') {
                // Award full points if correct
                if ($userAnswer && $userAnswer->is_correct) {
                    $totalScore += $question->points;
                }
            } elseif ($question->question_type === 'essay') {
                // Use essay_score directly (not percentage)
                if ($userAnswer && $userAnswer->essay_score !== null) {
                    $totalScore += $userAnswer->essay_score;
                }
            }
        }

        return round($totalScore, 2);
    }

    public function getUserResults(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $this->userExamRepository->paginateByUserId($user->id, $perPage);
    }

    public function getResultById(int $id): ?UserExam
    {
        return $this->userExamRepository->findById($id);
    }

    public function resetExamForUser(int $userExamId): bool
    {
        $userExam = $this->userExamRepository->findById($userExamId);
        
        if (!$userExam) {
            return false;
        }

        if ($userExam->status !== 'completed') {
            return false;
        }

        $hasPendingRetake = UserExam::query()
            ->where('user_id', $userExam->user_id)
            ->where('exam_id', $userExam->exam_id)
            ->where('status', 'not_started')
            ->exists();

        if ($hasPendingRetake) {
            return true;
        }

        $durationSeconds = max(0, (int) optional($userExam->exam)->duration * 60);

        // Create a NEW record to allow retake (keep old record for history)
        $this->userExamRepository->create([
            'user_id' => $userExam->user_id,
            'exam_id' => $userExam->exam_id,
            'status' => 'not_started',
            'grading_status' => 'auto_graded',
            'remaining_seconds' => $durationSeconds,
        ]);

        return true;
    }

    public function getUserExamHistory(int $userId, int $examId)
    {
        return UserExam::where('user_id', $userId)
            ->where('exam_id', $examId)
            ->orderBy('completed_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getPendingGradingExams(): \Illuminate\Support\Collection
    {
        return UserExam::where('status', 'completed')
            ->where('grading_status', 'pending_review')
            ->with(['user', 'exam'])
            ->orderBy('completed_at', 'desc')
            ->get();
    }

    public function gradeEssayAnswers(int $userExamId, array $essayGrades): bool
    {
        $userExam = $this->userExamRepository->findById($userExamId);
        
        if (!$userExam) {
            return false;
        }

        // Update essay answers with admin feedback and scores
        foreach ($essayGrades as $questionId => $gradeData) {
            $userAnswer = $userExam->userAnswers()
                ->where('question_id', $questionId)
                ->first();
                
            if ($userAnswer) {
                $userAnswer->update([
                    'admin_feedback' => $gradeData['feedback'] ?? null,
                    'essay_score' => $gradeData['score'] ?? null,
                    'is_correct' => ($gradeData['score'] / $userAnswer->question->points) >= 0.5, // 50% of question points
                ]);
            }
        }

        // Reload to get updated essay scores
        $userExam = $userExam->fresh(['userAnswers']);
        
        // Recalculate total score
        $totalScore = $this->calculateScore($userExam);
        
        // Update user exam
        $this->userExamRepository->update($userExam->id, [
            'score' => $totalScore,
            'grading_status' => 'manually_graded',
        ]);

        return true;
    }
}
