<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ExamController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\Admin\ClassController as AdminClassController;
use App\Http\Controllers\Api\Admin\ExamController as AdminExamController;
use App\Http\Controllers\Api\Admin\GradingController as AdminGradingController;
use App\Http\Controllers\Api\Admin\QuestionController as AdminQuestionController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\Teacher\AttendanceController as TeacherAttendanceController;
use App\Http\Controllers\Api\Teacher\ClassController as TeacherClassController;
use App\Http\Controllers\Api\Teacher\ExamController as TeacherExamController;
use App\Http\Controllers\Api\Teacher\QuestionController as TeacherQuestionController;
use App\Http\Controllers\Api\Teacher\StudentController as TeacherStudentController;
use App\Http\Controllers\Api\Student\ExamController as StudentExamController;
use App\Http\Controllers\Api\Student\ResultController as StudentResultController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');
        Route::get('/me', [AuthController::class, 'me']);
    });

    Route::middleware('auth')->group(function () {
        Route::get('/dashboard/overview', [DashboardController::class, 'overview']);
        Route::get('/exams', [ExamController::class, 'index']);

        Route::get('/profile', [ProfileController::class, 'show']);
        Route::put('/profile', [ProfileController::class, 'update']);
        Route::put('/profile/password', [ProfileController::class, 'updatePassword']);

        Route::prefix('admin')->middleware('admin')->group(function () {
            Route::get('/users', [AdminUserController::class, 'index']);
            Route::post('/users', [AdminUserController::class, 'store']);
            Route::put('/users/{user}', [AdminUserController::class, 'update']);
            Route::delete('/users/{user}', [AdminUserController::class, 'destroy']);

            Route::get('/classes', [AdminClassController::class, 'index']);
            Route::post('/classes', [AdminClassController::class, 'store']);
            Route::put('/classes/{class}', [AdminClassController::class, 'update']);
            Route::delete('/classes/{class}', [AdminClassController::class, 'destroy']);

            Route::get('/exams', [AdminExamController::class, 'index']);
            Route::post('/exams', [AdminExamController::class, 'store']);
            Route::put('/exams/{exam}', [AdminExamController::class, 'update']);
            Route::delete('/exams/{exam}', [AdminExamController::class, 'destroy']);
            Route::get('/exams/{exam}/assign', [AdminExamController::class, 'assignData']);
            Route::put('/exams/{exam}/assign', [AdminExamController::class, 'assignUsers']);

            Route::get('/exams/{exam}/questions', [AdminQuestionController::class, 'index']);
            Route::post('/exams/{exam}/questions', [AdminQuestionController::class, 'store']);
            Route::put('/questions/{question}', [AdminQuestionController::class, 'update']);
            Route::delete('/questions/{question}', [AdminQuestionController::class, 'destroy']);

            Route::get('/grading/pending', [AdminGradingController::class, 'pending']);
            Route::get('/grading/exams/{exam}/users', [AdminGradingController::class, 'examUsers']);
            Route::get('/grading/{userExam}', [AdminGradingController::class, 'show']);
            Route::post('/grading/{userExam}', [AdminGradingController::class, 'submit']);
        });

        Route::prefix('teacher')->middleware('teacher')->group(function () {
            Route::get('/classes', [TeacherClassController::class, 'index']);
            Route::post('/classes', [TeacherClassController::class, 'store']);
            Route::put('/classes/{class}', [TeacherClassController::class, 'update']);
            Route::delete('/classes/{class}', [TeacherClassController::class, 'destroy']);

            Route::get('/students', [TeacherStudentController::class, 'index']);
            Route::post('/students', [TeacherStudentController::class, 'store']);
            Route::put('/students/{student}', [TeacherStudentController::class, 'update']);
            Route::delete('/students/{student}', [TeacherStudentController::class, 'destroy']);

            Route::get('/exams', [TeacherExamController::class, 'index']);
            Route::post('/exams', [TeacherExamController::class, 'store']);
            Route::put('/exams/{exam}', [TeacherExamController::class, 'update']);
            Route::delete('/exams/{exam}', [TeacherExamController::class, 'destroy']);
            Route::get('/exams/{exam}/assign', [TeacherExamController::class, 'assignData']);
            Route::put('/exams/{exam}/assign', [TeacherExamController::class, 'assignUsers']);

            Route::get('/exams/{exam}/questions', [TeacherQuestionController::class, 'index']);
            Route::post('/exams/{exam}/questions', [TeacherQuestionController::class, 'store']);
            Route::put('/questions/{question}', [TeacherQuestionController::class, 'update']);
            Route::delete('/questions/{question}', [TeacherQuestionController::class, 'destroy']);

            Route::get('/attendances', [TeacherAttendanceController::class, 'index']);
            Route::post('/attendances', [TeacherAttendanceController::class, 'store']);
            Route::get('/attendances/statistics', [TeacherAttendanceController::class, 'statistics']);
        });

        Route::prefix('student')->middleware('student')->group(function () {
            Route::get('/exams', [StudentExamController::class, 'index']);
            Route::get('/exams/{exam}', [StudentExamController::class, 'show']);
            Route::post('/exams/{exam}/start', [StudentExamController::class, 'start']);
            Route::get('/exams/{exam}/take', [StudentExamController::class, 'take']);
            Route::post('/exams/{exam}/autosave', [StudentExamController::class, 'autosave']);
            Route::post('/exams/{exam}/submit', [StudentExamController::class, 'submit']);

            Route::get('/results', [StudentResultController::class, 'index']);
            Route::get('/results/{userExam}', [StudentResultController::class, 'show']);
        });
    });
});



