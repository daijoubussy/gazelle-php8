<?php

declare(strict_types=1);

namespace Gazelle\Http\Middleware;

use Gazelle\Core\Http\Middleware\Middleware;
use Gazelle\Core\Http\Request;
use Gazelle\Core\Http\Response;
use Gazelle\Infrastructure\Cache\CacheInterface;

/**
 * Rate Limiting Middleware
 */
final readonly class RateLimitMiddleware implements Middleware
{
    public function __construct(
        private CacheInterface $cache,
        private int $maxRequests = 60,
        private int $windowSeconds = 60
    ) {}

    public function handle(Request $request, callable $next): Response
    {
        $key = $this->getRateLimitKey($request);
        $current = (int) $this->cache->get($key, 0);

        if ($current >= $this->maxRequests) {
            return Response::json([
                'error' => 'Too Many Requests',
                'retry_after' => $this->windowSeconds,
            ], 429)->withHeader('Retry-After', (string) $this->windowSeconds);
        }

        $this->cache->set($key, $current + 1, $this->windowSeconds);

        $response = $next($request);

        return $response
            ->withHeader('X-RateLimit-Limit', (string) $this->maxRequests)
            ->withHeader('X-RateLimit-Remaining', (string) ($this->maxRequests - $current - 1));
    }

    private function getRateLimitKey(Request $request): string
    {
        $identifier = $request->attribute('user_id') ?? $request->ip();

        return "rate_limit:{$identifier}";
    }
}
