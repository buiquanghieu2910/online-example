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
}
