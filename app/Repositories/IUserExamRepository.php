<?php

namespace App\Repositories;

use App\Models\UserExam;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface IUserExamRepository
{
    public function findById(int $id): ?UserExam;
    
    public function getByUserId(int $userId): Collection;
    
    public function paginateByUserId(int $userId, int $perPage = 15): LengthAwarePaginator;
    
    public function create(array $data): UserExam;
    
    public function update(int $id, array $data): bool;
    
    public function getActiveExamForUser(int $userId, int $examId): ?UserExam;
    
    public function getRecentResults(int $limit = 5): Collection;
    
    public function getTotalCount(): int;
    
    public function getCompletedCount(): int;
}
