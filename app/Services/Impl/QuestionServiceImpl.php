<?php

namespace App\Services\Impl;

use App\Models\Question;
use App\Repositories\IQuestionRepository;
use App\Services\IQuestionService;
use Illuminate\Database\Eloquent\Collection;

class QuestionServiceImpl implements IQuestionService
{
    public function __construct(
        private IQuestionRepository $questionRepository
    ) {}

    public function getQuestionById(int $id): ?Question
    {
        return $this->questionRepository->findById($id);
    }

    public function getQuestionsByExamId(int $examId): Collection
    {
        return $this->questionRepository->getByExamId($examId);
    }

    public function createQuestion(int $examId, array $data): Question
    {
        $data['exam_id'] = $examId;
        return $this->questionRepository->create($data);
    }

    public function updateQuestion(int $id, array $data): bool
    {
        return $this->questionRepository->update($id, $data);
    }

    public function deleteQuestion(int $id): bool
    {
        return $this->questionRepository->delete($id);
    }

    public function syncAnswers(Question $question, array $answers): void
    {
        // Delete existing answers
        $question->answers()->delete();
        
        // Create new answers
        foreach ($answers as $answerData) {
            $question->answers()->create([
                'answer_text' => $answerData['answer_text'],
                'is_correct' => $answerData['is_correct'] ?? false,
            ]);
        }
    }
}
