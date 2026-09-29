<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\ExternalSource;
use App\Models\IncomingDataQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SyncExternalDataJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public array $backoff = [30, 120, 600];

    public $timeout = 120;

    public function __construct(public ExternalSource $source)
    {
    }

    public function handle(): void
    {
        $source = $this->source;

        $source->update([
            'last_sync_at' => now(),
            'last_sync_status' => 'running',
            'last_sync_error' => null,
        ]);

        try {
            $records = $this->fetchRecords($source);

            $enqueued = 0;

            foreach ($records as $record) {
                $payload = $this->transformRecord($record, $source);

                $syncId = sprintf(
                    '%s:%s',
                    $source->id,
                    $payload['slug'] ?? Str::slug((string) Str::uuid())
                );

                IncomingDataQueue::create([
                    'project_id' => $source->project_id,
                    'source_id' => $source->id,
                    'sync_id' => $syncId,
                    'payload' => $payload,
                    'action' => 'create',
                ]);

                $enqueued++;
            }

            $source->update([
                'last_sync_status' => 'completed',
                'sync_count' => $source->sync_count + $enqueued,
            ]);
        } catch (\Throwable $e) {
            $source->update([
                'last_sync_status' => 'failed',
                'last_sync_error' => $e->getMessage(),
            ]);

            report($e);

            throw $e;
        }
    }

    private function fetchRecords(ExternalSource $source): array
    {
        $config = $source->source_config ?? [];

        return match ($source->source_type) {
            'http' => $this->fetchHttp($config),
            default => throw new \RuntimeException(
                "Unsupported source_type: {$source->source_type}"
            ),
        };
    }

    private function fetchHttp(array $config): array
    {
        $endpoint = $config['endpoint'] ?? null;

        if (!$endpoint) {
            throw new \RuntimeException('HTTP source requires an "endpoint" in source_config');
        }

        $request = Http::timeout((int) ($config['timeout'] ?? 30))
            ->withHeaders($config['headers'] ?? []);

        if (!empty($config['auth_token'])) {
            $request->withToken($config['auth_token']);
        }

        $response = $request->get($endpoint);

        if ($response->failed()) {
            throw new \RuntimeException(
                "HTTP fetch failed with status {$response->status()}: {$endpoint}"
            );
        }

        $data = $response->json();

        if (isset($data['data']) && is_array($data['data'])) {
            $data = $data['data'];
        }

        if (!is_array($data)) {
            throw new \RuntimeException('HTTP source did not return a JSON array');
        }

        return $data;
    }

    private function transformRecord(array $record, ExternalSource $source): array
    {
        $mapping = $source->mapping_config ?? [];
        $fieldMappings = $mapping['field_mappings'] ?? [];
        $defaults = $mapping['defaults'] ?? [];

        $mapped = [];

        foreach ($fieldMappings as $target => $sourcePath) {
            $mapped[$target] = data_get($record, $sourcePath);
        }

        if (empty($mapped['title'])) {
            $mapped['title'] = $record['title'] ?? Str::title(
                Str::slug($record['slug'] ?? Str::uuid(), ' ')
            );
        }

        if (empty($mapped['slug'])) {
            $mapped['slug'] = Str::slug($record['slug'] ?? $mapped['title']);
        }

        foreach ($defaults as $key => $value) {
            if (empty($mapped[$key])) {
                $mapped[$key] = $value;
            }
        }

        return array_merge([
            'content' => $record['content'] ?? null,
            'content_type' => $record['content_type'] ?? 'mdx',
            'status' => $record['status'] ?? 'draft',
            'visibility' => $record['visibility'] ?? ($source->project->visibility ?? 'private'),
            'metadata' => [
                'source_id' => $source->id,
                'source_name' => $source->source_name,
                'raw' => $record,
            ],
        ], $mapped);
    }
}