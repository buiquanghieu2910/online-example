<?php

namespace App\Services;

use App\Models\User;

interface IDashboardService
{
    public function getOverview(User $user, array $filters = []): array;
}
