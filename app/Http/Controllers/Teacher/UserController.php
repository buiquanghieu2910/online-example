<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Services\IUserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        private IUserService $userService
    ) {}

    /**
     * Display a listing of the students.
     */
    public function index()
    {
        $users = $this->userService->getUsersByTeacher(auth()->id(), 10);
        return view('teacher.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new student.
     */
    public function create()
    {
        return view('teacher.users.create');
    }

    /**
     * Store a newly created student.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $validated['role'] = 'student';
        $student = $this->userService->createUser($validated);
        
        // Attach student to current teacher
        auth()->user()->students()->attach($student->id);

        return redirect()->route('teacher.users.index')
            ->with('success', 'Tạo học sinh thành công.');
    }

    /**
     * Display the specified student.
     */
    public function show(int $id)
    {
        $user = $this->userService->getUserWithExams($id);
        
        if ($user->role !== 'student' || !auth()->user()->students->contains($id)) {
            abort(403, 'Unauthorized access.');
        }
        
        return view('teacher.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified student.
     */
    public function edit(int $id)
    {
        $user = $this->userService->getUserById($id);
        if ($user->role !== 'student' || !auth()->user()->students->contains($id)) {
            abort(403, 'Unauthorized access.');
        }
        return view('teacher.users.edit', compact('user'));
    }

    /**
     * Update the specified student.
     */
    public function update(Request $request, int $id)
    {
        $user = $this->userService->getUserById($id);
        
        if ($user->role !== 'student' || !auth()->user()->students->contains($id)) {
            abort(403, 'Unauthorized access.');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $this->userService->updateUser($id, $validated);

        return redirect()->route('teacher.users.index')
            ->with('success', 'Cập nhật học sinh thành công.');
    }

    /**
     * Remove the specified student.
     */
    public function destroy(int $id)
    {
        $user = $this->userService->getUserById($id);
        
        if ($user->role !== 'student' || !auth()->user()->students->contains($id)) {
            abort(403, 'Unauthorized access.');
        }
        
        // Detach from current teacher before deleting (or just detach)
        auth()->user()->students()->detach($id);

        return redirect()->route('teacher.users.index')
            ->with('success', 'Xóa học sinh thành công.');
    }
}
