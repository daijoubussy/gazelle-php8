<?php

declare(strict_types=1);

namespace Gazelle\Infrastructure\Providers;

use Gazelle\Core\Container\Container;
use Gazelle\Core\Container\ServiceProvider;
use Gazelle\Infrastructure\Cache\CacheInterface;
use Gazelle\Infrastructure\Cache\MemcachedCache;

/**
 * Cache Service Provider
 */
final class CacheServiceProvider extends ServiceProvider
{
    public function register(Container $container): void
    {
        $container->singleton(CacheInterface::class, function (Container $c) {
            return new MemcachedCache($c->config);
        });

        // Alias for convenience
        $container->alias('cache', CacheInterface::class);
    }
}
