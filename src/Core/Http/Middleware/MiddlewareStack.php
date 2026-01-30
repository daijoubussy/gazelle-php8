<?php

declare(strict_types=1);

namespace Gazelle\Core\Http\Middleware;

use Gazelle\Core\Container\Container;
use Gazelle\Core\Http\Request;
use Gazelle\Core\Http\Response;

/**
 * Middleware Stack
 *
 * Manages and executes a stack of middleware in order.
 */
final class MiddlewareStack
{
    /** @var array<class-string<Middleware>|Middleware> */
    private array $middleware = [];

    public function __construct(
        private readonly Container $container
    ) {}

    /**
     * Add middleware to the stack
     *
     * @param class-string<Middleware>|Middleware $middleware
     */
    public function push(string|Middleware $middleware): self
    {
        $this->middleware[] = $middleware;
        return $this;
    }

    /**
     * Add middleware to the beginning of the stack
     *
     * @param class-string<Middleware>|Middleware $middleware
     */
    public function prepend(string|Middleware $middleware): self
    {
        array_unshift($this->middleware, $middleware);
        return $this;
    }

    /**
     * Handle the request through all middleware
     *
     * @param callable(Request): Response $destination
     */
    public function handle(Request $request, callable $destination): Response
    {
        $pipeline = array_reduce(
            array_reverse($this->middleware),
            fn (callable $next, string|Middleware $middleware) => fn (Request $request) =>
                $this->resolveMiddleware($middleware)->handle($request, $next),
            $destination
        );

        return $pipeline($request);
    }

    /**
     * Resolve middleware from class name or instance
     */
    private function resolveMiddleware(string|Middleware $middleware): Middleware
    {
        if ($middleware instanceof Middleware) {
            return $middleware;
        }

        return $this->container->get($middleware);
    }

    /**
     * Create a new empty stack
     */
    public static function empty(Container $container): self
    {
        return new self($container);
    }
}
