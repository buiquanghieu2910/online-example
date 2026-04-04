<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect(match (auth()->user()->role) {
            'admin' => '/app/admin/dashboard',
            'teacher' => '/app/teacher/dashboard',
            default => '/app/student/dashboard',
        });
    }

    return redirect('/app/login');
});

Route::get('/login', function () {
    return redirect('/app/login');
})->name('login')->middleware('guest');

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/app/login');
})->name('logout');

Route::middleware(['auth', 'admin'])->get('/admin/{any?}', function () {
    return redirect('/app/admin/dashboard');
})->where('any', '.*');

Route::middleware(['auth', 'teacher'])->get('/teacher/{any?}', function () {
    return redirect('/app/teacher/dashboard');
})->where('any', '.*');

Route::middleware(['auth', 'student'])->get('/student/{any?}', function () {
    return redirect('/app/student/dashboard');
})->where('any', '.*');

Route::middleware('auth')->get('/profile/{any?}', function () {
    return redirect('/app/profile');
})->where('any', '.*');

Route::view('/app/{any?}', 'spa')->where('any', '.*');
