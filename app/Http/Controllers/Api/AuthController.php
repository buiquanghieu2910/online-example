<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\IMaintenanceModeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct(private IMaintenanceModeService $maintenanceModeService)
    {
    }

    public function me(Request $request): JsonResponse
    {
        if (! $request->user()) {
            return response()->json(['data' => null]);
        }

        return response()->json([
            'data' => [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'username' => $request->user()->username,
                'role' => $request->user()->role,
            ],
        ]);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $remember = (bool) ($credentials['remember'] ?? false);

        if (! Auth::attempt([
            'username' => $credentials['username'],
            'password' => $credentials['password'],
        ], $remember)) {
            return response()->json([
                'message' => 'Tên đăng nhập hoặc mật khẩu không chính xác.',
            ], 422);
        }

        $request->session()->regenerate();

        if ($this->maintenanceModeService->isEnabled() && $request->user()?->role !== 'admin') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json([
                'message' => $this->maintenanceModeService->getMessage(),
                'code' => 'MAINTENANCE_MODE',
            ], 503);
        }

        return response()->json([
            'data' => [
                'user' => [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'username' => $request->user()->username,
                    'role' => $request->user()->role,
                ],
                'home' => $this->homeByRole($request->user()->role),
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Đăng xuất thành công']);
    }

    private function homeByRole(string $role): string
    {
        return match ($role) {
            'admin' => '/app/admin/dashboard',
            'teacher' => '/app/teacher/dashboard',
            default => '/app/student/dashboard',
        };
    }
}



