<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\IDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private IDashboardService $dashboardService)
    {
    }

    public function overview(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'days' => ['nullable', 'integer', 'in:7,14,30,60'],
            'class_id' => ['nullable', 'integer'],
            'exam_id' => ['nullable', 'integer'],
        ]);

        $data = $this->dashboardService->getOverview($request->user(), $validated);

        return response()->json(['data' => $data]);
    }
}
