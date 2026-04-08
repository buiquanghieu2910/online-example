<?php

namespace App\Repositories\Impl;

use App\Models\User;
use App\Repositories\IUserRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class UserRepositoryImpl implements IUserRepository
{
    protected User $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->paginate($perPage);
    }

    public function findById(int $id): ?User
    {
        return $this->model->find($id);
    }

    public function findByUsername(string $username): ?User
    {
        return $this->model->where('username', $username)->first();
    }

    public function create(array $data): User
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $user = $this->findById($id);
        return $user ? $user->update($data) : false;
    }

    public function delete(int $id): bool
    {
        $user = $this->findById($id);
        return $user ? $user->delete() : false;
    }

    public function getTotalCount(): int
    {
        return $this->model->count();
    }

    public function getUserWithExams(int $id): ?User
    {
        return $this->model->with(['userExams.exam'])->find($id);
    }
}
