<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class RateLimitMiddleware
{
    protected int $limit;
    protected int $window;

    public function handle(Request $request, Closure $next)
    {
        $this->limit = (int) config('api.rate_limit', 100);
        $this->window = (int) config('api.rate_window', 60);

        $key = 'rate_limit:' . ($request->header('X-API-Key') ?? $request->ip());
        $now = microtime(true);
        $windowStart = $now - $this->window;

        Redis::zremrangebyscore($key, 0, $windowStart);

        $current = (int) Redis::zcard($key);

        if ($current >= $this->limit) {
            $oldest = Redis::zrange($key, 0, 0, 'WITHSCORES');
            $retryAfter = !empty($oldest)
                ? ceil($oldest[array_key_last($oldest)] + $this->window - $now)
                : $this->window;

            return response()->json([
                'error' => 'Rate limit exceeded',
                'retry_after' => (int) $retryAfter,
            ], 429)->withHeaders([
                'X-RateLimit-Limit' => $this->limit,
                'X-RateLimit-Remaining' => 0,
                'X-RateLimit-Reset' => ceil($windowStart + $this->window),
                'Retry-After' => (int) $retryAfter,
            ]);
        }

        Redis::zadd($key, $now, uniqid('', true) . ':' . $request->ip());
        Redis::expire($key, $this->window);

        $response = $next($request);

        $response->headers->set('X-RateLimit-Limit', (string) $this->limit);
        $response->headers->set('X-RateLimit-Remaining', (string) max(0, $this->limit - $current - 1));
        $response->headers->set('X-RateLimit-Reset', (string) ceil($windowStart + $this->window));

        return $response;
    }
}
