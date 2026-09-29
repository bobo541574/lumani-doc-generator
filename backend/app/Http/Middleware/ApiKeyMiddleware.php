<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ApiKey;
use Carbon\Carbon;

class ApiKeyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('X-API-Key');

        if (!$apiKey) {
            return response()->json(['error' => 'API Key required'], 401);
        }

        $keyHash = hash('sha256', $apiKey);

        $key = ApiKey::where('key_hash', $keyHash)
            ->where('is_active', true)
            ->first();

        if (!$key) {
            return response()->json(['error' => 'Invalid API Key'], 401);
        }

        if ($key->expires_at && Carbon::parse($key->expires_at)->isPast()) {
            return response()->json(['error' => 'API Key expired'], 401);
        }

        $permission = match ($request->method()) {
            'GET', 'HEAD' => 'read',
            'POST', 'PUT', 'PATCH' => 'write',
            'DELETE' => 'delete',
            default => 'read',
        };

        if (!in_array($permission, $key->permissions, true)) {
            return response()->json(['error' => 'Insufficient permissions'], 403);
        }

        $request->merge([
            'api_key_id' => $key->id,
            'project_id' => $key->project_id,
        ]);

        $key->update([
            'last_used_at' => now(),
            'usage_count' => $key->usage_count + 1,
        ]);

        return $next($request);
    }
}
