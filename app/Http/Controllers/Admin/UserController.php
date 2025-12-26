<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\IUserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        private IUserService $userService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = $this->userService->getPaginatedUsers(10);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $teachers = \App\Models\User::where('role', 'teacher')->get();
        return view('admin.users.create', compact('teachers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:student,teacher',
            'teacher_ids' => 'nullable|array|required_if:role,student',
            'teacher_ids.*' => 'exists:users,id',
        ]);

        $teacherIds = $validated['teacher_ids'] ?? [];
        unset($validated['teacher_ids']);
        
        $user = $this->userService->createUser($validated);
        
        // If student, attach to teachers
        if ($user->role === 'student' && !empty($teacherIds)) {
            $user->teachers()->attach($teacherIds);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Tạo người dùng thành công.');
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $user = $this->userService->getUserWithExams($id);
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id)
    {
        $user = $this->userService->getUserById($id);
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        $user = $this->userService->getUserById($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $this->userService->updateUser($id, $validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'Cập nhật người dùng thành công.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $user = $this->userService->getUserById($id);
        
        if ($user->role === 'admin') {
            return redirect()->route('admin.users.index')
                ->with('error', 'Không thể xóa tài khoản admin.');
        }
        
        if ($user->role === 'teacher' && $user->students()->count() > 0) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Không thể xóa giáo viên đang có học sinh.');
        }

        $this->userService->deleteUser($id);

        return redirect()->route('admin.users.index')
            ->with('success', 'Xóa người dùng thành công.');
    }
}
