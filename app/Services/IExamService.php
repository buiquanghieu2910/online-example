<?php

namespace App\Services;

use App\Models\Exam;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface IExamService
{
    public function getAllExams(): Collection;
    
    public function getPaginatedExams(int $perPage = 15): LengthAwarePaginator;
    
    public function getExamById(int $id): ?Exam;
    
    public function createExam(array $data): Exam;
    
    public function updateExam(int $id, array $data): bool;
    
    public function deleteExam(int $id): bool;
    
    public function getActiveExams(): Collection;
    
    public function getExamWithQuestions(int $id): ?Exam;
    
    public function assignUsersToExam(int $examId, array $userIds): void;
    
    public function unassignUserFromExam(int $examId, int $userId): void;
    
    public function getAssignedExamsForUser(int $userId): Collection;
}
