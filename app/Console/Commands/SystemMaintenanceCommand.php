<?php

namespace App\Console\Commands;

use App\Services\IMaintenanceModeService;
use Illuminate\Console\Command;

class SystemMaintenanceCommand extends Command
{
    protected $signature = 'system:maintenance {action : on|off|status} {--message= : Thông báo bảo trì}';

    protected $description = 'Bật/tắt/xem trạng thái chế độ bảo trì hệ thống';

    public function __construct(private IMaintenanceModeService $maintenanceModeService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $action = strtolower((string) $this->argument('action'));

        if (! in_array($action, ['on', 'off', 'status'], true)) {
            $this->error('Action không hợp lệ. Dùng: on | off | status');
            return self::FAILURE;
        }

        if ($action === 'status') {
            $status = $this->maintenanceModeService->getStatus();
            $this->line('Maintenance: '.($status['enabled'] ? 'ON' : 'OFF'));
            $this->line('Message: '.$status['message']);
            $this->line('Updated at: '.($status['updated_at'] ?? '-'));
            $this->line('Updated by: '.($status['updated_by'] ?? '-'));
            return self::SUCCESS;
        }

        $enabled = $action === 'on';
        $status = $this->maintenanceModeService->updateStatus($enabled, $this->option('message'));

        $this->info($enabled ? 'Đã bật maintenance mode.' : 'Đã tắt maintenance mode.');
        $this->line('Message: '.$status['message']);

        return self::SUCCESS;
    }
}

