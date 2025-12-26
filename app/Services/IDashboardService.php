<?php

namespace App\Services;

interface IDashboardService
{
    public function getAdminDashboardData(): array;
    
    public function getTeacherDashboardData(int $teacherId): array;
}
