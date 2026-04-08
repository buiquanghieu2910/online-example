<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $teacher = $request->user();

        $classes = $teacher->teachingClasses()
            ->with(['students:id,name,username'])
            ->withCount(['students', 'exams'])
            ->orderByDesc('start_year')
            ->orderBy('name')
            ->get();

        $students = User::query()
            ->where('role', 'student')
            ->whereHas('teachers', fn ($query) => $query->where('teacher_id', $teacher->id))
            ->orderBy('name')
            ->get(['id', 'name', 'username']);

        return response()->json([
            'data' => [
                'classes' => $classes,
                'students' => $students,
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $teacher = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:classes,code'],
            'description' => ['nullable', 'string'],
            'subject' => ['required', 'string', 'max:100'],
            'start_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'end_year' => ['required', 'integer', 'min:2000', 'max:2100', 'gte:start_year'],
            'is_active' => ['nullable', 'boolean'],
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $allowedStudentIds = $teacher->students()->pluck('users.id')->map(fn ($id) => (int) $id)->all();
        $studentIds = collect($validated['student_ids'] ?? [])->map(fn ($id) => (int) $id)->all();
        $invalidStudentIds = array_values(array_diff($studentIds, $allowedStudentIds));
        if (! empty($invalidStudentIds)) {
            return response()->json([
                'message' => 'Danh sách học sinh có phần tử không thuộc phạm vi phụ trách.',
                'errors' => [
                    'student_ids' => ['Danh sách học sinh có phần tử không thuộc phạm vi phụ trách.'],
                ],
            ], 422);
        }

        $class = SchoolClass::create([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'description' => $validated['description'] ?? null,
            'subject' => $validated['subject'],
            'start_year' => $validated['start_year'],
            'end_year' => $validated['end_year'],
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        $class->teachers()->sync([$teacher->id]);
        $class->students()->sync($studentIds);

        return response()->json(['message' => 'Tạo lớp học thành công.']);
    }

    public function update(Request $request, SchoolClass $class): JsonResponse
    {
        $teacher = $request->user();

        if (! $class->teachers()->where('teacher_id', $teacher->id)->exists()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:classes,code,' . $class->id],
            'description' => ['nullable', 'string'],
            'subject' => ['required', 'string', 'max:100'],
            'start_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'end_year' => ['required', 'integer', 'min:2000', 'max:2100', 'gte:start_year'],
            'is_active' => ['nullable', 'boolean'],
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $allowedStudentIds = $teacher->students()->pluck('users.id')->map(fn ($id) => (int) $id)->all();
        $studentIds = collect($validated['student_ids'] ?? [])->map(fn ($id) => (int) $id)->all();
        $invalidStudentIds = array_values(array_diff($studentIds, $allowedStudentIds));
        if (! empty($invalidStudentIds)) {
            return response()->json([
                'message' => 'Danh sách học sinh có phần tử không thuộc phạm vi phụ trách.',
                'errors' => [
                    'student_ids' => ['Danh sách học sinh có phần tử không thuộc phạm vi phụ trách.'],
                ],
            ], 422);
        }

        $class->update([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'description' => $validated['description'] ?? null,
            'subject' => $validated['subject'],
            'start_year' => $validated['start_year'],
            'end_year' => $validated['end_year'],
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        $class->students()->sync($studentIds);

        return response()->json(['message' => 'Cập nhật lớp học thành công.']);
    }

    public function destroy(Request $request, SchoolClass $class): JsonResponse
    {
        if (! $class->teachers()->where('teacher_id', $request->user()->id)->exists()) {
            abort(403);
        }

        $class->delete();

        return response()->json(['message' => 'Xóa lớp học thành công.']);
    }
}
