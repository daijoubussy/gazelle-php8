<?php

declare(strict_types=1);

namespace Gazelle\Core;

use Gazelle\Core\Container\Container;
use Gazelle\Core\Config\Configuration;
use Gazelle\Core\Http\Kernel;
use Gazelle\Core\Http\Request;
use Gazelle\Core\Http\Response;

/**
 * Application Bootstrap
 *
 * Single entry point for the application. Handles initialization,
 * dependency injection setup, and request lifecycle.
 */
final class Bootstrap
{
    private static ?Container $container = null;
    private static bool $booted = false;

    /**
     * Boot the application
     */
    public static function boot(string $basePath): Container
    {
        if (self::$booted) {
            return self::$container;
        }

        // Load environment and configuration
        $config = Configuration::load($basePath);

        // Initialize container with configuration
        self::$container = new Container($config);

        // Register core service providers
        self::registerProviders();

        self::$booted = true;

        return self::$container;
    }

    /**
     * Handle an HTTP request
     */
    public static function handle(Request $request): Response
    {
        if (!self::$booted) {
            throw new \RuntimeException('Application not booted. Call Bootstrap::boot() first.');
        }

        $kernel = self::$container->get(Kernel::class);

        return $kernel->handle($request);
    }

    /**
     * Get the container instance
     */
    public static function container(): Container
    {
        if (!self::$booted) {
            throw new \RuntimeException('Application not booted.');
        }

        return self::$container;
    }

    /**
     * Register service providers
     */
    private static function registerProviders(): void
    {
        $providers = [
            \Gazelle\Infrastructure\Providers\DatabaseServiceProvider::class,
            \Gazelle\Infrastructure\Providers\CacheServiceProvider::class,
            \Gazelle\Infrastructure\Providers\RepositoryServiceProvider::class,
            \Gazelle\Infrastructure\Providers\EventServiceProvider::class,
        ];

        foreach ($providers as $provider) {
            self::$container->register(new $provider());
        }
    }

    /**
     * Terminate the application
     */
    public static function terminate(): void
    {
        if (self::$container !== null) {
            self::$container->terminate();
        }

        self::$container = null;
        self::$booted = false;
    }
}
