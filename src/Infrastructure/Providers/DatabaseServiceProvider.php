<?php

declare(strict_types=1);

namespace Gazelle\Infrastructure\Providers;

use Gazelle\Core\Container\Container;
use Gazelle\Core\Container\ServiceProvider;
use Gazelle\Infrastructure\Persistence\DatabaseConnection;

/**
 * Database Service Provider
 */
final class DatabaseServiceProvider extends ServiceProvider
{
    public function register(Container $container): void
    {
        $container->singleton(DatabaseConnection::class, function (Container $c) {
            return new DatabaseConnection($c->config);
        });
    }

    public function terminate(): void
    {
        // Connection cleanup is handled by PHP
    }
}
