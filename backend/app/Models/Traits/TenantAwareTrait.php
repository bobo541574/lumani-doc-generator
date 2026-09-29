<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait TenantAwareTrait
{
    public static function bootTenantAwareTrait(): void
    {
        static::addGlobalScope('tenant', function (Builder $query) {
            $tenant = app('tenant');
            if ($tenant instanceof Organization) {
                $query->where('organization_id', $tenant->id);
            }
        });

        static::creating(function (Model $model) {
            $tenant = app('tenant');
            if ($tenant instanceof Organization && in_array('organization_id', $model->getFillable())) {
                $model->organization_id = $tenant->id;
            }
        });
    }

    public function scopeForCurrentTenant(Builder $query): Builder
    {
        $tenant = app('tenant');
        if ($tenant instanceof Organization) {
            return $query->where('organization_id', $tenant->id);
        }
        return $query;
    }

    public function getTenant()
    {
        return app('tenant');
    }
}
