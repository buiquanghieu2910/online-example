<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    public function index()
    {
        $classes = SchoolClass::withCount(['students', 'teachers', 'exams'])
            ->orderBy('start_year', 'desc')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.classes.index', compact('classes'));
    }

    public function create()
    {
        $teachers = User::where('role', 'teacher')->orderBy('name')->get();
        $students = User::where('role', 'student')->orderBy('name')->get();
        return view('admin.classes.create', compact('teachers', 'students'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:classes,code',
            'description' => 'nullable|string',
            'subject' => 'required|string|max:100',
            'start_year' => 'required|integer|min:2000|max:2100',
            'end_year' => 'required|integer|min:2000|max:2100|gte:start_year',
            'is_active' => 'boolean',
            'teacher_id' => 'required|exists:users,id',
            'student_ids' => 'nullable|array',
            'student_ids.*' => 'exists:users,id',
        ]);

        $class = SchoolClass::create($request->only(['name', 'code', 'description', 'subject', 'start_year', 'end_year', 'is_active']));

        // Attach teacher
        $class->teachers()->attach($request->teacher_id);

        // Attach students to class (many-to-many)
        if ($request->student_ids) {
            $class->students()->attach($request->student_ids);
        }

        return redirect()->route('admin.classes.index')
            ->with('success', 'Lớp học đã được tạo thành công!');
    }

    public function show(SchoolClass $class)
    {
        $class->load(['students', 'teachers', 'exams']);
        return view('admin.classes.show', compact('class'));
    }

    public function edit(SchoolClass $class)
    {
        $class->load(['teachers', 'students']);
        $availableTeachers = User::where('role', 'teacher')->orderBy('name')->get();
        $availableStudents = User::where('role', 'student')
            ->whereDoesntHave('schoolClasses', function($query) use ($class) {
                $query->where('class_id', $class->id);
            })
            ->orderBy('name')
            ->get();
        return view('admin.classes.edit', compact('class', 'availableTeachers', 'availableStudents'));
    }

    public function update(Request $request, SchoolClass $class)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:classes,code,' . $class->id,
            'description' => 'nullable|string',
            'subject' => 'required|string|max:100',
            'start_year' => 'required|integer|min:2000|max:2100',
            'end_year' => 'required|integer|min:2000|max:2100|gte:start_year',
            'is_active' => 'boolean',
        ]);

        $class->update($request->only(['name', 'code', 'description', 'subject', 'start_year', 'end_year', 'is_active']));

        return redirect()->route('admin.classes.index')
            ->with('success', 'Lớp học đã được cập nhật!');
    }

    public function destroy(SchoolClass $class)
    {
        $class->delete();

        return redirect()->route('admin.classes.index')
            ->with('success', 'Lớp học đã được xóa!');
    }

    public function manageStudents(SchoolClass $class)
    {
        $class->load('students');
        $availableStudents = User::where('role', 'student')
            ->whereDoesntHave('schoolClasses', function($query) use ($class) {
                $query->where('class_id', $class->id);
            })
            ->orderBy('name')
            ->get();

        return view('admin.classes.students', compact('class', 'availableStudents'));
    }

    public function addStudent(Request $request, SchoolClass $class)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
        ]);

        $class->students()->attach($request->student_id);

        return redirect()->route('admin.classes.students', $class)
            ->with('success', 'Học sinh đã được thêm vào lớp!');
    }

    public function removeStudent(SchoolClass $class, User $student)
    {
        $class->students()->detach($student->id);

        return redirect()->route('admin.classes.students', $class)
            ->with('success', 'Học sinh đã được xóa khỏi lớp!');
    }

    public function manageTeachers(SchoolClass $class)
    {
        $class->load('teachers');
        $availableTeachers = User::where('role', 'teacher')
            ->whereDoesntHave('teachingClasses', function($q) use ($class) {
                $q->where('class_id', $class->id);
            })
            ->orderBy('name')
            ->get();

        return view('admin.classes.teachers', compact('class', 'availableTeachers'));
    }

    public function addTeacher(Request $request, SchoolClass $class)
    {
        $request->validate([
            'teacher_id' => 'required|exists:users,id',
        ]);

        $class->teachers()->attach($request->teacher_id);

        return redirect()->route('admin.classes.teachers', $class)
            ->with('success', 'Giáo viên đã được thêm vào lớp!');
    }

    public function removeTeacher(SchoolClass $class, User $teacher)
    {
        $class->teachers()->detach($teacher->id);

        return redirect()->route('admin.classes.teachers', $class)
            ->with('success', 'Giáo viên đã được xóa khỏi lớp!');
    }
}
