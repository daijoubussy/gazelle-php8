<?php

declare(strict_types=1);

namespace Gazelle\Http\Middleware;

use Gazelle\Core\Http\Middleware\Middleware;
use Gazelle\Core\Http\Request;
use Gazelle\Core\Http\Response;

/**
 * CORS Middleware
 */
final readonly class CorsMiddleware implements Middleware
{
    /**
     * @param array<string> $allowedOrigins
     * @param array<string> $allowedMethods
     * @param array<string> $allowedHeaders
     */
    public function __construct(
        private array $allowedOrigins = ['*'],
        private array $allowedMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
        private array $allowedHeaders = ['Content-Type', 'Authorization', 'X-Requested-With'],
        private bool $allowCredentials = true,
        private int $maxAge = 86400
    ) {}

    public function handle(Request $request, callable $next): Response
    {
        // Handle preflight requests
        if ($request->isMethod('OPTIONS')) {
            return $this->createPreflightResponse($request);
        }

        $response = $next($request);

        return $this->addCorsHeaders($request, $response);
    }

    private function createPreflightResponse(Request $request): Response
    {
        $response = Response::noContent();

        return $this->addCorsHeaders($request, $response);
    }

    private function addCorsHeaders(Request $request, Response $response): Response
    {
        $origin = $request->header('origin', '');

        // Check if origin is allowed
        $allowedOrigin = $this->getAllowedOrigin($origin);

        $response = $response
            ->withHeader('Access-Control-Allow-Origin', $allowedOrigin)
            ->withHeader('Access-Control-Allow-Methods', implode(', ', $this->allowedMethods))
            ->withHeader('Access-Control-Allow-Headers', implode(', ', $this->allowedHeaders))
            ->withHeader('Access-Control-Max-Age', (string) $this->maxAge);

        if ($this->allowCredentials && $allowedOrigin !== '*') {
            $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');
        }

        return $response;
    }

    private function getAllowedOrigin(string $origin): string
    {
        if (in_array('*', $this->allowedOrigins, true)) {
            return '*';
        }

        if (in_array($origin, $this->allowedOrigins, true)) {
            return $origin;
        }

        return $this->allowedOrigins[0] ?? '';
    }
}
