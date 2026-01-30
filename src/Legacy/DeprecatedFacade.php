<?php

declare(strict_types=1);

namespace Gazelle\Legacy;

use Gazelle\Core\Container\Container;

/**
 * Deprecated Facade
 *
 * Provides backwards compatibility for legacy code that still uses
 * the old global/static patterns. This facade maps old calls to
 * the new architecture.
 *
 * @deprecated Use the new DI-based services instead
 */
final class DeprecatedFacade
{
    private static ?Container $container = null;

    /**
     * Set the container for the facade
     */
    public static function setContainer(Container $container): void
    {
        self::$container = $container;
    }

    /**
     * Get the container
     */
    private static function container(): Container
    {
        if (self::$container === null) {
            throw new \RuntimeException(
                'Container not set. Call DeprecatedFacade::setContainer() first.'
            );
        }

        return self::$container;
    }

    /**
     * Get the cache service
     *
     * @deprecated Use CacheInterface via DI
     */
    public static function cache(): \Gazelle\Infrastructure\Cache\CacheInterface
    {
        return self::container()->get(\Gazelle\Infrastructure\Cache\CacheInterface::class);
    }

    /**
     * Get the database connection
     *
     * @deprecated Use DatabaseConnection via DI
     */
    public static function db(): \Gazelle\Infrastructure\Persistence\DatabaseConnection
    {
        return self::container()->get(\Gazelle\Infrastructure\Persistence\DatabaseConnection::class);
    }

    /**
     * Get the user service
     *
     * @deprecated Use UserService via DI
     */
    public static function userService(): \Gazelle\Application\User\Services\UserService
    {
        return self::container()->get(\Gazelle\Application\User\Services\UserService::class);
    }

    /**
     * Get the permission service
     *
     * @deprecated Use PermissionService via DI
     */
    public static function permissionService(): \Gazelle\Application\User\Services\PermissionService
    {
        return self::container()->get(\Gazelle\Application\User\Services\PermissionService::class);
    }

    /**
     * Get the format service
     *
     * @deprecated Use FormatService via DI
     */
    public static function formatService(): \Gazelle\Application\Common\Services\FormatService
    {
        return self::container()->get(\Gazelle\Application\Common\Services\FormatService::class);
    }

    /**
     * Get the text service
     *
     * @deprecated Use TextService via DI
     */
    public static function textService(): \Gazelle\Application\Common\Services\TextService
    {
        return self::container()->get(\Gazelle\Application\Common\Services\TextService::class);
    }

    /**
     * Get the torrent service
     *
     * @deprecated Use TorrentService via DI
     */
    public static function torrentService(): \Gazelle\Application\Torrent\Services\TorrentService
    {
        return self::container()->get(\Gazelle\Application\Torrent\Services\TorrentService::class);
    }

    /**
     * Get the forum service
     *
     * @deprecated Use ForumService via DI
     */
    public static function forumService(): \Gazelle\Application\Forum\Services\ForumService
    {
        return self::container()->get(\Gazelle\Application\Forum\Services\ForumService::class);
    }
}

/**
 * Legacy global function wrappers
 * These map old global functions to the new services
 */

if (!function_exists('check_perms')) {
    /**
     * @deprecated Use PermissionService::hasPermission()
     */
    function check_perms(string $permission, int $minLevel = 0): bool
    {
        global $LoggedUser;

        if (!isset($LoggedUser['ID'])) {
            return false;
        }

        $permService = DeprecatedFacade::permissionService();

        return $permService->hasPermission(
            $LoggedUser['PermissionID'] ?? 0,
            $permission,
            $LoggedUser['CustomPermissions'] ?? null
        );
    }
}

if (!function_exists('display_str')) {
    /**
     * @deprecated Use FormatService::escapeHtml()
     */
    function display_str(?string $str): string
    {
        if ($str === null || $str === '') {
            return '';
        }

        return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

if (!function_exists('is_number')) {
    /**
     * @deprecated Use proper type hints
     */
    function is_number(mixed $value): bool
    {
        if (is_int($value) || is_float($value)) {
            return true;
        }

        if (!is_string($value)) {
            return false;
        }

        return is_numeric($value) && !str_contains($value, 'x');
    }
}

if (!function_exists('get_size')) {
    /**
     * @deprecated Use FormatService::formatSize()
     */
    function get_size(int|float $bytes, int $precision = 2): string
    {
        return DeprecatedFacade::formatService()->formatSize($bytes, $precision);
    }
}

if (!function_exists('time_diff')) {
    /**
     * @deprecated Use FormatService::formatTimeAgo()
     */
    function time_diff(string|\DateTimeInterface $time, int $levels = 2, bool $span = true): string
    {
        if (is_string($time)) {
            $time = new \DateTimeImmutable($time);
        }

        return DeprecatedFacade::formatService()->formatTimeAgo($time, $levels, $span);
    }
}

if (!function_exists('send_irc')) {
    /**
     * @deprecated Use IrcService directly
     */
    function send_irc(string $raw): bool
    {
        // This would need the IRC service configured
        // For now, just log and return
        error_log("IRC (deprecated): {$raw}");
        return true;
    }
}
