<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Webhook;
use App\Models\WebhookDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\Response;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Throwable;

class SendWebhookJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Webhook $webhook,
        public string $event,
        public array $payload
    ) {
    }

    public function handle(): void
    {
        $headers = array_merge($this->webhook->headers ?? [], [
            'Content-Type' => 'application/json',
            'X-Webhook-Event' => $this->event,
        ]);

        if ($this->webhook->secret) {
            $signature = hash_hmac('sha256', json_encode($this->payload), $this->webhook->secret);
            $headers['X-Webhook-Signature'] = "sha256={$signature}";
        }

        try {
            $response = Http::timeout($this->webhook->timeout_ms)
                ->withHeaders($headers)
                ->post($this->webhook->url, $this->payload);

            $this->recordDelivery($response, null);
        } catch (Throwable $e) {
            $this->recordDelivery(null, $e);
        }
    }

    private function recordDelivery(?Response $response, ?Throwable $error): void
    {
        $success = $response !== null && $response->successful();

        WebhookDelivery::create([
            'webhook_id' => $this->webhook->id,
            'event' => $this->event,
            'payload' => $this->payload,
            'response_status' => $response?->status(),
            'response_headers' => $response?->headers(),
            'response_body' => $response?->body(),
            'error_message' => $error?->getMessage(),
            'error_stack' => $error?->getTraceAsString(),
            'success' => $success,
        ]);

        $this->webhook->update([
            'last_triggered_at' => now(),
            'last_triggered_status' => $success ? 'success' : 'failed',
            'last_error_message' => $error?->getMessage(),
        ]);

        if (!$success) {
            $this->release(max(1, $this->attempts()) * 30);
        }
    }
}
