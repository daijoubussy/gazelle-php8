<?php

declare(strict_types=1);

/*************************************************************************|
|--------------- Caching class -------------------------------------------|
|*************************************************************************|

This class is a wrapper for the Memcached class (upgraded from Memcache),
and it's been written in order to better handle the caching of full pages
with bits of dynamic content that are different for every user.

Updated to use Memcached extension for PHP 8.5 compatibility.

// Unix sockets
memcached -d -m 5120 -s /var/run/memcached.sock -a 0777 -t16 -C -u root

// TCP bind
memcached -d -m 8192 -l 10.10.0.1 -t8 -C

|*************************************************************************/

if (!extension_loaded('memcached')) {
    die('Memcached Extension not loaded.');
}

class CACHE
{
    /**
     * Torrent Group cache version
     */
    public const GROUP_VERSION = 5;

    /** @var array<string, mixed> */
    public array $CacheHits = [];

    /** @var array<int|string, mixed> */
    public array $MemcacheDBArray = [];

    public string $MemcacheDBKey = '';
    protected bool $InTransaction = false;
    public float $Time = 0.0;

    /** @var array<int, array{host: string, port: int, buckets: int}> */
    private array $Servers = [];

    /** @var array<int, string> */
    private array $PersistentKeys = [
        'ajax_requests_*',
        'query_lock_*',
        'stats_*',
        'top10tor_*',
        'top10votes_*',
        'users_snatched_*',
        // Cache-based features
        'global_notification',
        'notifications_one_reads_*',
    ];

    /** @var array<string, bool> */
    private array $ClearedKeys = [];

    public bool $CanClear = false;
    public bool $InternalCache = true;

    private Memcached $memcached;

    /**
     * @param array<int, array{host: string, port: int, buckets: int}> $Servers
     */
    public function __construct(array $Servers)
    {
        $this->Servers = $Servers;
        $this->memcached = new Memcached();

        foreach ($Servers as $Server) {
            $host = $Server['host'];
            $port = $Server['port'];

            // Handle Unix socket connections
            if (str_starts_with($host, 'unix://')) {
                $this->memcached->addServer(substr($host, 7), 0);
            } else {
                $this->memcached->addServer($host, $port);
            }
        }

        // Set Memcached options for better performance
        $this->memcached->setOption(Memcached::OPT_BINARY_PROTOCOL, true);
        $this->memcached->setOption(Memcached::OPT_TCP_NODELAY, true);
        $this->memcached->setOption(Memcached::OPT_COMPRESSION, true);
    }

    //---------- Caching functions ----------//

    /**
     * Allows us to set an expiration on otherwise permanently cached values
     * Useful for disabled users, locked threads, basically reducing ram usage
     */
    public function expire_value(string $Key, int $Duration = 2592000): void
    {
        $StartTime = microtime(true);
        $value = $this->get($Key);
        if ($value !== false) {
            $this->set($Key, $value, $Duration);
        }
        $this->Time += (microtime(true) - $StartTime) * 1000;
    }

    /**
     * Wrapper for Memcached::set, with default duration of 30 days
     */
    public function cache_value(string $Key, mixed $Value, int $Duration = 2592000): void
    {
        $StartTime = microtime(true);
        if (empty($Key)) {
            trigger_error("Cache insert failed for empty key");
        }
        if (!$this->set($Key, $Value, $Duration)) {
            trigger_error("Cache insert failed for key $Key");
        }
        if ($this->InternalCache && array_key_exists($Key, $this->CacheHits)) {
            $this->CacheHits[$Key] = $Value;
        }
        $this->Time += (microtime(true) - $StartTime) * 1000;
    }

    /**
     * Wrapper for Memcached::add, with default duration of 30 days
     */
    public function add_value(string $Key, mixed $Value, int $Duration = 2592000): bool
    {
        $StartTime = microtime(true);
        $Added = $this->add($Key, $Value, $Duration);
        $this->Time += (microtime(true) - $StartTime) * 1000;
        return $Added;
    }

    public function replace_value(string $Key, mixed $Value, int $Duration = 2592000): void
    {
        $StartTime = microtime(true);
        $this->replace($Key, $Value, $Duration);
        if ($this->InternalCache && array_key_exists($Key, $this->CacheHits)) {
            $this->CacheHits[$Key] = $Value;
        }
        $this->Time += (microtime(true) - $StartTime) * 1000;
    }

    public function get_value(string $Key, bool $NoCache = false): mixed
    {
        if (!$this->InternalCache) {
            $NoCache = true;
        }
        $StartTime = microtime(true);
        if (empty($Key)) {
            trigger_error('Cache retrieval failed for empty key');
        }

        if (!empty($_GET['clearcache']) && $this->CanClear && !isset($this->ClearedKeys[$Key]) && !Misc::in_array_partial($Key, $this->PersistentKeys)) {
            if ($_GET['clearcache'] === '1') {
                // Because check_perms() isn't true until LoggedUser is pulled from the cache, we have to remove the entries loaded before the LoggedUser data
                // Because of this, not user cache data will require a secondary pageload following the clearcache to update
                if (count($this->CacheHits) > 0) {
                    foreach (array_keys($this->CacheHits) as $HitKey) {
                        if (!isset($this->ClearedKeys[$HitKey]) && !Misc::in_array_partial($HitKey, $this->PersistentKeys)) {
                            $this->delete($HitKey);
                            unset($this->CacheHits[$HitKey]);
                            $this->ClearedKeys[$HitKey] = true;
                        }
                    }
                }
                $this->delete($Key);
                $this->Time += (microtime(true) - $StartTime) * 1000;
                return false;
            } elseif ($_GET['clearcache'] == $Key) {
                $this->delete($Key);
                $this->Time += (microtime(true) - $StartTime) * 1000;
                return false;
            } elseif (str_ends_with((string)$_GET['clearcache'], '*')) {
                $Prefix = substr((string)$_GET['clearcache'], 0, -1);
                if ($Prefix === '' || $Prefix === substr($Key, 0, strlen($Prefix))) {
                    $this->delete($Key);
                    $this->Time += (microtime(true) - $StartTime) * 1000;
                    return false;
                }
            }
            $this->ClearedKeys[$Key] = true;
        }

        // For cases like the forums, if a key is already loaded, grab the existing pointer
        if (isset($this->CacheHits[$Key]) && !$NoCache) {
            $this->Time += (microtime(true) - $StartTime) * 1000;
            return $this->CacheHits[$Key];
        }

        $Return = $this->get($Key);
        if ($Return !== false) {
            $this->CacheHits[$Key] = $NoCache ? null : $Return;
        }
        $this->Time += (microtime(true) - $StartTime) * 1000;
        return $Return;
    }

    /**
     * Wrapper for Memcached::delete
     */
    public function delete_value(string $Key): void
    {
        $StartTime = microtime(true);
        if (empty($Key)) {
            trigger_error('Cache deletion failed for empty key');
        }
        $this->delete($Key);
        unset($this->CacheHits[$Key]);
        $this->Time += (microtime(true) - $StartTime) * 1000;
    }

    public function increment_value(string $Key, int $Value = 1): void
    {
        $StartTime = microtime(true);
        $NewVal = $this->increment($Key, $Value);
        if (isset($this->CacheHits[$Key])) {
            $this->CacheHits[$Key] = $NewVal;
        }
        $this->Time += (microtime(true) - $StartTime) * 1000;
    }

    public function decrement_value(string $Key, int $Value = 1): void
    {
        $StartTime = microtime(true);
        $NewVal = $this->decrement($Key, $Value);
        if (isset($this->CacheHits[$Key])) {
            $this->CacheHits[$Key] = $NewVal;
        }
        $this->Time += (microtime(true) - $StartTime) * 1000;
    }

    //---------- Memcached wrapper methods ----------//

    public function get(string $key): mixed
    {
        return $this->memcached->get($key);
    }

    public function set(string $key, mixed $value, int $expiration = 0): bool
    {
        return $this->memcached->set($key, $value, $expiration);
    }

    public function add(string $key, mixed $value, int $expiration = 0): bool
    {
        return $this->memcached->add($key, $value, $expiration);
    }

    public function replace(string $key, mixed $value, int $expiration = 0): bool
    {
        return $this->memcached->replace($key, $value, $expiration);
    }

    public function delete(string $key): bool
    {
        return $this->memcached->delete($key);
    }

    public function increment(string $key, int $offset = 1): int|false
    {
        return $this->memcached->increment($key, $offset);
    }

    public function decrement(string $key, int $offset = 1): int|false
    {
        return $this->memcached->decrement($key, $offset);
    }

    //---------- memcachedb functions ----------//

    public function begin_transaction(string $Key): bool
    {
        $Value = $this->get($Key);
        if (!is_array($Value)) {
            $this->InTransaction = false;
            $this->MemcacheDBArray = [];
            $this->MemcacheDBKey = '';
            return false;
        }
        $this->MemcacheDBArray = $Value;
        $this->MemcacheDBKey = $Key;
        $this->InTransaction = true;
        return true;
    }

    public function cancel_transaction(): void
    {
        $this->InTransaction = false;
        $this->MemcacheDBArray = [];
        $this->MemcacheDBKey = '';
    }

    public function commit_transaction(int $Time = 2592000): bool
    {
        if (!$this->InTransaction) {
            return false;
        }
        $this->cache_value($this->MemcacheDBKey, $this->MemcacheDBArray, $Time);
        $this->InTransaction = false;
        return true;
    }

    /**
     * Updates multiple rows in an array
     * @param array<int|string>|int|string $Rows
     * @param mixed $Values
     */
    public function update_transaction(array|int|string $Rows, mixed $Values): bool
    {
        if (!$this->InTransaction) {
            return false;
        }
        $Array = $this->MemcacheDBArray;
        if (is_array($Rows)) {
            $i = 0;
            $Keys = $Rows[0];
            $Property = $Rows[1];
            foreach ($Keys as $Row) {
                $Array[$Row][$Property] = $Values[$i];
                $i++;
            }
        } else {
            $Array[$Rows] = $Values;
        }
        $this->MemcacheDBArray = $Array;
        return true;
    }

    /**
     * Updates multiple values in a single row in an array
     * $Values must be an associative array with key:value pairs like in the array we're updating
     *
     * @param int|string|false $Row
     * @param array<string, mixed> $Values
     */
    public function update_row(int|string|false $Row, array $Values): bool
    {
        if (!$this->InTransaction) {
            return false;
        }
        if ($Row === false) {
            $UpdateArray = $this->MemcacheDBArray;
        } else {
            $UpdateArray = $this->MemcacheDBArray[$Row];
        }
        foreach ($Values as $Key => $Value) {
            if (!array_key_exists($Key, $UpdateArray)) {
                trigger_error('Bad transaction key (' . $Key . ') for cache ' . $this->MemcacheDBKey);
            }
            if ($Value === '+1') {
                if (!is_number($UpdateArray[$Key])) {
                    trigger_error('Tried to increment non-number (' . $Key . ') for cache ' . $this->MemcacheDBKey);
                }
                ++$UpdateArray[$Key];
            } elseif ($Value === '-1') {
                if (!is_number($UpdateArray[$Key])) {
                    trigger_error('Tried to decrement non-number (' . $Key . ') for cache ' . $this->MemcacheDBKey);
                }
                --$UpdateArray[$Key];
            } else {
                $UpdateArray[$Key] = $Value;
            }
        }
        if ($Row === false) {
            $this->MemcacheDBArray = $UpdateArray;
        } else {
            $this->MemcacheDBArray[$Row] = $UpdateArray;
        }
        return true;
    }

    /**
     * Increments multiple values in a single row in an array
     * $Values must be an associative array with key:value pairs like in the array we're updating
     *
     * @param int|string|false $Row
     * @param array<string, int> $Values
     */
    public function increment_row(int|string|false $Row, array $Values): bool
    {
        if (!$this->InTransaction) {
            return false;
        }
        if ($Row === false) {
            $UpdateArray = $this->MemcacheDBArray;
        } else {
            $UpdateArray = $this->MemcacheDBArray[$Row];
        }
        foreach ($Values as $Key => $Value) {
            if (!array_key_exists($Key, $UpdateArray)) {
                trigger_error("Bad transaction key ($Key) for cache " . $this->MemcacheDBKey);
            }
            if (!is_number($Value)) {
                trigger_error("Tried to increment with non-number ($Key) for cache " . $this->MemcacheDBKey);
            }
            $UpdateArray[$Key] += $Value;
        }
        if ($Row === false) {
            $this->MemcacheDBArray = $UpdateArray;
        } else {
            $this->MemcacheDBArray[$Row] = $UpdateArray;
        }
        return true;
    }

    /**
     * Insert a value at the beginning of the array
     */
    public function insert_front(string $Key, mixed $Value): bool
    {
        if (!$this->InTransaction) {
            return false;
        }
        if ($Key === '') {
            array_unshift($this->MemcacheDBArray, $Value);
        } else {
            $this->MemcacheDBArray = [$Key => $Value] + $this->MemcacheDBArray;
        }
        return true;
    }

    /**
     * Insert a value at the end of the array
     */
    public function insert_back(string $Key, mixed $Value): bool
    {
        if (!$this->InTransaction) {
            return false;
        }
        if ($Key === '') {
            $this->MemcacheDBArray[] = $Value;
        } else {
            $this->MemcacheDBArray = $this->MemcacheDBArray + [$Key => $Value];
        }
        return true;
    }

    public function insert(string $Key, mixed $Value): bool
    {
        if (!$this->InTransaction) {
            return false;
        }
        if ($Key === '') {
            $this->MemcacheDBArray[] = $Value;
        } else {
            $this->MemcacheDBArray[$Key] = $Value;
        }
        return true;
    }

    public function delete_row(int|string $Row): bool
    {
        if (!$this->InTransaction) {
            return false;
        }
        if (!isset($this->MemcacheDBArray[$Row])) {
            trigger_error("Tried to delete non-existent row ($Row) for cache " . $this->MemcacheDBKey);
        }
        unset($this->MemcacheDBArray[$Row]);
        return true;
    }

    /**
     * @param array<int|string>|int|string $Rows
     */
    public function update(string $Key, array|int|string $Rows, mixed $Values, int $Time = 2592000): void
    {
        if (!$this->InTransaction) {
            $this->begin_transaction($Key);
            $this->update_transaction($Rows, $Values);
            $this->commit_transaction($Time);
        } else {
            $this->update_transaction($Rows, $Values);
        }
    }

    /**
     * Tries to set a lock. Expiry time is one hour to avoid indefinite locks
     *
     * @param string $LockName name of the lock
     * @return bool true if lock was acquired
     */
    public function get_query_lock(string $LockName): bool
    {
        return $this->add_value('query_lock_' . $LockName, 1, 3600);
    }

    /**
     * Remove lock
     *
     * @param string $LockName name of the lock
     */
    public function clear_query_lock(string $LockName): void
    {
        $this->delete_value('query_lock_' . $LockName);
    }

    /**
     * Get cache server status
     *
     * @return array<string, int> (host => status code, ...)
     */
    public function server_status(): array
    {
        $Status = [];
        $stats = $this->memcached->getStats();

        foreach ($this->Servers as $Server) {
            $host = $Server['host'];
            $port = $Server['port'];

            // Handle Unix socket
            if (str_starts_with($host, 'unix://')) {
                $key = substr($host, 7) . ':0';
            } else {
                $key = "$host:$port";
            }

            $Status["$host:$port"] = isset($stats[$key]) ? 1 : 0;
        }
        return $Status;
    }

    /**
     * Get the underlying Memcached instance for advanced operations
     */
    public function getMemcached(): Memcached
    {
        return $this->memcached;
    }
}
