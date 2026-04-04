<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    public function index(): JsonResponse
    {
        $classes = SchoolClass::query()
            ->with(['teachers:id,name', 'students:id,name'])
            ->withCount(['teachers', 'students', 'exams'])
            ->orderByDesc('start_year')
            ->orderBy('name')
            ->get();

        $teachers = User::query()->where('role', 'teacher')->orderBy('name')->get(['id', 'name']);
        $students = User::query()->where('role', 'student')->orderBy('name')->get(['id', 'name']);

        return response()->json([
            'data' => [
                'classes' => $classes,
                'teachers' => $teachers,
                'students' => $students,
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:classes,code'],
            'description' => ['nullable', 'string'],
            'subject' => ['required', 'string', 'max:100'],
            'start_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'end_year' => ['required', 'integer', 'min:2000', 'max:2100', 'gte:start_year'],
            'is_active' => ['nullable', 'boolean'],
            'teacher_ids' => ['required', 'array', 'min:1'],
            'teacher_ids.*' => ['integer', 'exists:users,id'],
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $class = SchoolClass::create([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'description' => $validated['description'] ?? null,
            'subject' => $validated['subject'],
            'start_year' => $validated['start_year'],
            'end_year' => $validated['end_year'],
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        $class->teachers()->sync($validated['teacher_ids']);
        $class->students()->sync($validated['student_ids'] ?? []);

        return response()->json([
            'message' => 'Tạo lớp học thành công.',
        ]);
    }

    public function update(Request $request, SchoolClass $class): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:classes,code,' . $class->id],
            'description' => ['nullable', 'string'],
            'subject' => ['required', 'string', 'max:100'],
            'start_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'end_year' => ['required', 'integer', 'min:2000', 'max:2100', 'gte:start_year'],
            'is_active' => ['nullable', 'boolean'],
            'teacher_ids' => ['required', 'array', 'min:1'],
            'teacher_ids.*' => ['integer', 'exists:users,id'],
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $class->update([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'description' => $validated['description'] ?? null,
            'subject' => $validated['subject'],
            'start_year' => $validated['start_year'],
            'end_year' => $validated['end_year'],
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        $class->teachers()->sync($validated['teacher_ids']);
        $class->students()->sync($validated['student_ids'] ?? []);

        return response()->json([
            'message' => 'Cập nhật lớp học thành công.',
        ]);
    }

    public function destroy(SchoolClass $class): JsonResponse
    {
        $class->delete();

        return response()->json([
            'message' => 'Xóa lớp học thành công.',
        ]);
    }
}

