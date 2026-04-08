<?php

namespace App\Repositories\Impl;

use App\Models\Question;
use App\Repositories\IQuestionRepository;
use Illuminate\Database\Eloquent\Collection;

class QuestionRepositoryImpl implements IQuestionRepository
{
    protected Question $model;

    public function __construct(Question $model)
    {
        $this->model = $model;
    }

    public function findById(int $id): ?Question
    {
        return $this->model->find($id);
    }

    public function getByExamId(int $examId): Collection
    {
        return $this->model->where('exam_id', $examId)
            ->with('answers')
            ->orderBy('order')
            ->get();
    }

    public function create(array $data): Question
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $question = $this->findById($id);
        return $question ? $question->update($data) : false;
    }

    public function delete(int $id): bool
    {
        $question = $this->findById($id);
        return $question ? $question->delete() : false;
    }

    public function getQuestionWithAnswers(int $id): ?Question
    {
        return $this->model->with('answers')->find($id);
    }
}
