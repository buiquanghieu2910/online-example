<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    // Hiển thị danh sách điểm danh
    public function index(Request $request)
    {
        $teacher = auth()->user();
        $date = $request->get('date', today()->toDateString());
        $classId = $request->get('class_id');
        
        // Get teacher's classes
        $classes = $teacher->teachingClasses()->orderBy('name')->get();
        
        // Get students based on class filter
        $studentsQuery = User::where('role', 'student');
        
        if ($classId) {
            $studentsQuery->whereHas('schoolClasses', function($query) use ($classId) {
                $query->where('class_id', $classId);
            });
        } else {
            // Get all students from teacher's classes
            $classIds = $classes->pluck('id');
            $studentsQuery->whereHas('schoolClasses', function($query) use ($classIds) {
                $query->whereIn('class_id', $classIds);
            });
        }
        
        $students = $studentsQuery->with(['attendances' => function($query) use ($date) {
                $query->where('date', $date);
            }])
            ->orderBy('name')
            ->get();
        
        return view('teacher.attendances.index', compact('students', 'date', 'classes', 'classId'));
    }

    // Lưu điểm danh
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'class_id' => 'nullable|exists:classes,id',
            'attendances' => 'required|array',
            'attendances.*.student_id' => 'required|exists:users,id',
            'attendances.*.status' => 'required|in:present,absent,late,excused',
            'attendances.*.notes' => 'nullable|string',
        ]);

        $teacher = auth()->user();
        $date = $request->date;
        $classId = $request->class_id;

        foreach ($request->attendances as $attendance) {
            Attendance::updateOrCreate(
                [
                    'student_id' => $attendance['student_id'],
                    'date' => $date,
                ],
                [
                    'teacher_id' => $teacher->id,
                    'class_id' => $classId,
                    'status' => $attendance['status'],
                    'notes' => $attendance['notes'] ?? null,
                ]
            );
        }

        return redirect()->route('teacher.attendances.index', ['date' => $date, 'class_id' => $classId])
            ->with('success', 'Điểm danh đã được lưu thành công!');
    }

    // Thống kê theo tháng cho từng học sinh
    public function statistics(Request $request)
    {
        $teacher = auth()->user();
        $studentId = $request->get('student_id');
        $month = $request->get('month', now()->format('Y-m'));
        $classId = $request->get('class_id');

        // Get teacher's classes
        $classes = $teacher->teachingClasses()->orderBy('name')->get();
        
        // Get students based on class filter
        $studentsQuery = User::where('role', 'student');
        if ($classId) {
            $studentsQuery->whereHas('schoolClasses', function($query) use ($classId) {
                $query->where('class_id', $classId);
            });
        } else {
            $classIds = $classes->pluck('id');
            $studentsQuery->whereHas('schoolClasses', function($query) use ($classIds) {
                $query->whereIn('class_id', $classIds);
            });
        }
        $students = $studentsQuery->orderBy('name')->get();

        if ($studentId) {
            $student = User::findOrFail($studentId);
            
            // Lấy thống kê điểm danh theo tháng
            $attendances = Attendance::where('student_id', $studentId)
                ->whereYear('date', substr($month, 0, 4))
                ->whereMonth('date', substr($month, 5, 2))
                ->orderBy('date')
                ->get();

            $stats = [
                'present' => $attendances->where('status', 'present')->count(),
                'absent' => $attendances->where('status', 'absent')->count(),
                'late' => $attendances->where('status', 'late')->count(),
                'excused' => $attendances->where('status', 'excused')->count(),
                'total' => $attendances->count(),
            ];

            return view('teacher.attendances.statistics', compact('student', 'students', 'classes', 'attendances', 'stats', 'month', 'classId'));
        }

        return view('teacher.attendances.statistics', compact('students', 'classes', 'month', 'classId'));
    }
}
