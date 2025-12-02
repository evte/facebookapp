<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class TenantAccessStats extends Command
{
    protected $signature = 'tenant:access-stats {--days=1 : Number of days to analyze}';
    protected $description = 'Show tenant access statistics';

    public function handle()
    {
        $days = $this->option('days');
        $logFile = storage_path('logs/laravel.log');

        if (!File::exists($logFile)) {
            $this->error('Log file not found!');
            return 1;
        }

        $this->info("Analyzing tenant access logs for the last {$days} day(s)...");
        $this->newLine();

        // 读取日志并统计
        $logs = File::get($logFile);
        $lines = explode("\n", $logs);

        $stats = [];
        $totalAccess = 0;

        foreach ($lines as $line) {
            if (strpos($line, 'Tenant initialized') !== false) {
                $totalAccess++;

                // 提取租户ID
                if (preg_match('/"tenant_id":"([^"]+)"/', $line, $matches)) {
                    $tenantId = $matches[1];
                    $stats[$tenantId] = ($stats[$tenantId] ?? 0) + 1;
                }
            }
        }

        $this->table(
            ['Tenant ID', 'Access Count', 'Percentage'],
            collect($stats)->map(function ($count, $tenantId) use ($totalAccess) {
                return [
                    substr($tenantId, 0, 20) . '...',
                    $count,
                    round(($count / $totalAccess) * 100, 2) . '%'
                ];
            })->toArray()
        );

        $this->info("Total accesses: {$totalAccess}");

        return 0;
    }
}
