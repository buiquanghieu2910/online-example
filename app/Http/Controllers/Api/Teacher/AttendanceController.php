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
        $allowedClassIds = $classes->pluck('id')->all();

        if ($classId && ! in_array((int) $classId, $allowedClassIds, true)) {
            abort(403);
        }

        $studentsQuery = User::query()->where('role', 'student');

        if ($classId) {
            $studentsQuery->whereHas('schoolClasses', fn ($query) => $query->where('class_id', $classId));
        } else {
            $studentsQuery->whereHas('schoolClasses', fn ($query) => $query->whereIn('class_id', $allowedClassIds));
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
        $teacher = $request->user();

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'class_id' => ['nullable', 'exists:classes,id'],
            'attendances' => ['required', 'array'],
            'attendances.*.student_id' => ['required', 'exists:users,id'],
            'attendances.*.status' => ['required', 'in:present,absent,late,excused'],
            'attendances.*.notes' => ['nullable', 'string'],
        ]);

        $allowedClassIds = $teacher->teachingClasses()->pluck('classes.id')->all();
        if (! empty($validated['class_id']) && ! in_array((int) $validated['class_id'], $allowedClassIds, true)) {
            abort(403);
        }

        $allowedStudentIdsQuery = $teacher->students()->pluck('users.id');
        if (! empty($validated['class_id'])) {
            $allowedStudentIdsQuery = $teacher->students()
                ->whereHas('schoolClasses', fn ($query) => $query->where('class_id', $validated['class_id']))
                ->pluck('users.id');
        }
        $allowedStudentIds = $allowedStudentIdsQuery->map(fn ($id) => (int) $id)->all();

        $invalidStudentIds = collect($validated['attendances'])
            ->pluck('student_id')
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => ! in_array($id, $allowedStudentIds, true))
            ->unique()
            ->values()
            ->all();

        if (! empty($invalidStudentIds)) {
            return response()->json([
                'message' => 'Một số học sinh không thuộc phạm vi phụ trách.',
                'errors' => [
                    'attendances' => ['Một số học sinh không thuộc phạm vi phụ trách.'],
                ],
            ], 422);
        }

        foreach ($validated['attendances'] as $attendance) {

            Attendance::updateOrCreate(
                [
                    'student_id' => $attendance['student_id'],
                    'date' => $validated['date'],
                ],
                [
                    'teacher_id' => $teacher->id,
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
        $allowedClassIds = $classes->pluck('id')->all();

        if ($classId && ! in_array((int) $classId, $allowedClassIds, true)) {
            abort(403);
        }

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
            $student = $teacher->students()->where('users.id', $studentId)->firstOrFail();
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
