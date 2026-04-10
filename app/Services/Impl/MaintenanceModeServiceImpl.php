<?php

namespace App\Services\Impl;

use App\Models\MaintenanceSetting;
use App\Services\IMaintenanceModeService;
use Illuminate\Support\Facades\Log;

class MaintenanceModeServiceImpl implements IMaintenanceModeService
{
    public function isEnabled(): bool
    {
        return (bool) $this->getSetting()->enabled;
    }

    public function getMessage(): string
    {
        return (string) $this->getSetting()->message;
    }

    public function getStatus(): array
    {
        $setting = $this->getSetting();

        return [
            'enabled' => (bool) $setting->enabled,
            'message' => (string) $setting->message,
            'updated_at' => optional($setting->updated_at)?->toISOString(),
            'updated_by' => $setting->updated_by,
        ];
    }

    public function updateStatus(bool $enabled, ?string $message = null, ?int $updatedBy = null): array
    {
        $setting = $this->getSetting();
        $setting->enabled = $enabled;
        if ($message !== null) {
            $setting->message = trim($message) !== '' ? trim($message) : $setting->message;
        }
        $setting->updated_by = $updatedBy;
        $setting->save();

        return $this->getStatus();
    }

    private function getSetting(): MaintenanceSetting
    {
        try {
            return MaintenanceSetting::query()->firstOrCreate(
                ['id' => 1],
                [
                    'enabled' => (bool) config('maintenance.enabled', false),
                    'message' => (string) config('maintenance.message', 'Hệ thống đang bảo trì. Vui lòng quay lại sau.'),
                    'updated_by' => null,
                ]
            );
        } catch (\Throwable $exception) {
            Log::warning('Cannot load maintenance setting from database, fallback to config.', [
                'error' => $exception->getMessage(),
            ]);

            return new MaintenanceSetting([
                'id' => 1,
                'enabled' => (bool) config('maintenance.enabled', false),
                'message' => (string) config('maintenance.message', 'Hệ thống đang bảo trì. Vui lòng quay lại sau.'),
                'updated_by' => null,
            ]);
        }
    }
}
