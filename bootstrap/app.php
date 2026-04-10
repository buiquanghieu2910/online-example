<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        \App\Console\Commands\AutoSubmitExpiredExams::class,
        \App\Console\Commands\SystemMaintenanceCommand::class,
    ])
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('exams:auto-submit-expired')
            ->everyMinute()
            ->withoutOverlapping();
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'teacher' => \App\Http\Middleware\TeacherMiddleware::class,
            'student' => \App\Http\Middleware\StudentMiddleware::class,
            'user' => \App\Http\Middleware\UserMiddleware::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\MaintenanceModeMiddleware::class,
        ]);

        $middleware->api(append: [
            \App\Http\Middleware\MaintenanceModeMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
