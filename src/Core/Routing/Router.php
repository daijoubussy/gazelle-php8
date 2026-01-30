<?php

declare(strict_types=1);

namespace Gazelle\Core\Routing;

use Gazelle\Core\Container\Container;
use Gazelle\Core\Http\Request;

/**
 * Router
 *
 * Matches requests to routes and controllers.
 */
final class Router
{
    /** @var array<string, array<Route>> */
    private array $routes = [];

    /** @var array<string, Route> */
    private array $namedRoutes = [];

    /** @var array<class-string> */
    private array $groupMiddleware = [];

    private string $groupPrefix = '';

    public function __construct(
        private readonly Container $container
    ) {}

    /**
     * Register a GET route
     */
    public function get(string $uri, string $controller, string $action): Route
    {
        return $this->addRoute('GET', $uri, $controller, $action);
    }

    /**
     * Register a POST route
     */
    public function post(string $uri, string $controller, string $action): Route
    {
        return $this->addRoute('POST', $uri, $controller, $action);
    }

    /**
     * Register a PUT route
     */
    public function put(string $uri, string $controller, string $action): Route
    {
        return $this->addRoute('PUT', $uri, $controller, $action);
    }

    /**
     * Register a PATCH route
     */
    public function patch(string $uri, string $controller, string $action): Route
    {
        return $this->addRoute('PATCH', $uri, $controller, $action);
    }

    /**
     * Register a DELETE route
     */
    public function delete(string $uri, string $controller, string $action): Route
    {
        return $this->addRoute('DELETE', $uri, $controller, $action);
    }

    /**
     * Register a route for any HTTP method
     */
    public function any(string $uri, string $controller, string $action): Route
    {
        $route = $this->addRoute('GET', $uri, $controller, $action);
        $this->addRoute('POST', $uri, $controller, $action);
        $this->addRoute('PUT', $uri, $controller, $action);
        $this->addRoute('PATCH', $uri, $controller, $action);
        $this->addRoute('DELETE', $uri, $controller, $action);

        return $route;
    }

    /**
     * Create a route group with shared attributes
     *
     * @param array{prefix?: string, middleware?: array<class-string>} $attributes
     */
    public function group(array $attributes, callable $callback): void
    {
        $previousPrefix = $this->groupPrefix;
        $previousMiddleware = $this->groupMiddleware;

        $this->groupPrefix .= $attributes['prefix'] ?? '';
        $this->groupMiddleware = array_merge(
            $this->groupMiddleware,
            $attributes['middleware'] ?? []
        );

        $callback($this);

        $this->groupPrefix = $previousPrefix;
        $this->groupMiddleware = $previousMiddleware;
    }

    /**
     * Match a request to a route
     */
    public function match(Request $request): ?Route
    {
        $method = $request->method();
        $path = $request->path();

        if (!isset($this->routes[$method])) {
            return null;
        }

        foreach ($this->routes[$method] as $route) {
            if ($route->matches($path)) {
                return $route;
            }
        }

        return null;
    }

    /**
     * Generate URL for a named route
     *
     * @param array<string, mixed> $parameters
     */
    public function url(string $name, array $parameters = []): string
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new \InvalidArgumentException("Route not found: {$name}");
        }

        return $this->namedRoutes[$name]->generateUrl($parameters);
    }

    /**
     * Add a route
     */
    private function addRoute(
        string $method,
        string $uri,
        string $controller,
        string $action
    ): Route {
        $uri = $this->groupPrefix . '/' . ltrim($uri, '/');
        $uri = '/' . trim($uri, '/');

        $route = new Route(
            method: $method,
            uri: $uri,
            controller: $controller,
            action: $action,
            middleware: $this->groupMiddleware,
            container: $this->container
        );

        $this->routes[$method][] = $route;

        return $route;
    }

    /**
     * Register a named route
     */
    public function registerNamed(string $name, Route $route): void
    {
        $this->namedRoutes[$name] = $route;
    }
}
