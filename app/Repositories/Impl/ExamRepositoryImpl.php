<?php

namespace App\Repositories\Impl;

use App\Models\Exam;
use App\Repositories\IExamRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ExamRepositoryImpl implements IExamRepository
{
    protected Exam $model;

    public function __construct(Exam $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->withCount('questions')->paginate($perPage);
    }

    public function findById(int $id): ?Exam
    {
        return $this->model->find($id);
    }

    public function create(array $data): Exam
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $exam = $this->findById($id);
        return $exam ? $exam->update($data) : false;
    }

    public function delete(int $id): bool
    {
        $exam = $this->findById($id);
        return $exam ? $exam->delete() : false;
    }

    public function getActiveExams(): Collection
    {
        return $this->model->where('is_active', true)
            ->withCount('questions')
            ->get();
    }

    public function getRecentExams(int $limit = 5): Collection
    {
        return $this->model->latest()
            ->limit($limit)
            ->get();
    }

    public function getExamWithQuestions(int $id): ?Exam
    {
        return $this->model->with(['questions.answers'])->find($id);
    }

    public function getTotalCount(): int
    {
        return $this->model->count();
    }
}
