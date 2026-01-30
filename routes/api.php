<?php

declare(strict_types=1);

/**
 * API Routes
 *
 * All API routes are prefixed with /api and use JSON responses.
 */

use Gazelle\Core\Routing\Router;
use Gazelle\Http\Controllers\AuthController;
use Gazelle\Http\Controllers\TorrentController;
use Gazelle\Http\Controllers\UserController;
use Gazelle\Http\Middleware\AuthMiddleware;
use Gazelle\Http\Middleware\RateLimitMiddleware;

return function (Router $router): void {
    // Public routes
    $router->group(['prefix' => '/api'], function (Router $router) {
        // Authentication
        $router->post('/auth/login', AuthController::class, 'login')
            ->name('auth.login');

        $router->post('/auth/register', AuthController::class, 'register')
            ->name('auth.register');
    });

    // Protected routes
    $router->group([
        'prefix' => '/api',
        'middleware' => [AuthMiddleware::class, RateLimitMiddleware::class],
    ], function (Router $router) {
        // Auth
        $router->post('/auth/logout', AuthController::class, 'logout')
            ->name('auth.logout');

        // Users
        $router->get('/users/me', UserController::class, 'me')
            ->name('users.me');

        $router->get('/users/{id}', UserController::class, 'show')
            ->where('id', '\d+')
            ->name('users.show');

        // Torrents
        $router->get('/torrents', TorrentController::class, 'index')
            ->name('torrents.index');

        $router->get('/torrents/{id}', TorrentController::class, 'show')
            ->where('id', '\d+')
            ->name('torrents.show');

        $router->post('/torrents', TorrentController::class, 'store')
            ->name('torrents.store');

        $router->get('/torrents/{id}/download', TorrentController::class, 'download')
            ->where('id', '\d+')
            ->name('torrents.download');
    });
};
