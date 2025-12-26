<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    public function index()
    {
        $teacher = auth()->user();
        $classes = $teacher->teachingClasses()
            ->withCount(['students', 'exams'])
            ->orderBy('start_year', 'desc')
            ->orderBy('name')
            ->get();

        return view('teacher.classes.index', compact('classes'));
    }

    public function create()
    {
        $students = User::where('role', 'student')
            ->orderBy('name')
            ->get();

        return view('teacher.classes.create', compact('students'));
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
            'student_ids' => 'nullable|array',
            'student_ids.*' => 'exists:users,id',
        ]);

        $class = SchoolClass::create(array_merge(
            $request->only(['name', 'code', 'description', 'subject', 'start_year', 'end_year']),
            ['is_active' => true]
        ));

        // Attach teacher to class
        $class->teachers()->attach(auth()->id());

        // Attach students to class (many-to-many)
        if ($request->student_ids) {
            $class->students()->attach($request->student_ids);
        }

        return redirect()->route('teacher.classes.index')
            ->with('success', 'Lớp học đã được tạo thành công!');
    }

    public function show(SchoolClass $class)
    {
        // Check if teacher owns this class
        if (!$class->teachers->contains(auth()->id())) {
            abort(403);
        }

        $class->load(['students', 'exams']);
        return view('teacher.classes.show', compact('class'));
    }

    public function edit(SchoolClass $class)
    {
        // Check if teacher owns this class
        if (!$class->teachers->contains(auth()->id())) {
            abort(403);
        }

        $class->load('students');
        $availableStudents = User::where('role', 'student')
            ->whereDoesntHave('schoolClasses', function($query) use ($class) {
                $query->where('class_id', $class->id);
            })
            ->orderBy('name')
            ->get();

        return view('teacher.classes.edit', compact('class', 'availableStudents'));
    }

    public function update(Request $request, SchoolClass $class)
    {
        // Check if teacher owns this class
        if (!$class->teachers->contains(auth()->id())) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:classes,code,' . $class->id,
            'description' => 'nullable|string',
            'subject' => 'required|string|max:100',
            'start_year' => 'required|integer|min:2000|max:2100',
            'end_year' => 'required|integer|min:2000|max:2100|gte:start_year',
            'is_active' => 'nullable|boolean',
        ]);

        $class->update(array_merge(
            $request->only(['name', 'code', 'description', 'subject', 'start_year', 'end_year']),
            ['is_active' => $request->has('is_active')]
        ));

        return redirect()->route('teacher.classes.index')
            ->with('success', 'Lớp học đã được cập nhật!');
    }

    public function destroy(SchoolClass $class)
    {
        // Check if teacher owns this class
        if (!$class->teachers->contains(auth()->id())) {
            abort(403);
        }

        $class->delete();

        return redirect()->route('teacher.classes.index')
            ->with('success', 'Lớp học đã được xóa!');
    }

    public function manageStudents(SchoolClass $class)
    {
        // Check if teacher owns this class
        if (!$class->teachers->contains(auth()->id())) {
            abort(403);
        }

        $class->load('students');
        $availableStudents = User::where('role', 'student')
            ->whereDoesntHave('schoolClasses', function($query) use ($class) {
                $query->where('class_id', $class->id);
            })
            ->orderBy('name')
            ->get();

        return view('teacher.classes.students', compact('class', 'availableStudents'));
    }

    public function addStudent(Request $request, SchoolClass $class)
    {
        // Check if teacher owns this class
        if (!$class->teachers->contains(auth()->id())) {
            abort(403);
        }

        $request->validate([
            'student_id' => 'required|exists:users,id',
        ]);

        $class->students()->attach($request->student_id);

        return redirect()->route('teacher.classes.students', $class)
            ->with('success', 'Học sinh đã được thêm vào lớp!');
    }

    public function addMultipleStudents(Request $request, SchoolClass $class)
    {
        // Check if teacher owns this class
        if (!$class->teachers->contains(auth()->id())) {
            abort(403);
        }

        $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:users,id',
        ]);

        $class->students()->syncWithoutDetaching($request->student_ids);

        $count = count($request->student_ids);
        return redirect()->route('teacher.classes.students', $class)
            ->with('success', "Đã thêm {$count} học sinh vào lớp!");
    }

    public function removeStudent(SchoolClass $class, User $student)
    {
        // Check if teacher owns this class
        if (!$class->teachers->contains(auth()->id())) {
            abort(403);
        }

        $class->students()->detach($student->id);

        return redirect()->route('teacher.classes.students', $class)
            ->with('success', 'Học sinh đã được xóa khỏi lớp!');
    }

    public function removeMultipleStudents(Request $request, SchoolClass $class)
    {
        // Check if teacher owns this class
        if (!$class->teachers->contains(auth()->id())) {
            abort(403);
        }

        $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:users,id',
        ]);

        $class->students()->detach($request->student_ids);

        $count = count($request->student_ids);
        return redirect()->back()
            ->with('success', "Đã xóa {$count} học sinh khỏi lớp!");
    }
}
