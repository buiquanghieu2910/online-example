<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface IUserRepository
{
    public function all(): Collection;
    
    public function paginate(int $perPage = 15): LengthAwarePaginator;
    
    public function findById(int $id): ?User;
    
    public function findByUsername(string $username): ?User;
    
    public function create(array $data): User;
    
    public function update(int $id, array $data): bool;
    
    public function delete(int $id): bool;
    
    public function getTotalCount(): int;
    
    public function getUserWithExams(int $id): ?User;
}
