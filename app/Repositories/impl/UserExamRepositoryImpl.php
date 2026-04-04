<?php

namespace App\Repositories\Impl;

use App\Models\UserExam;
use App\Repositories\IUserExamRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class UserExamRepositoryImpl implements IUserExamRepository
{
    protected UserExam $model;

    public function __construct(UserExam $model)
    {
        $this->model = $model;
    }

    public function findById(int $id): ?UserExam
    {
        return $this->model->find($id);
    }

    public function getByUserId(int $userId): Collection
    {
        return $this->model->where('user_id', $userId)
            ->with('exam')
            ->latest()
            ->get();
    }

    public function paginateByUserId(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where('user_id', $userId)
            ->where('status', 'completed')
            ->with('exam')
            ->latest('completed_at')
            ->paginate($perPage);
    }

    public function create(array $data): UserExam
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $userExam = $this->findById($id);
        return $userExam ? $userExam->update($data) : false;
    }

    public function getActiveExamForUser(int $userId, int $examId): ?UserExam
    {
        return $this->model->where('user_id', $userId)
            ->where('exam_id', $examId)
            ->whereIn('status', ['in_progress', 'not_started'])
            ->first();
    }

    public function getRecentResults(int $limit = 5): Collection
    {
        return $this->model->where('status', 'completed')
            ->with(['user', 'exam'])
            ->latest('completed_at')
            ->limit($limit)
            ->get();
    }

    public function getTotalCount(): int
    {
        return $this->model->count();
    }

    public function getCompletedCount(): int
    {
        return $this->model->where('status', 'completed')->count();
    }
}
