<?php

namespace App\Services\Impl;

use App\Models\User;
use App\Repositories\IUserRepository;
use App\Services\IUserService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class UserServiceImpl implements IUserService
{
    public function __construct(
        private IUserRepository $userRepository
    ) {}

    public function getPaginatedUsers(int $perPage = 15): LengthAwarePaginator
    {
        return $this->userRepository->paginate($perPage);
    }

    public function getUserById(int $id): ?User
    {
        return $this->userRepository->findById($id);
    }

    public function getUserByUsername(string $username): ?User
    {
        return $this->userRepository->findByUsername($username);
    }

    public function createUser(array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        
        return $this->userRepository->create($data);
    }

    public function updateUser(int $id, array $data): bool
    {
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        
        return $this->userRepository->update($id, $data);
    }

    public function deleteUser(int $id): bool
    {
        return $this->userRepository->delete($id);
    }

    public function getUserWithExams(int $id): ?User
    {
        return $this->userRepository->getUserWithExams($id);
    }
}
