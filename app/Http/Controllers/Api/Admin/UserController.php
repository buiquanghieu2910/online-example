<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::query()
            ->with(['teachers:id,name', 'students:id,name'])
            ->orderBy('created_at', 'desc')
            ->get(['id', 'name', 'username', 'role', 'created_at']);

        $teachers = User::query()
            ->where('role', 'teacher')
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'data' => [
                'users' => $users,
                'teachers' => $teachers,
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:student,teacher,admin'],
            'teacher_ids' => ['nullable', 'array'],
            'teacher_ids.*' => ['integer', Rule::exists('users', 'id')->where('role', 'teacher')],
        ]);

        $teacherIds = $validated['teacher_ids'] ?? [];
        unset($validated['teacher_ids']);

        $user = User::create($validated);

        if ($user->role === 'student') {
            $user->teachers()->sync($teacherIds);
        }

        return response()->json([
            'message' => 'Tạo người dùng thành công.',
            'data' => $user->only(['id', 'name', 'username', 'role']),
        ]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username,' . $user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:student,teacher,admin'],
            'teacher_ids' => ['nullable', 'array'],
            'teacher_ids.*' => ['integer', Rule::exists('users', 'id')->where('role', 'teacher')],
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $teacherIds = $validated['teacher_ids'] ?? [];
        unset($validated['teacher_ids']);

        $user->update($validated);

        if ($user->role === 'student') {
            $user->teachers()->sync($teacherIds);
        } else {
            $user->teachers()->detach();
        }

        return response()->json([
            'message' => 'Cập nhật người dùng thành công.',
            'data' => $user->only(['id', 'name', 'username', 'role']),
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        if ($user->role === 'admin') {
            return response()->json([
                'message' => 'Không thể xóa tài khoản quản trị viên.',
            ], 422);
        }

        if ($user->role === 'teacher' && $user->students()->exists()) {
            return response()->json([
                'message' => 'Không thể xóa giáo viên đang có học sinh phụ trách.',
            ], 422);
        }

        $user->delete();

        return response()->json([
            'message' => 'Xóa người dùng thành công.',
        ]);
    }
}
