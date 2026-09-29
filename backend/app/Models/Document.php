<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'documents';

    protected $fillable = [
        'project_id',
        'parent_id',
        'title',
        'slug',
        'content',
        'content_type',
        'version',
        'status',
        'visibility',
        'is_published',
        'published_at',
        'order_index',
        'metadata',
        'ai_generated',
        'ai_metadata',
        'view_count',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'metadata' => 'array',
        'ai_metadata' => 'array',
        'is_published' => 'boolean',
        'ai_generated' => 'boolean',
        'published_at' => 'datetime',
        'view_count' => 'integer',
        'order_index' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)->where('status', 'published');
    }
}
