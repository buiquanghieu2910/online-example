<?php

namespace App\Services;

interface IExamMonitoringService
{
    public function getActiveAttemptsForAdmin(array $filters = []): array;

    public function getActiveAttemptsForTeacher(int $teacherId, array $filters = []): array;

    public function getTimelineForAdmin(int $userExamId): ?array;

    public function getTimelineForTeacher(int $teacherId, int $userExamId): ?array;
}
