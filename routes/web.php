<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExamController as AdminExamController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Teacher\ExamController as TeacherExamController;
use App\Http\Controllers\Teacher\QuestionController as TeacherQuestionController;
use App\Http\Controllers\Teacher\UserController as TeacherUserController;
use App\Http\Controllers\User\ExamController as UserExamController;
use App\Http\Controllers\User\ResultController;

Route::get('/', function () {
    if (auth()->check()) {
        if (auth()->user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif (auth()->user()->role === 'teacher') {
            return redirect()->route('teacher.dashboard');
        }
        return redirect()->route('student.exams.index');
    }
    return view('welcome');
});

// Auth Routes
Route::get('/login', [\App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [\App\Http\Controllers\Auth\LoginController::class, 'login']);
Route::post('/logout', [\App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Redirect /admin to /admin/dashboard
    Route::get('/', function () {
        return redirect()->route('admin.dashboard');
    });
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::resource('users', AdminUserController::class);
    Route::resource('exams', AdminExamController::class);
    Route::resource('classes', \App\Http\Controllers\Admin\ClassController::class);
    
    // Class management routes
    Route::get('classes/{class}/students', [\App\Http\Controllers\Admin\ClassController::class, 'manageStudents'])->name('classes.students');
    Route::post('classes/{class}/students', [\App\Http\Controllers\Admin\ClassController::class, 'addStudent'])->name('classes.students.add');
    Route::delete('classes/{class}/students/{student}', [\App\Http\Controllers\Admin\ClassController::class, 'removeStudent'])->name('classes.students.remove');
    Route::get('classes/{class}/teachers', [\App\Http\Controllers\Admin\ClassController::class, 'manageTeachers'])->name('classes.teachers');
    Route::post('classes/{class}/teachers', [\App\Http\Controllers\Admin\ClassController::class, 'addTeacher'])->name('classes.teachers.add');
    Route::delete('classes/{class}/teachers/{teacher}', [\App\Http\Controllers\Admin\ClassController::class, 'removeTeacher'])->name('classes.teachers.remove');
    
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

// Teacher Routes
Route::middleware(['auth', 'teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    // Redirect /teacher to /teacher/dashboard
    Route::get('/', function () {
        return redirect()->route('teacher.dashboard');
    });
    
    Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('dashboard');
    
    Route::resource('classes', \App\Http\Controllers\Teacher\ClassController::class);
    Route::get('classes/{class}/students', [\App\Http\Controllers\Teacher\ClassController::class, 'manageStudents'])->name('classes.students');
    Route::post('classes/{class}/students', [\App\Http\Controllers\Teacher\ClassController::class, 'addStudent'])->name('classes.students.add');
    Route::post('classes/{class}/students/add-multiple', [\App\Http\Controllers\Teacher\ClassController::class, 'addMultipleStudents'])->name('classes.students.addMultiple');
    Route::delete('classes/{class}/students/{student}', [\App\Http\Controllers\Teacher\ClassController::class, 'removeStudent'])->name('classes.students.remove');
    Route::delete('classes/{class}/students', [\App\Http\Controllers\Teacher\ClassController::class, 'removeMultipleStudents'])->name('classes.students.removeMultiple');
    
    Route::resource('students', TeacherUserController::class)->names([
        'index' => 'students.index',
        'create' => 'students.create',
        'store' => 'students.store',
        'show' => 'students.show',
        'edit' => 'students.edit',
        'update' => 'students.update',
        'destroy' => 'students.destroy',
    ]);
    Route::resource('exams', TeacherExamController::class);
    
    Route::prefix('exams/{exam}')->name('questions.')->group(function () {
        Route::get('questions', [TeacherQuestionController::class, 'index'])->name('index');
        Route::get('questions/create', [TeacherQuestionController::class, 'create'])->name('create');
        Route::post('questions', [TeacherQuestionController::class, 'store'])->name('store');
        Route::get('questions/{question}', [TeacherQuestionController::class, 'show'])->name('show');
        Route::get('questions/{question}/edit', [TeacherQuestionController::class, 'edit'])->name('edit');
        Route::put('questions/{question}', [TeacherQuestionController::class, 'update'])->name('update');
        Route::delete('questions/{question}', [TeacherQuestionController::class, 'destroy'])->name('destroy');
    });
    
    // Assign users to exam
    Route::get('exams/{exam}/assign', [TeacherExamController::class, 'showAssign'])->name('exams.assign');
    Route::post('exams/{exam}/assign', [TeacherExamController::class, 'assignUsers'])->name('exams.assign.store');
    Route::delete('exams/{exam}/unassign/{user}', [TeacherExamController::class, 'unassignUser'])->name('exams.unassign');
    
    // Reset exam for user
    Route::post('results/{userExam}/reset', [TeacherExamController::class, 'resetExam'])->name('results.reset');
    
    // View exam history for user
    Route::get('exams/{exam}/history/{user}', [TeacherExamController::class, 'showHistory'])->name('exams.history');
    
    // Attendance routes
    Route::get('attendances', [\App\Http\Controllers\Teacher\AttendanceController::class, 'index'])->name('attendances.index');
    Route::post('attendances', [\App\Http\Controllers\Teacher\AttendanceController::class, 'store'])->name('attendances.store');
    Route::get('attendances/statistics', [\App\Http\Controllers\Teacher\AttendanceController::class, 'statistics'])->name('attendances.statistics');
});

// Auth Routes
Route::get('/login', [\App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [\App\Http\Controllers\Auth\LoginController::class, 'login']);
Route::post('/logout', [\App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

// Profile Routes (for all authenticated users)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password.update');
});

// Student Routes
Route::middleware(['auth', 'student'])->prefix('student')->name('student.')->group(function () {
    // Redirect /student to /student/exams
    Route::get('/', function () {
        return redirect()->route('student.exams.index');
    });
    
    Route::get('/exams', [UserExamController::class, 'index'])->name('exams.index');
    Route::get('/exams/{exam}', [UserExamController::class, 'show'])->name('exams.show');
    Route::post('/exams/{exam}/start', [UserExamController::class, 'start'])->name('exams.start');
    Route::get('/exams/{exam}/take', [UserExamController::class, 'take'])->name('exams.take');
    Route::post('/exams/{exam}/submit', [UserExamController::class, 'submit'])->name('exams.submit');
    
    Route::get('/results', [ResultController::class, 'index'])->name('results.index');
    Route::get('/results/{userExam}', [ResultController::class, 'show'])->name('results.show');
});
