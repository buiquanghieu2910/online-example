<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Services\IDashboardService;

class DashboardController extends Controller
{
    public function __construct(
        private IDashboardService $dashboardService
    ) {}

    /**
     * Display the teacher dashboard.
     */
    public function index()
    {
        $stats = $this->dashboardService->getTeacherDashboardData(auth()->id());
        return view('teacher.dashboard', compact('stats'));
    }
}
