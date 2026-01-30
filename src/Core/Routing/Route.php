<?php

declare(strict_types=1);

namespace Gazelle\Core\Routing;

use Gazelle\Core\Container\Container;
use Gazelle\Core\Http\Middleware\MiddlewareStack;

/**
 * Route
 *
 * Represents a single route definition.
 */
final class Route
{
    private string $pattern;

    /** @var array<string, string> */
    private array $parameterPatterns = [];

    /** @var array<string, mixed> */
    private array $matchedParameters = [];

    private ?string $name = null;

    /**
     * @param array<class-string> $middleware
     */
    public function __construct(
        private readonly string $method,
        private readonly string $uri,
        private readonly string $controller,
        private readonly string $action,
        private array $middleware,
        private readonly Container $container
    ) {
        $this->pattern = $this->compilePattern($uri);
    }

    /**
     * Check if this route matches the given path
     */
    public function matches(string $path): bool
    {
        if (preg_match($this->pattern, $path, $matches)) {
            // Extract named parameters
            $this->matchedParameters = [];
            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $this->matchedParameters[$key] = $value;
                }
            }
            return true;
        }

        return false;
    }

    /**
     * Get the controller class
     */
    public function controller(): string
    {
        return $this->controller;
    }

    /**
     * Get the action method
     */
    public function action(): string
    {
        return $this->action;
    }

    /**
     * Get matched parameters
     *
     * @return array<string, mixed>
     */
    public function parameters(): array
    {
        return $this->matchedParameters;
    }

    /**
     * Get the middleware stack for this route
     */
    public function middleware(): MiddlewareStack
    {
        $stack = MiddlewareStack::empty($this->container);

        foreach ($this->middleware as $middlewareClass) {
            $stack->push($middlewareClass);
        }

        return $stack;
    }

    /**
     * Add middleware to this route
     *
     * @param class-string ...$middleware
     */
    public function withMiddleware(string ...$middleware): self
    {
        $this->middleware = array_merge($this->middleware, $middleware);
        return $this;
    }

    /**
     * Set a name for this route
     */
    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Set a pattern constraint for a parameter
     */
    public function where(string $parameter, string $pattern): self
    {
        $this->parameterPatterns[$parameter] = $pattern;
        $this->pattern = $this->compilePattern($this->uri);
        return $this;
    }

    /**
     * Generate a URL for this route with given parameters
     *
     * @param array<string, mixed> $parameters
     */
    public function generateUrl(array $parameters): string
    {
        $url = $this->uri;

        foreach ($parameters as $key => $value) {
            $url = preg_replace("/\{" . $key . "\??}/", (string) $value, $url);
        }

        // Remove optional parameters that weren't provided
        $url = preg_replace("/\{[^}]+\?}/", '', $url);

        return $url;
    }

    /**
     * Compile the URI pattern into a regex
     */
    private function compilePattern(string $uri): string
    {
        // Escape forward slashes
        $pattern = preg_quote($uri, '#');

        // Replace {param} with named capture group
        $pattern = preg_replace_callback(
            '/\\\{(\w+)\\\}/',
            function ($matches) {
                $param = $matches[1];
                $constraint = $this->parameterPatterns[$param] ?? '[^/]+';
                return "(?P<{$param}>{$constraint})";
            },
            $pattern
        );

        // Replace {param?} with optional named capture group
        $pattern = preg_replace_callback(
            '/\\\{(\w+)\\\?\\\}/',
            function ($matches) {
                $param = $matches[1];
                $constraint = $this->parameterPatterns[$param] ?? '[^/]+';
                return "(?:/(?P<{$param}>{$constraint}))?";
            },
            $pattern
        );

        return '#^' . $pattern . '$#';
    }
}
