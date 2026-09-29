<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiKey extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'api_keys';

    protected $fillable = [
        'project_id',
        'name',
        'key_hash',
        'key_preview',
        'permissions',
        'expires_at',
        'last_used_at',
        'usage_count',
        'is_active',
        'created_by',
    ];

    protected $hidden = [
        'key_hash',
    ];

    protected $casts = [
        'permissions' => 'array',
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
        'usage_count' => 'integer',
        'is_active' => 'boolean',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function generateKey(): string
    {
        return 'lum_' . bin2hex(random_bytes(32));
    }

    public static function hashKey(string $key): string
    {
        return hash('sha256', $key);
    }
}
