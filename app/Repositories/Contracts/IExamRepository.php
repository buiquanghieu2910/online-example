<?php

namespace App\Repositories\Contracts;

use App\Models\Exam;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface IExamRepository
{
    public function all(): Collection;
    
    public function paginate(int $perPage = 15): LengthAwarePaginator;
    
    public function findById(int $id): ?Exam;
    
    public function create(array $data): Exam;
    
    public function update(int $id, array $data): bool;
    
    public function delete(int $id): bool;
    
    public function getActiveExams(): Collection;
    
    public function getRecentExams(int $limit = 5): Collection;
    
    public function getExamWithQuestions(int $id): ?Exam;
    
    public function getTotalCount(): int;
}
