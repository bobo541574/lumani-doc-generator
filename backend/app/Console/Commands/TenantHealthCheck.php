<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Organization;
use App\Models\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class TenantHealthCheck extends Command
{
    protected $signature = 'tenant:health';

    protected $description = 'Run health checks across all tenants and infrastructure';

    public function handle(): int
    {
        $this->components->info('Running infrastructure health checks...');

        $this->checkDatabase();
        $this->checkRedis();
        $this->checkTenantCounts();

        return self::SUCCESS;
    }

    private function checkDatabase(): void
    {
        try {
            DB::select('select 1');
            $this->components->success('Database connection: OK');
        } catch (\Throwable $e) {
            $this->components->error("Database connection failed: {$e->getMessage()}");
        }
    }

    private function checkRedis(): void
    {
        try {
            Redis::ping();
            $this->components->success('Redis connection: OK');
        } catch (\Throwable $e) {
            $this->components->error("Redis connection failed: {$e->getMessage()}");
        }
    }

    private function checkTenantCounts(): void
    {
        $organizations = Organization::count();
        $projects = Project::count();

        $this->components->info("Tenants: {$organizations}");
        $this->components->info("Projects: {$projects}");

        $failedIngest = DB::table('incoming_data_queue')
            ->where('status', 'failed')
            ->count();

        if ($failedIngest > 0) {
            $this->components->warn("Failed ingestion records: {$failedIngest}");
        }
    }
}
