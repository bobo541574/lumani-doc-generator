<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncomingDataQueue extends Model
{
    use HasUuids;

    protected $table = 'incoming_data_queue';

    protected $fillable = [
        'project_id',
        'source_id',
        'sync_id',
        'payload',
        'target_document_id',
        'action',
        'status',
        'progress',
        'error_message',
        'error_stack',
        'processed_at',
        'retry_count',
        'max_retries',
        'next_retry_at',
        'metadata',
    ];

    protected $casts = [
        'payload' => 'array',
        'metadata' => 'array',
        'progress' => 'integer',
        'retry_count' => 'integer',
        'max_retries' => 'integer',
        'processed_at' => 'datetime',
        'next_retry_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(ExternalSource::class, 'source_id');
    }

    public function targetDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'target_document_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
