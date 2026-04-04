<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\SchoolClass;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ExamController extends Controller
{
	private function scoreColumn(): string
	{
		return Schema::hasColumn('exams', 'pass_score') ? 'pass_score' : 'min_score';
	}

	public function index(Request $request): JsonResponse
	{
		$teacher = $request->user();
		$classIds = $teacher->teachingClasses()->pluck('classes.id');
		$scoreColumn = $this->scoreColumn();

		$classes = $teacher->teachingClasses()->orderBy('name')->get(['classes.id', 'name']);

		$exams = Exam::query()
			->whereIn('class_id', $classIds)
			->with('schoolClass:id,name')
			->withCount(['questions', 'assignedUsers'])
			->orderByDesc('created_at')
			->get()
			->map(fn (Exam $exam) => [
				'id' => $exam->id,
				'title' => $exam->title,
				'description' => $exam->description,
				'duration' => $exam->duration,
				'pass_score' => (float) $exam->{$scoreColumn},
				'is_active' => (bool) $exam->is_active,
				'start_time' => optional($exam->start_time)->toDateTimeString(),
				'end_time' => optional($exam->end_time)->toDateTimeString(),
				'class_id' => $exam->class_id,
				'class_name' => $exam->schoolClass?->name,
				'questions_count' => $exam->questions_count,
				'assigned_users_count' => $exam->assigned_users_count,
			]);

		return response()->json([
			'data' => [
				'exams' => $exams,
				'classes' => $classes,
			],
		]);
	}

	public function store(Request $request): JsonResponse
	{
		$teacher = $request->user();

		$validated = $request->validate([
			'title' => ['required', 'string', 'max:255'],
			'description' => ['nullable', 'string'],
			'duration' => ['required', 'integer', 'min:1'],
			'pass_score' => ['required', 'numeric', 'min:0'],
			'is_active' => ['nullable', 'boolean'],
			'start_time' => ['nullable', 'date'],
			'end_time' => ['nullable', 'date', 'after:start_time'],
			'class_id' => ['required', 'exists:classes,id'],
		]);

		if (! $teacher->teachingClasses()->where('classes.id', $validated['class_id'])->exists()) {
			abort(403);
		}

		$scoreColumn = $this->scoreColumn();

		$exam = Exam::create([
			'title' => $validated['title'],
			'description' => $validated['description'] ?? null,
			'duration' => $validated['duration'],
			$scoreColumn => $validated['pass_score'],
			'is_active' => (bool) ($validated['is_active'] ?? true),
			'start_time' => $validated['start_time'] ?? null,
			'end_time' => $validated['end_time'] ?? null,
			'class_id' => $validated['class_id'],
		]);

		$studentIds = SchoolClass::findOrFail($validated['class_id'])->students()->pluck('users.id')->all();
		if (! empty($studentIds)) {
			$exam->assignedUsers()->syncWithoutDetaching($studentIds);
		}

		return response()->json(['message' => 'Tạo bài thi thành công.']);
	}

	public function update(Request $request, Exam $exam): JsonResponse
	{
		if (! $request->user()->teachingClasses()->where('classes.id', $exam->class_id)->exists()) {
			abort(403);
		}

		$validated = $request->validate([
			'title' => ['required', 'string', 'max:255'],
			'description' => ['nullable', 'string'],
			'duration' => ['required', 'integer', 'min:1'],
			'pass_score' => ['required', 'numeric', 'min:0'],
			'is_active' => ['nullable', 'boolean'],
			'start_time' => ['nullable', 'date'],
			'end_time' => ['nullable', 'date', 'after:start_time'],
			'class_id' => ['required', 'exists:classes,id'],
		]);

		if (! $request->user()->teachingClasses()->where('classes.id', $validated['class_id'])->exists()) {
			abort(403);
		}

		$scoreColumn = $this->scoreColumn();

		$exam->update([
			'title' => $validated['title'],
			'description' => $validated['description'] ?? null,
			'duration' => $validated['duration'],
			$scoreColumn => $validated['pass_score'],
			'is_active' => (bool) ($validated['is_active'] ?? false),
			'start_time' => $validated['start_time'] ?? null,
			'end_time' => $validated['end_time'] ?? null,
			'class_id' => $validated['class_id'],
		]);

		return response()->json(['message' => 'Cập nhật bài thi thành công.']);
	}

	public function destroy(Request $request, Exam $exam): JsonResponse
	{
		if (! $request->user()->teachingClasses()->where('classes.id', $exam->class_id)->exists()) {
			abort(403);
		}

		$exam->delete();

		return response()->json(['message' => 'Xóa bài thi thành công.']);
	}

	public function assignData(Request $request, Exam $exam): JsonResponse
	{
		if (! $request->user()->teachingClasses()->where('classes.id', $exam->class_id)->exists()) {
			abort(403);
		}

		$users = $request->user()->students()->orderBy('name')->get(['users.id', 'name', 'username']);
		$assignedIds = $exam->assignedUsers()->pluck('users.id');

		return response()->json([
			'data' => [
				'exam' => ['id' => $exam->id, 'title' => $exam->title],
				'users' => $users,
				'assigned_ids' => $assignedIds,
			],
		]);
	}

	public function assignUsers(Request $request, Exam $exam): JsonResponse
	{
		if (! $request->user()->teachingClasses()->where('classes.id', $exam->class_id)->exists()) {
			abort(403);
		}

		$validated = $request->validate([
			'user_ids' => ['nullable', 'array'],
			'user_ids.*' => ['integer', 'exists:users,id'],
		]);

		$teacherStudentIds = $request->user()->students()->pluck('users.id')->all();
		$userIds = array_values(array_intersect($validated['user_ids'] ?? [], $teacherStudentIds));

		$exam->assignedUsers()->wherePivotIn('user_id', $teacherStudentIds)->detach();
		if (! empty($userIds)) {
			$syncData = [];
			foreach ($userIds as $id) {
				$syncData[$id] = ['assigned_at' => now()];
			}
			$exam->assignedUsers()->attach($syncData);
		}

		return response()->json(['message' => 'Cập nhật phân công học sinh thành công.']);
	}
}

