<?php

namespace App\Services\Impl;

use App\Repositories\IExamRepository;
use App\Repositories\IUserExamRepository;
use App\Repositories\IUserRepository;
use App\Services\IDashboardService;

class DashboardServiceImpl implements IDashboardService
{
    public function __construct(
        private IExamRepository $examRepository,
        private IUserRepository $userRepository,
        private IUserExamRepository $userExamRepository
    ) {}

    public function getAdminDashboardData(): array
    {
        return [
            'total_users' => $this->userRepository->getTotalCount(),
            'total_exams' => $this->examRepository->getTotalCount(),
            'total_attempts' => $this->userExamRepository->getTotalCount(),
            'completed_exams' => $this->userExamRepository->getCompletedCount(),
            'recent_exams' => $this->examRepository->getRecentExams(5),
            'recent_results' => $this->userExamRepository->getRecentResults(10),
        ];
    }

    public function getTeacherDashboardData(int $teacherId): array
    {
        $teacher = \App\Models\User::find($teacherId);
        $students = $teacher ? $teacher->students()->count() : 0;
        $exams = $this->examRepository->getTotalCount();
        $recentExams = $this->examRepository->getRecentExams(5);
        
        return [
            'total_students' => $students,
            'total_exams' => $exams,
            'recent_exams' => $recentExams,
        ];
    }
}
