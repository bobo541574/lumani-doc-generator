<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreatePartitions extends Command
{
    protected $signature = 'partitions:create
        {--months=3 : How many future months of partitions to provision beyond the current month}
        {--backfill=1 : How many past months to backfill from the current month}
        {--tables=incoming_data_queue,activity_logs : Comma-separated list of partitioned tables to manage}';

    protected $description = 'Provision monthly range partitions (and a default partition) for partitioned tables';

    public function handle(): int
    {
        $tables = array_values(array_filter(array_map(
            'trim',
            explode(',', (string) $this->option('tables')),
        )));

        if ($tables === []) {
            $this->components->error('No tables provided.');

            return self::FAILURE;
        }

        $futureMonths = max(0, (int) $this->option('months'));
        $backfillMonths = max(0, (int) $this->option('backfill'));

        $now = now()->startOfMonth();

        // Start from (backfill months ago) through (future months ahead).
        $cursor = $now->copy()->subMonths($backfillMonths);
        $end = $now->copy()->addMonths($futureMonths);

        foreach ($tables as $table) {
            $this->components->task("Managing partitions for {$table}", function () use ($table, $cursor, $end) {
                $this->ensureDefaultPartition($table);

                $month = $cursor->copy();
                while ($month->lte($end)) {
                    $this->createMonthPartition($table, $month);
                    $month->addMonth();
                }

                return true;
            });
        }

        $this->components->info("Partitions provisioned for: ".implode(', ', $tables));

        return self::SUCCESS;
    }

    /**
     * Create a catch-all default partition so out-of-range rows never fail with
     * "no partition of relation found for row".
     */
    private function ensureDefaultPartition(string $table): void
    {
        $partition = $table.'_default';

        if ($this->partitionExists($partition)) {
            return;
        }

        DB::statement("CREATE TABLE {$partition} PARTITION OF {$table} DEFAULT");
    }

    /**
     * Create a monthly partition named {table}_{YYYY_MM} if it does not already exist.
     */
    private function createMonthPartition(string $table, \Illuminate\Support\Carbon $month): void
    {
        $partition = $table.'_'.$month->format('Y_m');
        $from = $month->format('Y-m-01 00:00:00+00');
        $to = $month->copy()->addMonth()->format('Y-m-01 00:00:00+00');

        if ($this->partitionExists($partition)) {
            return;
        }

        DB::statement(
            "CREATE TABLE {$partition} PARTITION OF {$table} FOR VALUES FROM (TIMESTAMPTZ '{$from}') TO (TIMESTAMPTZ '{$to}')"
        );
    }

    /**
     * Determine whether a partition (table relation) already exists.
     */
    private function partitionExists(string $partition): bool
    {
        return DB::selectOne(
            "SELECT 1 FROM pg_class WHERE relname = ? AND relkind IN ('r', 'p')",
            [$partition]
        ) !== null;
    }
}
