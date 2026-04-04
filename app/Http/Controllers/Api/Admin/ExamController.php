<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ExamController extends Controller
{
    private function scoreColumn(): string
    {
        return Schema::hasColumn('exams', 'pass_score') ? 'pass_score' : 'min_score';
    }

    public function index(): JsonResponse
    {
        $scoreColumn = $this->scoreColumn();

        $exams = Exam::query()
            ->with('schoolClass:id,name')
            ->withCount(['questions', 'assignedUsers'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function (Exam $exam) use ($scoreColumn) {
                return [
                    'id' => $exam->id,
                    'title' => $exam->title,
                    'description' => $exam->description,
                    'duration' => $exam->duration,
                    'pass_score' => (float) $exam->{$scoreColumn},
                    'is_active' => (bool) $exam->is_active,
                    'start_time' => optional($exam->start_time)->toDateTimeString(),
                    'end_time' => optional($exam->end_time)->toDateTimeString(),
                    'class_id' => $exam->class_id,
                    'class_name' => $exam->schoolClass?->name,
                    'questions_count' => $exam->questions_count,
                    'assigned_users_count' => $exam->assigned_users_count,
                ];
            });

        return response()->json(['data' => $exams]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration' => ['required', 'integer', 'min:1'],
            'pass_score' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'start_time' => ['nullable', 'date'],
            'end_time' => ['nullable', 'date', 'after:start_time'],
            'class_id' => ['nullable', 'exists:classes,id'],
        ]);

        $scoreColumn = $this->scoreColumn();

        $payload = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'duration' => $validated['duration'],
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'start_time' => $validated['start_time'] ?? null,
            'end_time' => $validated['end_time'] ?? null,
            'class_id' => $validated['class_id'] ?? null,
            $scoreColumn => $validated['pass_score'],
        ];

        $exam = Exam::create($payload);

        return response()->json([
            'message' => 'Tạo bài thi thành công.',
            'data' => ['id' => $exam->id],
        ]);
    }

    public function update(Request $request, Exam $exam): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration' => ['required', 'integer', 'min:1'],
            'pass_score' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'start_time' => ['nullable', 'date'],
            'end_time' => ['nullable', 'date', 'after:start_time'],
            'class_id' => ['nullable', 'exists:classes,id'],
        ]);

        $scoreColumn = $this->scoreColumn();

        $exam->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'duration' => $validated['duration'],
            'is_active' => (bool) ($validated['is_active'] ?? false),
            'start_time' => $validated['start_time'] ?? null,
            'end_time' => $validated['end_time'] ?? null,
            'class_id' => $validated['class_id'] ?? null,
            $scoreColumn => $validated['pass_score'],
        ]);

        return response()->json([
            'message' => 'Cập nhật bài thi thành công.',
        ]);
    }

    public function destroy(Exam $exam): JsonResponse
    {
        $exam->delete();

        return response()->json([
            'message' => 'Xóa bài thi thành công.',
        ]);
    }

    public function assignData(Exam $exam): JsonResponse
    {
        $users = User::query()
            ->where('role', 'student')
            ->orderBy('name')
            ->get(['id', 'name', 'username']);

        $assignedIds = $exam->assignedUsers()->pluck('users.id');

        return response()->json([
            'data' => [
                'exam' => [
                    'id' => $exam->id,
                    'title' => $exam->title,
                ],
                'users' => $users,
                'assigned_ids' => $assignedIds,
            ],
        ]);
    }

    public function assignUsers(Request $request, Exam $exam): JsonResponse
    {
        $validated = $request->validate([
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $exam->assignedUsers()->sync($validated['user_ids']);

        return response()->json([
            'message' => 'Cập nhật phân công học sinh thành công.',
        ]);
    }
}

