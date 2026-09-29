<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\SyncExternalDataJob;
use App\Models\ExternalSource;
use Illuminate\Console\Command;

class SyncExternalData extends Command
{
    protected $signature = 'sync:external-data {--source= : Filter by source ID}';

    protected $description = 'Synchronize external data sources into the documentation engine';

    public function handle(): int
    {
        $query = ExternalSource::query()->where('is_active', true);

        if ($sourceId = $this->option('source')) {
            $query->where('id', $sourceId);
        }

        $sources = $query->get();

        foreach ($sources as $source) {
            SyncExternalDataJob::dispatch($source);
            $this->line("Dispatched sync for source: {$source->source_name}");
        }

        $this->info("Dispatched {$sources->count()} sync job(s).");

        return self::SUCCESS;
    }
}
