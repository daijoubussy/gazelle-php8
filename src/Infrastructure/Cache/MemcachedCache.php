<?php

declare(strict_types=1);

namespace Gazelle\Infrastructure\Cache;

use Gazelle\Core\Config\Configuration;

/**
 * Memcached Cache Implementation
 */
final class MemcachedCache implements CacheInterface
{
    private \Memcached $memcached;
    private string $prefix;

    public function __construct(Configuration $config)
    {
        $this->memcached = new \Memcached();
        $this->prefix = $config->get('cache.prefix', 'gazelle:');

        $servers = $config->get('cache.servers', [['127.0.0.1', 11211]]);

        foreach ($servers as $server) {
            $this->memcached->addServer($server[0], $server[1]);
        }

        $this->memcached->setOptions([
            \Memcached::OPT_BINARY_PROTOCOL => true,
            \Memcached::OPT_COMPRESSION => true,
            \Memcached::OPT_CONNECT_TIMEOUT => 100,
            \Memcached::OPT_RETRY_TIMEOUT => 1,
        ]);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->memcached->get($this->prefix . $key);

        if ($this->memcached->getResultCode() === \Memcached::RES_NOTFOUND) {
            return $default;
        }

        return $value;
    }

    public function set(string $key, mixed $value, int $ttl = 3600): bool
    {
        return $this->memcached->set($this->prefix . $key, $value, $ttl);
    }

    public function delete(string $key): bool
    {
        return $this->memcached->delete($this->prefix . $key);
    }

    public function has(string $key): bool
    {
        $this->memcached->get($this->prefix . $key);

        return $this->memcached->getResultCode() !== \Memcached::RES_NOTFOUND;
    }

    public function clear(): bool
    {
        return $this->memcached->flush();
    }

    public function getMultiple(array $keys): array
    {
        $prefixedKeys = array_map(
            fn (string $key) => $this->prefix . $key,
            $keys
        );

        $values = $this->memcached->getMulti($prefixedKeys) ?: [];

        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $values[$this->prefix . $key] ?? null;
        }

        return $result;
    }

    public function setMultiple(array $values, int $ttl = 3600): bool
    {
        $prefixedValues = [];
        foreach ($values as $key => $value) {
            $prefixedValues[$this->prefix . $key] = $value;
        }

        return $this->memcached->setMulti($prefixedValues, $ttl);
    }

    public function deleteMultiple(array $keys): bool
    {
        $prefixedKeys = array_map(
            fn (string $key) => $this->prefix . $key,
            $keys
        );

        $results = $this->memcached->deleteMulti($prefixedKeys);

        return !in_array(false, $results, true);
    }

    public function remember(string $key, int $ttl, callable $callback): mixed
    {
        $value = $this->get($key);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->set($key, $value, $ttl);

        return $value;
    }
}
