<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExamController as AdminExamController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\User\ExamController as UserExamController;
use App\Http\Controllers\User\ResultController;

Route::get('/', function () {
    if (auth()->check()) {
        if (auth()->user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('user.exams.index');
    }
    return view('welcome');
});

// Auth Routes
Route::get('/login', [\App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [\App\Http\Controllers\Auth\LoginController::class, 'login']);
Route::post('/logout', [\App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::resource('users', AdminUserController::class);
    Route::resource('exams', AdminExamController::class);
    
    Route::prefix('exams/{exam}')->name('questions.')->group(function () {
        Route::get('questions', [QuestionController::class, 'index'])->name('index');
        Route::get('questions/create', [QuestionController::class, 'create'])->name('create');
        Route::post('questions', [QuestionController::class, 'store'])->name('store');
        Route::get('questions/{question}', [QuestionController::class, 'show'])->name('show');
        Route::get('questions/{question}/edit', [QuestionController::class, 'edit'])->name('edit');
        Route::put('questions/{question}', [QuestionController::class, 'update'])->name('update');
        Route::delete('questions/{question}', [QuestionController::class, 'destroy'])->name('destroy');
    });
    
    // Reset exam for user
    Route::post('results/{userExam}/reset', [AdminExamController::class, 'resetExam'])->name('results.reset');
    
    // Assign users to exam
    Route::get('exams/{exam}/assign', [AdminExamController::class, 'showAssign'])->name('exams.assign');
    Route::post('exams/{exam}/assign', [AdminExamController::class, 'assignUsers'])->name('exams.assign.store');
    Route::delete('exams/{exam}/unassign/{user}', [AdminExamController::class, 'unassignUser'])->name('exams.unassign');
    
    // View exam history for user
    Route::get('exams/{exam}/history/{user}', [AdminExamController::class, 'showHistory'])->name('exams.history');
    
    // Grading routes
    Route::get('grading/pending', [AdminExamController::class, 'pendingGrading'])->name('grading.pending');
    Route::get('grading/exam/{exam}/users', [AdminExamController::class, 'examPendingUsers'])->name('grading.exam.users');
    Route::get('grading/{userExam}', [AdminExamController::class, 'showGrading'])->name('grading.show');
    Route::post('grading/{userExam}', [AdminExamController::class, 'submitGrading'])->name('grading.submit');
});

// Auth Routes
Route::get('/login', [\App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [\App\Http\Controllers\Auth\LoginController::class, 'login']);
Route::post('/logout', [\App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

// User Routes
Route::middleware(['auth', 'user'])->prefix('user')->name('user.')->group(function () {
    Route::get('/exams', [UserExamController::class, 'index'])->name('exams.index');
    Route::get('/exams/{exam}', [UserExamController::class, 'show'])->name('exams.show');
    Route::post('/exams/{exam}/start', [UserExamController::class, 'start'])->name('exams.start');
    Route::get('/exams/{exam}/take', [UserExamController::class, 'take'])->name('exams.take');
    Route::post('/exams/{exam}/submit', [UserExamController::class, 'submit'])->name('exams.submit');
    
    Route::get('/results', [ResultController::class, 'index'])->name('results.index');
    Route::get('/results/{userExam}', [ResultController::class, 'show'])->name('results.show');
});
