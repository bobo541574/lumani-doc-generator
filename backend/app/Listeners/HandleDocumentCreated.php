<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\DocumentCreated;
use App\Services\SearchService;
use App\Services\WebhookService;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleDocumentCreated implements ShouldQueue
{
    public function __construct(
        private SearchService $search,
        private WebhookService $webhooks,
    ) {
    }

    public function handle(DocumentCreated $event): void
    {
        $document = $event->document;

        $this->search->indexDocument(
            app('meilisearch')->index('documents'),
            $document
        );

        $this->webhooks->dispatch('document.created', [
            'id' => $document->id,
            'title' => $document->title,
            'slug' => $document->slug,
        ]);
    }
}
