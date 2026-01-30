<?php

declare(strict_types=1);

namespace Gazelle\Http\Middleware;

use Gazelle\Application\User\Services\SessionService;
use Gazelle\Core\Http\Middleware\Middleware;
use Gazelle\Core\Http\Request;
use Gazelle\Core\Http\Response;

/**
 * Authentication Middleware
 *
 * Validates the session token and adds user info to the request.
 */
final readonly class AuthMiddleware implements Middleware
{
    public function __construct(
        private SessionService $sessionService
    ) {}

    public function handle(Request $request, callable $next): Response
    {
        $token = $this->extractToken($request);

        if ($token === null) {
            return Response::unauthorized('Authentication required');
        }

        $userId = $this->sessionService->validate($token);

        if ($userId === null) {
            return Response::unauthorized('Invalid or expired token');
        }

        // Add user ID to request attributes
        $request = $request->withAttributes([
            'user_id' => $userId->value(),
            'session_token' => $token,
        ]);

        return $next($request);
    }

    private function extractToken(Request $request): ?string
    {
        // Check Authorization header first
        $bearer = $request->bearerToken();
        if ($bearer !== null) {
            return $bearer;
        }

        // Check for session cookie
        $cookie = $request->cookie('session_token');
        if ($cookie !== null) {
            return (string) $cookie;
        }

        // Check query parameter (for torrent downloads)
        $queryToken = $request->query('token');
        if ($queryToken !== null) {
            return (string) $queryToken;
        }

        return null;
    }
}
