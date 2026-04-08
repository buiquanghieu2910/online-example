<?php

namespace App\Services\Impl;

use App\Models\Exam;
use App\Repositories\IExamRepository;
use App\Services\IExamService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ExamServiceImpl implements IExamService
{
    public function __construct(
        private IExamRepository $examRepository
    ) {}

    public function getAllExams(): Collection
    {
        return $this->examRepository->all();
    }

    public function getPaginatedExams(int $perPage = 15): LengthAwarePaginator
    {
        return $this->examRepository->paginate($perPage);
    }

    public function getExamById(int $id): ?Exam
    {
        return $this->examRepository->findById($id);
    }

    public function createExam(array $data): Exam
    {
        return $this->examRepository->create($data);
    }

    public function updateExam(int $id, array $data): bool
    {
        return $this->examRepository->update($id, $data);
    }

    public function deleteExam(int $id): bool
    {
        return $this->examRepository->delete($id);
    }

    public function getActiveExams(): Collection
    {
        return $this->examRepository->getActiveExams();
    }

    public function getExamWithQuestions(int $id): ?Exam
    {
        return $this->examRepository->getExamWithQuestions($id);
    }

    public function getExamWithAssignedUsers(int $id): ?Exam
    {
        return Exam::with('assignedUsers')->find($id);
    }

    public function assignUsersToExam(int $examId, array $userIds): void
    {
        $exam = $this->examRepository->findById($examId);
        if ($exam) {
            $exam->assignedUsers()->syncWithoutDetaching($userIds);
        }
    }

    public function unassignUserFromExam(int $examId, int $userId): void
    {
        $exam = $this->examRepository->findById($examId);
        if ($exam) {
            $exam->assignedUsers()->detach($userId);
        }
    }

    public function getAssignedExamsForUser(int $userId): Collection
    {
        return Exam::whereHas('assignedUsers', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->where('is_active', true)
        ->withCount('questions')
        ->get();
    }
}
