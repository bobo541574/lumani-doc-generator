<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookDelivery extends Model
{
    use HasUuids;

    protected $table = 'webhook_deliveries';

    protected $fillable = [
        'webhook_id',
        'event',
        'payload',
        'response_status',
        'response_headers',
        'response_body',
        'error_message',
        'error_stack',
        'delivered_at',
        'retry_count',
        'success',
    ];

    protected $casts = [
        'payload' => 'array',
        'response_headers' => 'array',
        'delivered_at' => 'datetime',
        'retry_count' => 'integer',
        'success' => 'boolean',
    ];

    public function webhook(): BelongsTo
    {
        return $this->belongsTo(Webhook::class);
    }
}
