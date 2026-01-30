<?php

declare(strict_types=1);

/**
 * Helper Functions
 *
 * Global helper functions available throughout the application.
 */

if (!function_exists('env')) {
    /**
     * Get an environment variable with a default fallback
     */
    function env(string $key, mixed $default = null): mixed
    {
        $value = getenv($key);

        if ($value === false) {
            return $default;
        }

        // Handle boolean strings
        return match (strtolower($value)) {
            'true', '(true)' => true,
            'false', '(false)' => false,
            'null', '(null)' => null,
            'empty', '(empty)' => '',
            default => $value,
        };
    }
}

if (!function_exists('app')) {
    /**
     * Get the application container or resolve a service
     */
    function app(?string $abstract = null): mixed
    {
        $container = \Gazelle\Core\Bootstrap::container();

        if ($abstract === null) {
            return $container;
        }

        return $container->get($abstract);
    }
}

if (!function_exists('config')) {
    /**
     * Get a configuration value
     */
    function config(string $key, mixed $default = null): mixed
    {
        return app(\Gazelle\Core\Config\Configuration::class)->get($key, $default);
    }
}

if (!function_exists('cache')) {
    /**
     * Get the cache instance or a cached value
     */
    function cache(?string $key = null, mixed $default = null): mixed
    {
        $cache = app(\Gazelle\Infrastructure\Cache\CacheInterface::class);

        if ($key === null) {
            return $cache;
        }

        return $cache->get($key, $default);
    }
}

if (!function_exists('now')) {
    /**
     * Get the current datetime
     */
    function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }
}

if (!function_exists('format_bytes')) {
    /**
     * Format bytes to human readable string
     */
    function format_bytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        if ($bytes === 0) {
            return '0 B';
        }

        $unitIndex = 0;
        $value = (float) $bytes;

        while ($value >= 1024 && $unitIndex < count($units) - 1) {
            $value /= 1024;
            $unitIndex++;
        }

        return round($value, $precision) . ' ' . $units[$unitIndex];
    }
}

if (!function_exists('format_ratio')) {
    /**
     * Format a ratio value
     */
    function format_ratio(float $ratio, int $precision = 2): string
    {
        if ($ratio === INF) {
            return '∞';
        }

        return number_format($ratio, $precision);
    }
}
