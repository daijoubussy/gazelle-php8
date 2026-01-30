<?php

declare(strict_types=1);

namespace Gazelle\Infrastructure\Providers;

use Gazelle\Core\Container\Container;
use Gazelle\Core\Container\ServiceProvider;
use Gazelle\Domain\User\UserRepository;
use Gazelle\Domain\Torrent\TorrentRepository;
use Gazelle\Infrastructure\Persistence\UserRepositoryImpl;
use Gazelle\Infrastructure\Persistence\TorrentRepositoryImpl;

/**
 * Repository Service Provider
 *
 * Binds repository interfaces to their implementations.
 */
final class RepositoryServiceProvider extends ServiceProvider
{
    public function register(Container $container): void
    {
        // User repository
        $container->singleton(UserRepository::class, function (Container $c) {
            return new UserRepositoryImpl($c->get(\Gazelle\Infrastructure\Persistence\DatabaseConnection::class));
        });

        // Torrent repository
        $container->singleton(TorrentRepository::class, function (Container $c) {
            return new TorrentRepositoryImpl($c->get(\Gazelle\Infrastructure\Persistence\DatabaseConnection::class));
        });
    }
}
