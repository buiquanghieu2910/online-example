<?php

namespace App\Http\Middleware;

use App\Services\IMaintenanceModeService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceModeMiddleware
{
    public function __construct(private IMaintenanceModeService $maintenanceModeService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->maintenanceModeService->isEnabled()) {
            return $next($request);
        }

        $user = $request->user();
        if ($user && $user->role === 'admin') {
            return $next($request);
        }

        if (
            $request->is('api/auth/login')
            || $request->is('api/auth/me')
            || $request->is('csrf-token')
            || $request->is('up')
            || $request->is('app/login')
            || $request->is('login')
        ) {
            return $next($request);
        }

        $message = $this->maintenanceModeService->getMessage();

        if ($request->is('api/*')) {
            return response()->json([
                'message' => $message,
                'code' => 'MAINTENANCE_MODE',
            ], 503);
        }

        return response()->view('maintenance', ['message' => $message], 503);
    }
}
