<?php

namespace App\Services;

interface IMaintenanceModeService
{
    public function isEnabled(): bool;

    public function getMessage(): string;

    public function getStatus(): array;

    public function updateStatus(bool $enabled, ?string $message = null, ?int $updatedBy = null): array;
}

