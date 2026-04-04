<?php

namespace App\Http\Controllers\Teacher;

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
        return view('teacher.exams.index', compact('exams'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $teacher = auth()->user();
        $classes = $teacher->teachingClasses()->with('students')->get();
        return view('teacher.exams.create', compact('classes'));
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
            'class_id' => 'required|exists:classes,id',
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

        // Auto-assign all students in the class
        $class = \App\Models\SchoolClass::findOrFail($request->class_id);
        $studentIds = $class->students()->pluck('users.id')->toArray();
        if (!empty($studentIds)) {
            $exam->assignedUsers()->syncWithoutDetaching($studentIds);
        }

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

        return redirect()->route('teacher.exams.show', $exam)
            ->with('success', 'Kỳ thi đã được tạo thành công và tất cả học sinh trong lớp đã được phân công.');
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $exam = $this->examService->getExamWithQuestions($id);
        return view('teacher.exams.show', compact('exam'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id)
    {
        $exam = $this->examService->getExamById($id);
        $exam->load(['schoolClass.students']);
        return view('teacher.exams.edit', compact('exam'));
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
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after:start_time',
        ]);

        // Handle checkbox - if unchecked, it won't be in the request
        $validated['is_active'] = $request->has('is_active') ? true : false;

        $this->examService->updateExam($id, $validated);

        return redirect()->route('teacher.exams.index')
            ->with('success', 'Kỳ thi đã được cập nhật thành công.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $this->examService->deleteExam($id);

        return redirect()->route('teacher.exams.index')
            ->with('success', 'Kỳ thi đã được xóa thành công.');
    }

    /**
     * Show form to assign students to exam
     */
    public function showAssign(int $id)
    {
        $exam = $this->examService->getExamWithAssignedUsers($id);
        $assignedUserIds = $exam->assignedUsers->pluck('id')->toArray();
        
        // Get all students of this teacher
        $allUsers = auth()->user()->students()->get();
            
        return view('teacher.exams.assign', compact('exam', 'allUsers', 'assignedUserIds'));
    }

    /**
     * Assign students to exam
     */
    public function assignUsers(Request $request, int $id)
    {
        $validated = $request->validate([
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $exam = $this->examService->getExamById($id);
        
        // Get teacher's student IDs
        $teacherStudentIds = auth()->user()->students->pluck('id')->toArray();
        
        // Filter only students that belong to this teacher
        $userIdsToAssign = isset($validated['user_ids']) 
            ? array_intersect($validated['user_ids'], $teacherStudentIds)
            : [];
        
        // Sync assignments (this will add new and remove unchecked)
        $syncData = [];
        foreach ($userIdsToAssign as $userId) {
            $syncData[$userId] = ['assigned_at' => now()];
        }
        
        // Only sync students that belong to this teacher
        $exam->assignedUsers()->wherePivotIn('user_id', $teacherStudentIds)->detach();
        $exam->assignedUsers()->attach($syncData);

        return redirect()->route('teacher.exams.show', $exam)
            ->with('success', 'Đã gán học sinh vào kỳ thi thành công.');
    }

    /**
     * Unassign student from exam
     */
    public function unassignUser(int $examId, int $userId)
    {
        // Verify student belongs to this teacher
        $student = User::find($userId);
        if (!$student || $student->role !== 'student' || !auth()->user()->students->contains($userId)) {
            abort(403, 'Unauthorized access.');
        }
        
        $exam = $this->examService->getExamById($examId);
        $exam->assignedUsers()->detach($userId);

        return redirect()->route('teacher.exams.show', $exam)
            ->with('success', 'Đã hủy gán học sinh khỏi kỳ thi.');
    }

    /**
     * Reset exam for user to allow retake
     */
    public function resetExam(int $userExamId)
    {
        $userExam = \App\Models\UserExam::findOrFail($userExamId);
        
        // Verify student belongs to this teacher
        if (!auth()->user()->students->contains($userExam->user_id)) {
            abort(403, 'Unauthorized access.');
        }
        
        $result = $this->examTakingService->resetExamForUser($userExamId);

        if ($result) {
            return redirect()->back()
                ->with('success', 'Đã cho phép học sinh làm lại bài thi.');
        }

        return redirect()->back()
            ->with('error', 'Không thể reset bài thi.');
    }

    /**
     * View exam history for a student
     */
    public function showHistory(int $examId, int $userId)
    {
        $exam = $this->examService->getExamById($examId);
        $user = User::findOrFail($userId);
        $userExams = $this->examTakingService->getUserExamHistory($userId, $examId);
        
        return view('teacher.exams.history', compact('exam', 'user', 'userExams'));
    }
}
