<?php

declare(strict_types=1);

namespace Gazelle\Infrastructure\Cache;

/**
 * Cache Interface
 *
 * Simple caching abstraction.
 */
interface CacheInterface
{
    /**
     * Get a value from cache
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Set a value in cache
     */
    public function set(string $key, mixed $value, int $ttl = 3600): bool;

    /**
     * Delete a value from cache
     */
    public function delete(string $key): bool;

    /**
     * Check if a key exists
     */
    public function has(string $key): bool;

    /**
     * Clear all cached values
     */
    public function clear(): bool;

    /**
     * Get multiple values
     *
     * @param array<string> $keys
     * @return array<string, mixed>
     */
    public function getMultiple(array $keys): array;

    /**
     * Set multiple values
     *
     * @param array<string, mixed> $values
     */
    public function setMultiple(array $values, int $ttl = 3600): bool;

    /**
     * Delete multiple values
     *
     * @param array<string> $keys
     */
    public function deleteMultiple(array $keys): bool;

    /**
     * Get or set a cached value
     *
     * @template T
     * @param callable(): T $callback
     * @return T
     */
    public function remember(string $key, int $ttl, callable $callback): mixed;
}
