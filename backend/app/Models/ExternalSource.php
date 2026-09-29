<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExternalSource extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'external_sources';

    protected $fillable = [
        'project_id',
        'source_type',
        'source_name',
        'source_config',
        'mapping_config',
        'sync_schedule',
        'last_sync_at',
        'last_sync_status',
        'last_sync_error',
        'sync_count',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'source_config' => 'array',
        'mapping_config' => 'array',
        'last_sync_at' => 'datetime',
        'sync_count' => 'integer',
        'is_active' => 'boolean',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function queues(): HasMany
    {
        return $this->hasMany(IncomingDataQueue::class, 'source_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
