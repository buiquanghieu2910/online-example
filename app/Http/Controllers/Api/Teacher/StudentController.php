<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $teacher = $request->user();

        $students = $teacher->students()
            ->with('schoolClasses:id,name')
            ->orderBy('name')
            ->get(['users.id', 'name', 'username', 'role']);

        return response()->json(['data' => $students]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $student = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'password' => $validated['password'],
            'role' => 'student',
        ]);

        $request->user()->students()->syncWithoutDetaching([$student->id]);

        return response()->json(['message' => 'Tạo học sinh thành công.']);
    }

    public function update(Request $request, User $student): JsonResponse
    {
        if ($student->role !== 'student' || ! $request->user()->students()->where('student_id', $student->id)->exists()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username,' . $student->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $student->update($validated);

        return response()->json(['message' => 'Cập nhật học sinh thành công.']);
    }

    public function destroy(Request $request, User $student): JsonResponse
    {
        if ($student->role !== 'student' || ! $request->user()->students()->where('student_id', $student->id)->exists()) {
            abort(403);
        }

        $request->user()->students()->detach($student->id);

        return response()->json(['message' => 'Đã gỡ học sinh khỏi danh sách phụ trách.']);
    }
}

