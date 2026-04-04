<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\SchoolClass;
use App\Models\User;
use App\Models\UserExam;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function overview(Request $request): JsonResponse
    {
        $user = $request->user();

        $stats = match ($user->role) {
            'admin' => [
                ['key' => 'users', 'label' => 'Người dùng', 'value' => User::count()],
                ['key' => 'classes', 'label' => 'Lớp học', 'value' => SchoolClass::count()],
                ['key' => 'exams', 'label' => 'Bài thi', 'value' => Exam::count()],
            ],
            'teacher' => [
                ['key' => 'classes', 'label' => 'Lớp đang dạy', 'value' => $user->teachingClasses()->count()],
                ['key' => 'students', 'label' => 'Học sinh phụ trách', 'value' => $user->students()->count()],
                ['key' => 'exams', 'label' => 'Bài thi được giao', 'value' => Exam::count()],
            ],
            default => [
                ['key' => 'assigned_exams', 'label' => 'Bài thi được giao', 'value' => $user->assignedExams()->count()],
                ['key' => 'completed', 'label' => 'Đã hoàn thành', 'value' => UserExam::where('user_id', $user->id)->where('status', 'completed')->count()],
                ['key' => 'in_progress', 'label' => 'Đang làm', 'value' => UserExam::where('user_id', $user->id)->where('status', 'in_progress')->count()],
            ],
        };

        return response()->json([
            'data' => [
                'stats' => $stats,
            ],
        ]);
    }
}

