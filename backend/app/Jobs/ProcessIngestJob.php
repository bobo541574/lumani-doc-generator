<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\IncomingDataQueue;
use App\Models\Document;
use App\Services\DocumentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Throwable;

class ProcessIngestJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public array $backoff = [10, 60, 300];

    public function __construct(public IncomingDataQueue $queue)
    {
    }

    public function handle(DocumentService $documentService): void
    {
        $lock = Redis::set(
            "ingest:process:{$this->queue->id}",
            true,
            'EX',
            60,
            'NX'
        );

        if (!$lock) {
            $this->release(10);
            return;
        }

        try {
            DB::transaction(function () use ($documentService) {
                $this->queue->update(['status' => 'processing']);

                $this->queue->update(['progress' => 25]);

                match ($this->queue->action) {
                    'create' => $documentService->createFromPayload($this->queue),
                    'update' => $documentService->updateFromPayload($this->queue),
                    'delete' => $documentService->deleteFromPayload($this->queue),
                    default => throw new \RuntimeException("Unsupported action: {$this->queue->action}"),
                };

                $this->queue->update([
                    'status' => 'completed',
                    'progress' => 100,
                    'processed_at' => now(),
                ]);
            });
        } catch (Throwable $e) {
            $this->queue->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'error_stack' => $e->getTraceAsString(),
                'retry_count' => $this->queue->retry_count + 1,
            ]);
            throw $e;
        } finally {
            Redis::del("ingest:process:{$this->queue->id}");
        }
    }

    public function failed(Throwable $e): void
    {
        $this->queue->update([
            'status' => 'failed',
            'error_message' => $e->getMessage(),
            'error_stack' => $e->getTraceAsString(),
            'retry_count' => $this->queue->retry_count + 1,
        ]);
    }

    public function retryUntil(): \DateTime
    {
        return now()->addHours(4);
    }
}
