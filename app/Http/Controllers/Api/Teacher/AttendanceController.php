<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $teacher = $request->user();
        $date = $request->get('date', now()->toDateString());
        $classId = $request->get('class_id');

        $classes = $teacher->teachingClasses()->orderBy('name')->get(['classes.id', 'name']);

        $studentsQuery = User::query()->where('role', 'student');

        if ($classId) {
            $studentsQuery->whereHas('schoolClasses', fn ($query) => $query->where('class_id', $classId));
        } else {
            $classIds = $classes->pluck('id');
            $studentsQuery->whereHas('schoolClasses', fn ($query) => $query->whereIn('class_id', $classIds));
        }

        $students = $studentsQuery
            ->with(['attendances' => fn ($query) => $query->where('date', $date)])
            ->orderBy('name')
            ->get(['id', 'name', 'username']);

        return response()->json([
            'data' => [
                'date' => $date,
                'class_id' => $classId,
                'classes' => $classes,
                'students' => $students,
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'class_id' => ['nullable', 'exists:classes,id'],
            'attendances' => ['required', 'array'],
            'attendances.*.student_id' => ['required', 'exists:users,id'],
            'attendances.*.status' => ['required', 'in:present,absent,late,excused'],
            'attendances.*.notes' => ['nullable', 'string'],
        ]);

        foreach ($validated['attendances'] as $attendance) {
            Attendance::updateOrCreate(
                [
                    'student_id' => $attendance['student_id'],
                    'date' => $validated['date'],
                ],
                [
                    'teacher_id' => $request->user()->id,
                    'class_id' => $validated['class_id'] ?? null,
                    'status' => $attendance['status'],
                    'notes' => $attendance['notes'] ?? null,
                ]
            );
        }

        return response()->json(['message' => 'Lưu điểm danh thành công.']);
    }

    public function statistics(Request $request): JsonResponse
    {
        $teacher = $request->user();
        $studentId = $request->get('student_id');
        $month = $request->get('month', now()->format('Y-m'));
        $classId = $request->get('class_id');

        $classes = $teacher->teachingClasses()->orderBy('name')->get(['classes.id', 'name']);

        $studentsQuery = User::query()->where('role', 'student');
        if ($classId) {
            $studentsQuery->whereHas('schoolClasses', fn ($query) => $query->where('class_id', $classId));
        } else {
            $classIds = $classes->pluck('id');
            $studentsQuery->whereHas('schoolClasses', fn ($query) => $query->whereIn('class_id', $classIds));
        }
        $students = $studentsQuery->orderBy('name')->get(['id', 'name']);

        $data = [
            'month' => $month,
            'class_id' => $classId,
            'classes' => $classes,
            'students' => $students,
            'attendances' => [],
            'stats' => null,
            'student' => null,
        ];

        if ($studentId) {
            $student = User::findOrFail($studentId);
            $attendances = Attendance::query()
                ->where('student_id', $studentId)
                ->whereYear('date', substr($month, 0, 4))
                ->whereMonth('date', substr($month, 5, 2))
                ->orderBy('date')
                ->get();

            $data['student'] = $student->only(['id', 'name', 'username']);
            $data['attendances'] = $attendances;
            $data['stats'] = [
                'present' => $attendances->where('status', 'present')->count(),
                'absent' => $attendances->where('status', 'absent')->count(),
                'late' => $attendances->where('status', 'late')->count(),
                'excused' => $attendances->where('status', 'excused')->count(),
                'total' => $attendances->count(),
            ];
        }

        return response()->json(['data' => $data]);
    }
}

