<?php

declare(strict_types=1);

namespace Gazelle\Core\Http;

use Gazelle\Core\Container\Container;
use Gazelle\Core\Http\Middleware\MiddlewareStack;
use Gazelle\Core\Routing\Router;

/**
 * HTTP Kernel
 *
 * Handles the request lifecycle through middleware and routing.
 */
final readonly class Kernel
{
    public function __construct(
        private Container $container,
        private Router $router,
        private MiddlewareStack $middleware
    ) {}

    /**
     * Handle an incoming request
     */
    public function handle(Request $request): Response
    {
        try {
            // Run through middleware stack
            $response = $this->middleware->handle($request, function (Request $request) {
                return $this->dispatchToRouter($request);
            });

            return $response;
        } catch (\Throwable $e) {
            return $this->handleException($e, $request);
        }
    }

    /**
     * Dispatch the request to the router
     */
    private function dispatchToRouter(Request $request): Response
    {
        $route = $this->router->match($request);

        if ($route === null) {
            return Response::notFound('Page not found');
        }

        // Add route parameters to request
        $request = $request->withAttributes($route->parameters());

        // Run route middleware
        $response = $route->middleware()->handle($request, function (Request $request) use ($route) {
            return $this->callController($route, $request);
        });

        return $response;
    }

    /**
     * Call the controller action
     */
    private function callController(mixed $route, Request $request): Response
    {
        $controller = $this->container->get($route->controller());
        $action = $route->action();

        $response = $controller->$action($request, ...$route->parameters());

        if ($response instanceof Response) {
            return $response;
        }

        if (is_array($response)) {
            return Response::json($response);
        }

        if (is_string($response)) {
            return Response::make($response);
        }

        throw new \RuntimeException('Invalid controller response type');
    }

    /**
     * Handle an exception
     */
    private function handleException(\Throwable $e, Request $request): Response
    {
        // Log the exception
        error_log($e->getMessage() . "\n" . $e->getTraceAsString());

        if ($request->expectsJson()) {
            return Response::json([
                'error' => true,
                'message' => $e->getMessage(),
            ], 500);
        }

        return Response::serverError($e->getMessage());
    }
}
