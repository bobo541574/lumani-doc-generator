<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attachment extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'attachments';

    protected $fillable = [
        'document_id',
        'file_name',
        'file_key',
        'file_size',
        'mime_type',
        'file_url',
        'thumbnail_url',
        'thumbnail_key',
        'type',
        'alt_text',
        'metadata',
        'extracted_text',
        'processing_status',
        'processing_error',
        'processing_started_at',
        'processed_at',
        'uploaded_by',
    ];

    protected $casts = [
        'metadata' => 'array',
        'file_size' => 'integer',
        'processing_started_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function scopePending($query)
    {
        return $query->where('processing_status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('processing_status', 'failed');
    }

    public function scopeProcessed($query)
    {
        return $query->where('processing_status', 'completed');
    }
}
