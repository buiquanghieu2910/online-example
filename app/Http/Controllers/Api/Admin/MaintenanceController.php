<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\IMaintenanceModeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function __construct(private IMaintenanceModeService $maintenanceModeService)
    {
    }

    public function show(): JsonResponse
    {
        return response()->json([
            'data' => $this->maintenanceModeService->getStatus(),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'enabled' => ['required', 'boolean'],
            'message' => ['nullable', 'string', 'max:255'],
        ]);

        $status = $this->maintenanceModeService->updateStatus(
            (bool) $validated['enabled'],
            $validated['message'] ?? null,
            $request->user()?->id
        );

        return response()->json([
            'message' => $status['enabled'] ? 'Đã bật chế độ bảo trì.' : 'Đã tắt chế độ bảo trì.',
            'data' => $status,
        ]);
    }
}

