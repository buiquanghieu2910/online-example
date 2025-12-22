<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\IDashboardService;

class DashboardController extends Controller
{
    public function __construct(
        private IDashboardService $dashboardService
    ) {}

    public function index()
    {
        $data = $this->dashboardService->getAdminDashboardData();

        return view('admin.dashboard', [
            'totalExams' => $data['total_exams'],
            'totalUsers' => $data['total_users'],
            'totalAttempts' => $data['total_attempts'],
            'recentExams' => $data['recent_exams'],
            'recentResults' => $data['recent_results'],
        ]);
    }
}
