<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\AttachmentProcessed;
use App\Services\WebhookService;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleAttachmentProcessed implements ShouldQueue
{
    public function __construct(private WebhookService $webhooks)
    {
    }

    public function handle(AttachmentProcessed $event): void
    {
        $this->webhooks->dispatch(
            $event->success ? 'attachment.processed' : 'attachment.failed',
            [
                'id' => $event->attachment->id,
                'document_id' => $event->attachment->document_id,
                'file_name' => $event->attachment->file_name,
                'processing_status' => $event->attachment->processing_status,
            ]
        );
    }
}
