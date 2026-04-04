<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $exams = match ($user->role) {
            'student' => $user->assignedExams()
                ->select('exams.id', 'title', 'duration', 'pass_score', 'is_active', 'start_time', 'end_time')
                ->latest('exams.created_at')
                ->get(),
            default => Exam::query()
                ->select('id', 'title', 'duration', 'pass_score', 'is_active', 'start_time', 'end_time')
                ->latest('created_at')
                ->get(),
        };

        return response()->json([
            'data' => $exams,
        ]);
    }
}

