<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\IExamService;
use App\Services\IExamTakingService;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function __construct(
        private IExamService $examService,
        private IExamTakingService $examTakingService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $exams = $this->examService->getPaginatedExams(10);
        return view('admin.exams.index', compact('exams'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.exams.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration' => 'required|integer|min:1',
            'min_score' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after:start_time',
            'questions' => 'nullable|array',
            'questions.*.question_text' => 'required_with:questions|string',
            'questions.*.question_type' => 'required_with:questions|in:multiple_choice,true_false,essay',
            'questions.*.points' => 'required_with:questions|numeric|min:0.5',
            'questions.*.order' => 'required_with:questions|integer|min:0',
            'questions.*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:51200',
            'questions.*.answers' => 'required_if:questions.*.question_type,multiple_choice,true_false|array|min:2',
            'questions.*.answers.*.answer_text' => 'required_with:questions.*.answers|string',
            'questions.*.answers.*.is_correct' => 'nullable|boolean',
        ]);

        // Create exam
        $exam = $this->examService->createExam($validated);

        // Create questions if provided
        if ($request->has('questions')) {
            foreach ($request->questions as $questionData) {
                // Handle image upload
                $imageUrl = null;
                if (isset($questionData['image']) && $questionData['image']->isValid()) {
                    $imageUrl = upload_image_to_minio($questionData['image']);
                }

                // Create question
                $question = $exam->questions()->create([
                    'question_text' => $questionData['question_text'],
                    'question_type' => $questionData['question_type'],
                    'points' => $questionData['points'],
                    'order' => $questionData['order'],
                    'image_url' => $imageUrl,
                ]);

                // Create answers if not essay
                if ($questionData['question_type'] !== 'essay' && isset($questionData['answers'])) {
                    foreach ($questionData['answers'] as $answerData) {
                        $question->answers()->create([
                            'answer_text' => $answerData['answer_text'],
                            'is_correct' => isset($answerData['is_correct']) && $answerData['is_correct'] == '1',
                        ]);
                    }
                }
            }
        }

        return redirect()->route('admin.exams.show', $exam)
            ->with('success', 'Kỳ thi đã được tạo thành công.');
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $exam = $this->examService->getExamWithQuestions($id);
        return view('admin.exams.show', compact('exam'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id)
    {
        $exam = $this->examService->getExamById($id);
        return view('admin.exams.edit', compact('exam'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration' => 'required|integer|min:1',
            'min_score' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after:start_time',
        ]);

        $this->examService->updateExam($id, $validated);

        return redirect()->route('admin.exams.index')
            ->with('success', 'Kỳ thi đã được cập nhật thành công.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $this->examService->deleteExam($id);

        return redirect()->route('admin.exams.index')
            ->with('success', 'Kỳ thi đã được xóa thành công.');
    }

    /**
     * Reset exam for user to allow retake
     */
    public function resetExam(int $userExamId)
    {
        $result = $this->examTakingService->resetExamForUser($userExamId);

        if ($result) {
            return redirect()->back()
                ->with('success', 'Đã cho phép người dùng làm lại bài thi.');
        }

        return redirect()->back()
            ->with('error', 'Không thể reset bài thi.');
    }

    /**
     * Show form to assign users to exam
     */
    public function showAssign(int $id)
    {
        $exam = $this->examService->getExamById($id);
        $allUsers = User::where('role', 'user')->get();
        $assignedUserIds = $exam->assignedUsers->pluck('id')->toArray();
        
        return view('admin.exams.assign', compact('exam', 'allUsers', 'assignedUserIds'));
    }

    /**
     * Assign users to exam
     */
    public function assignUsers(Request $request, int $id)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $this->examService->assignUsersToExam($id, $validated['user_ids']);

        return redirect()->route('admin.exams.show', $id)
            ->with('success', 'Đã gán người dùng cho đề thi.');
    }

    /**
     * Unassign user from exam
     */
    public function unassignUser(int $examId, int $userId)
    {
        $this->examService->unassignUserFromExam($examId, $userId);

        return redirect()->back()
            ->with('success', 'Đã gỡ người dùng khỏi đề thi.');
    }

    /**
     * Show exam history for a user
     */
    public function showHistory(int $examId, int $userId)
    {
        $exam = $this->examService->getExamById($examId);
        $user = User::findOrFail($userId);
        $history = $this->examTakingService->getUserExamHistory($userId, $examId);

        return view('admin.exams.history', compact('exam', 'user', 'history'));
    }

    /**
     * Show list of exams that have pending grading
     */
    public function pendingGrading()
    {
        $exams = $this->examService->getAllExams();
        return view('admin.exams.pending-grading', compact('exams'));
    }

    /**
     * Show list of users pending grading for specific exam
     */
    public function examPendingUsers(int $examId)
    {
        $exam = $this->examService->getExamById($examId);
        
        if (!$exam) {
            return redirect()->route('admin.grading.pending')
                ->with('error', 'Không tìm thấy đề thi.');
        }

        $pendingUserExams = \App\Models\UserExam::where('exam_id', $examId)
            ->where('status', 'completed')
            ->where('grading_status', 'pending_review')
            ->with(['user'])
            ->orderBy('completed_at', 'desc')
            ->get();

        return view('admin.exams.exam-pending-users', compact('exam', 'pendingUserExams'));
    }

    /**
     * Show grading form for a specific user exam
     */
    public function showGrading(int $userExamId)
    {
        $userExam = $this->examTakingService->getResultById($userExamId);
        
        if (!$userExam) {
            return redirect()->route('admin.grading.pending')
                ->with('error', 'Không tìm thấy bài thi.');
        }

        $userExam->load(['user', 'exam', 'userAnswers.question', 'userAnswers.answer']);

        return view('admin.exams.grade', compact('userExam'));
    }

    /**
     * Submit grading for essay questions
     */
    public function submitGrading(Request $request, int $userExamId)
    {
        $userExam = $this->examTakingService->getResultById($userExamId);
        
        if (!$userExam) {
            return redirect()->route('admin.grading.pending')
                ->with('error', 'Không tìm thấy bài thi.');
        }

        // Validate with dynamic max score based on question points
        $rules = [
            'grades' => 'required|array',
        ];
        
        foreach ($request->input('grades', []) as $questionId => $gradeData) {
            $question = $userExam->exam->questions()->find($questionId);
            if ($question) {
                $rules["grades.{$questionId}.feedback"] = 'nullable|string';
                $rules["grades.{$questionId}.score"] = "required|numeric|min:0|max:{$question->points}";
            }
        }
        
        $validated = $request->validate($rules);

        $result = $this->examTakingService->gradeEssayAnswers($userExamId, $validated['grades']);

        if ($result) {
            return redirect()->route('admin.grading.exam.users', $userExam->exam_id)
                ->with('success', 'Đã chấm bài thi thành công.');
        }

        return redirect()->back()
            ->with('error', 'Không thể chấm bài thi.');
    }
}
