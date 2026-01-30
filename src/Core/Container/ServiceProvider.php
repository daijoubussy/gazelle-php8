<?php

declare(strict_types=1);

namespace Gazelle\Core\Container;

/**
 * Service Provider Interface
 *
 * Service providers are the central place to configure and bind
 * services into the dependency injection container.
 */
abstract class ServiceProvider
{
    /**
     * Register services into the container
     */
    abstract public function register(Container $container): void;

    /**
     * Boot services after all providers are registered
     */
    public function boot(Container $container): void
    {
        // Override in subclass if needed
    }

    /**
     * Cleanup when application terminates
     */
    public function terminate(): void
    {
        // Override in subclass if needed
    }
}
