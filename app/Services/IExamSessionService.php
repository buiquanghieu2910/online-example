<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\UserExam;

interface IExamSessionService
{
    public function getTimeRemaining(UserExam $userExam, Exam $exam): int;

    public function syncTimer(UserExam $userExam, Exam $exam, int $clientRemaining): int;

    public function autosaveAnswers(UserExam $userExam, array $answers): int;

    public function forgetTimer(UserExam $userExam): void;

    public function logExamEvent(
        UserExam $userExam,
        string $event,
        array $meta = [],
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): void;
}
