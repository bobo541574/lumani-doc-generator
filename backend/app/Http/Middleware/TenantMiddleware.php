<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Organization;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $tenantId = $request->header('X-Tenant-ID')
            ?? $this->resolveFromDomain($request->getHost());

        if (!$tenantId) {
            abort(400, 'Tenant identifier required');
        }

        $tenant = Cache::remember(
            "tenant:{$tenantId}",
            3600,
            function () use ($tenantId) {
                $query = Organization::query();

                if (Str::isUuid($tenantId)) {
                    $query->where('id', $tenantId);
                } else {
                    $query->where('slug', $tenantId);
                }

                return $query->firstOrFail();
            }
        );

        if (!$tenant->is_active) {
            abort(403, 'Organization is inactive');
        }

        app()->instance('tenant', $tenant);
        view()->share('tenant', $tenant);

        return $next($request);
    }

    private function resolveFromDomain(string $host): ?string
    {
        $domain = preg_replace('#^https?://#', '', strtolower($host));
        $org = Organization::where('domain', $domain)->first();
        return $org?->id;
    }
}
