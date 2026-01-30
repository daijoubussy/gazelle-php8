<?php

declare(strict_types=1);

namespace Gazelle\Core\Http\Middleware;

use Gazelle\Core\Http\Request;
use Gazelle\Core\Http\Response;

/**
 * Middleware Interface
 *
 * Middleware intercepts requests before they reach the controller
 * and can modify requests/responses.
 */
interface Middleware
{
    /**
     * Handle the request
     *
     * @param callable(Request): Response $next
     */
    public function handle(Request $request, callable $next): Response;
}
