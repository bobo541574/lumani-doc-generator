<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\SendWebhookJob;
use App\Models\Webhook;

class WebhookService
{
    public function dispatch(string $event, array $payload): void
    {
        $webhooks = Webhook::where('is_active', true)
            ->whereJsonContains('events', $event)
            ->get();

        foreach ($webhooks as $webhook) {
            SendWebhookJob::dispatch($webhook, $event, $payload);
        }
    }
}
