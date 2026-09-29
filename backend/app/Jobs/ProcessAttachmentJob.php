<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Attachment;
use App\Services\AttachmentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProcessAttachmentJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 300;

    public int $tries = 3;

    public array $backoff = [15, 60, 600];

    public function __construct(public Attachment $attachment)
    {
    }

    public function handle(AttachmentService $attachmentService): void
    {
        DB::transaction(function () use ($attachmentService) {
            $this->attachment->update([
                'processing_status' => 'processing',
                'processing_started_at' => now(),
            ]);

            $attachmentService->process($this->attachment);

            $this->attachment->update([
                'processing_status' => 'completed',
                'processed_at' => now(),
            ]);
        });
    }

    public function failed(Throwable $e): void
    {
        $this->attachment->update([
            'processing_status' => 'failed',
            'processing_error' => $e->getMessage(),
        ]);
    }

    public function retryUntil(): \DateTime
    {
        return now()->addHours(6);
    }
}
