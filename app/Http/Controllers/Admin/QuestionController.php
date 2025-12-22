<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\IExamService;
use App\Services\IQuestionService;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function __construct(
        private IExamService $examService,
        private IQuestionService $questionService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(int $examId)
    {
        $exam = $this->examService->getExamWithQuestions($examId);
        $questions = $this->questionService->getQuestionsByExamId($examId);
        return view('admin.questions.index', compact('exam', 'questions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(int $examId)
    {
        $exam = $this->examService->getExamById($examId);
        return view('admin.questions.create', compact('exam'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, int $examId)
    {
        $validated = $request->validate([
            'question_text' => 'required|string',
            'question_type' => 'required|in:multiple_choice,true_false,essay',
            'points' => 'required|integer|min:1',
            'order' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:51200',
            'answers' => 'required_if:question_type,multiple_choice,true_false|array|min:2',
            'answers.*.answer_text' => 'required_with:answers|string',
            'answers.*.is_correct' => 'nullable|boolean',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $path = $file->store('questions', 'minio');
            $validated['image_url'] = $path;
        }

        $question = $this->questionService->createQuestion($examId, $validated);

        if (isset($validated['answers']) && $validated['question_type'] !== 'essay') {
            $this->questionService->syncAnswers($question, $validated['answers']);
        }

        return redirect()->route('admin.questions.index', $examId)
            ->with('success', 'Câu hỏi đã được tạo thành công.');
    }

    /**
     * Display the specified resource.
     */
    public function show(int $examId, int $questionId)
    {
        $exam = $this->examService->getExamById($examId);
        $question = $this->questionService->getQuestionById($questionId);
        return view('admin.questions.show', compact('exam', 'question'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $examId, int $questionId)
    {
        $exam = $this->examService->getExamById($examId);
        $question = $this->questionService->getQuestionById($questionId);
        return view('admin.questions.edit', compact('exam', 'question'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $examId, int $questionId)
    {
        $validated = $request->validate([
            'question_text' => 'required|string',
            'question_type' => 'required|in:multiple_choice,true_false,essay',
            'points' => 'required|integer|min:1',
            'order' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:51200',
            'answers' => 'required_if:question_type,multiple_choice,true_false|array|min:2',
            'answers.*.answer_text' => 'required_with:answers|string',
            'answers.*.is_correct' => 'nullable|boolean',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $question = $this->questionService->getQuestionById($questionId);
            
            // Delete old image if exists
            if ($question->image_url) {
                \Storage::disk('minio')->delete($question->image_url);
            }
            
            $file = $request->file('image');
            $path = $file->store('questions', 'minio');
            $validated['image_url'] = $path;
        }

        $this->questionService->updateQuestion($questionId, $validated);

        if (isset($validated['answers']) && $validated['question_type'] !== 'essay') {
            $question = $this->questionService->getQuestionById($questionId);
            $this->questionService->syncAnswers($question, $validated['answers']);
        }

        return redirect()->route('admin.questions.index', $examId)
            ->with('success', 'Câu hỏi đã được cập nhật thành công.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $examId, int $questionId)
    {
        $this->questionService->deleteQuestion($questionId);

        return redirect()->route('admin.questions.index', $examId)
            ->with('success', 'Câu hỏi đã được xóa thành công.');
    }
}
