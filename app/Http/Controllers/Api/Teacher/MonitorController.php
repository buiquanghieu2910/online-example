<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\UserExam;
use App\Services\IExamMonitoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MonitorController extends Controller
{
    public function __construct(private IExamMonitoringService $monitoringService)
    {
    }

    public function activeAttempts(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'class_id' => ['nullable', 'integer'],
            'exam_id' => ['nullable', 'integer'],
            'student_id' => ['nullable', 'integer'],
        ]);

        $data = $this->monitoringService->getActiveAttemptsForTeacher($request->user()->id, $validated);

        return response()->json(['data' => $data]);
    }

    public function timeline(Request $request, UserExam $userExam): JsonResponse
    {
        $data = $this->monitoringService->getTimelineForTeacher($request->user()->id, $userExam->id);
        if (! $data) {
            return response()->json(['message' => 'Không tìm thấy phiên làm bài.'], 404);
        }

        return response()->json(['data' => $data]);
    }
}
