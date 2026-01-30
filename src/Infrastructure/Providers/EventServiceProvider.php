<?php

declare(strict_types=1);

namespace Gazelle\Infrastructure\Providers;

use Gazelle\Application\Common\EventDispatcher;
use Gazelle\Core\Container\Container;
use Gazelle\Core\Container\ServiceProvider;
use Gazelle\Infrastructure\Events\SyncEventDispatcher;

/**
 * Event Service Provider
 */
final class EventServiceProvider extends ServiceProvider
{
    public function register(Container $container): void
    {
        $container->singleton(EventDispatcher::class, function (Container $c) {
            return new SyncEventDispatcher($c);
        });
    }
}
