<?php

namespace App\Services\Contracts;

use App\Models\Exam;
use App\Models\User;
use App\Models\UserExam;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface IExamTakingService
{
    public function startExam(User $user, Exam $exam): UserExam;
    
    public function getActiveExam(User $user, Exam $exam): ?UserExam;
    
    public function submitExam(UserExam $userExam, array $answers): UserExam;
    
    public function calculateScore(UserExam $userExam): float;
    
    public function getUserResults(User $user, int $perPage = 15): LengthAwarePaginator;
    
    public function getResultById(int $id): ?UserExam;
}
