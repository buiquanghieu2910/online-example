<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Question;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function index(Request $request, Exam $exam): JsonResponse
    {
        if (! $request->user()->teachingClasses()->where('classes.id', $exam->class_id)->exists()) {
            abort(403);
        }

        $questions = $exam->questions()
            ->with('answers:id,question_id,answer_text,is_correct')
            ->orderBy('order')
            ->get();

        return response()->json([
            'data' => [
                'exam' => ['id' => $exam->id, 'title' => $exam->title],
                'questions' => $questions,
            ],
        ]);
    }

    public function store(Request $request, Exam $exam): JsonResponse
    {
        if (! $request->user()->teachingClasses()->where('classes.id', $exam->class_id)->exists()) {
            abort(403);
        }

        $validated = $request->validate([
            'question_text' => ['required', 'string'],
            'question_type' => ['required', 'in:multiple_choice,true_false,essay'],
            'points' => ['required', 'numeric', 'min:0.5'],
            'order' => ['required', 'integer', 'min:0'],
            'answers' => ['nullable', 'array'],
            'answers.*.answer_text' => ['required_with:answers', 'string'],
            'answers.*.is_correct' => ['nullable', 'boolean'],
        ]);

        $question = $exam->questions()->create([
            'question_text' => $validated['question_text'],
            'question_type' => $validated['question_type'],
            'points' => $validated['points'],
            'order' => $validated['order'],
        ]);

        $this->syncAnswers($question, $validated['question_type'], $validated['answers'] ?? []);

        return response()->json(['message' => 'Tạo câu hỏi thành công.']);
    }

    public function update(Request $request, Question $question): JsonResponse
    {
        if (! $request->user()->teachingClasses()->where('classes.id', $question->exam->class_id)->exists()) {
            abort(403);
        }

        $validated = $request->validate([
            'question_text' => ['required', 'string'],
            'question_type' => ['required', 'in:multiple_choice,true_false,essay'],
            'points' => ['required', 'numeric', 'min:0.5'],
            'order' => ['required', 'integer', 'min:0'],
            'answers' => ['nullable', 'array'],
            'answers.*.answer_text' => ['required_with:answers', 'string'],
            'answers.*.is_correct' => ['nullable', 'boolean'],
        ]);

        $question->update([
            'question_text' => $validated['question_text'],
            'question_type' => $validated['question_type'],
            'points' => $validated['points'],
            'order' => $validated['order'],
        ]);

        $this->syncAnswers($question, $validated['question_type'], $validated['answers'] ?? []);

        return response()->json(['message' => 'Cập nhật câu hỏi thành công.']);
    }

    public function destroy(Request $request, Question $question): JsonResponse
    {
        if (! $request->user()->teachingClasses()->where('classes.id', $question->exam->class_id)->exists()) {
            abort(403);
        }

        $question->delete();

        return response()->json(['message' => 'Xóa câu hỏi thành công.']);
    }

    private function syncAnswers(Question $question, string $questionType, array $answers): void
    {
        if ($questionType === 'essay') {
            $question->answers()->delete();
            return;
        }

        $normalized = collect($answers)
            ->filter(fn ($answer) => !empty($answer['answer_text'] ?? null))
            ->map(fn ($answer) => [
                'answer_text' => $answer['answer_text'],
                'is_correct' => (bool) ($answer['is_correct'] ?? false),
            ])
            ->values();

        if ($normalized->count() < 2) {
            return;
        }

        if (! $normalized->contains(fn ($answer) => $answer['is_correct'])) {
            $normalized[0]['is_correct'] = true;
        }

        $question->answers()->delete();
        $question->answers()->createMany($normalized->all());
    }
}

