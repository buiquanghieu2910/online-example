<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface IUserService
{
    public function getPaginatedUsers(int $perPage = 15): LengthAwarePaginator;
    
    public function getUsersByRole(string $role, int $perPage = 15): LengthAwarePaginator;
    
    public function getUserById(int $id): ?User;
    
    public function getUserByUsername(string $username): ?User;
    
    public function createUser(array $data): User;
    
    public function updateUser(int $id, array $data): bool;
    
    public function deleteUser(int $id): bool;
    
    public function getUserWithExams(int $id): ?User;
    
    public function getUsersByTeacher(int $teacherId, int $perPage = 15): LengthAwarePaginator;
}
