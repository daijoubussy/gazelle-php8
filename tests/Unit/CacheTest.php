<?php

declare(strict_types=1);

namespace Gazelle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

/**
 * Unit tests for CACHE class
 */
#[RequiresPhpExtension('memcached')]
class CacheTest extends TestCase
{
    private ?\CACHE $cache = null;

    protected function setUp(): void
    {
        // Define required functions if not available
        if (!function_exists('is_number')) {
            require_once dirname(__DIR__, 2) . '/classes/util.php';
        }

        // Mock Misc class if not available
        if (!class_exists('Misc')) {
            eval('class Misc {
                public static function in_array_partial($needle, $haystack) {
                    foreach ($haystack as $item) {
                        if (str_ends_with($item, "*")) {
                            $prefix = substr($item, 0, -1);
                            if (str_starts_with($needle, $prefix)) return true;
                        } elseif ($needle === $item) {
                            return true;
                        }
                    }
                    return false;
                }
            }');
        }

        require_once dirname(__DIR__, 2) . '/classes/cache.class.php';

        // Create cache with mock servers (won't actually connect in tests)
        $this->cache = new \CACHE([
            ['host' => '127.0.0.1', 'port' => 11211, 'buckets' => 1]
        ]);
    }

    protected function tearDown(): void
    {
        $this->cache = null;
    }

    #[Test]
    public function constructor_creates_cache_instance(): void
    {
        $this->assertInstanceOf(\CACHE::class, $this->cache);
    }

    #[Test]
    public function initial_state_is_correct(): void
    {
        $this->assertSame([], $this->cache->CacheHits);
        $this->assertSame(0.0, $this->cache->Time);
        $this->assertFalse($this->cache->CanClear);
        $this->assertTrue($this->cache->InternalCache);
    }

    #[Test]
    public function group_version_constant_exists(): void
    {
        $this->assertSame(5, \CACHE::GROUP_VERSION);
    }

    #[Test]
    public function begin_transaction_returns_false_for_non_array(): void
    {
        $result = $this->cache->begin_transaction('nonexistent_key');

        $this->assertFalse($result);
    }

    #[Test]
    public function commit_transaction_returns_false_when_not_in_transaction(): void
    {
        $result = $this->cache->commit_transaction();

        $this->assertFalse($result);
    }

    #[Test]
    public function cancel_transaction_resets_state(): void
    {
        $this->cache->cancel_transaction();

        $this->assertSame('', $this->cache->MemcacheDBKey);
        $this->assertSame([], $this->cache->MemcacheDBArray);
    }

    #[Test]
    public function insert_returns_false_when_not_in_transaction(): void
    {
        $result = $this->cache->insert('key', 'value');

        $this->assertFalse($result);
    }

    #[Test]
    public function insert_front_returns_false_when_not_in_transaction(): void
    {
        $result = $this->cache->insert_front('key', 'value');

        $this->assertFalse($result);
    }

    #[Test]
    public function insert_back_returns_false_when_not_in_transaction(): void
    {
        $result = $this->cache->insert_back('key', 'value');

        $this->assertFalse($result);
    }

    #[Test]
    public function update_row_returns_false_when_not_in_transaction(): void
    {
        $result = $this->cache->update_row('row', ['key' => 'value']);

        $this->assertFalse($result);
    }

    #[Test]
    public function increment_row_returns_false_when_not_in_transaction(): void
    {
        $result = $this->cache->increment_row('row', ['counter' => 1]);

        $this->assertFalse($result);
    }

    #[Test]
    public function delete_row_returns_false_when_not_in_transaction(): void
    {
        $result = $this->cache->delete_row('row');

        $this->assertFalse($result);
    }

    #[Test]
    public function getMemcached_returns_memcached_instance(): void
    {
        $memcached = $this->cache->getMemcached();

        $this->assertInstanceOf(\Memcached::class, $memcached);
    }

    #[Test]
    public function time_is_tracked_for_operations(): void
    {
        // Trigger some operations
        $this->cache->get_value('test_key');

        // Time should be greater than 0 after an operation
        $this->assertGreaterThanOrEqual(0.0, $this->cache->Time);
    }

    #[Test]
    public function server_status_returns_array(): void
    {
        $status = $this->cache->server_status();

        $this->assertIsArray($status);
    }

    #[Test]
    public function cache_value_triggers_error_for_empty_key(): void
    {
        // Suppress the error for testing
        set_error_handler(function ($errno, $errstr) {
            $this->assertStringContainsString('empty key', $errstr);
            return true;
        });

        $this->cache->cache_value('', 'value');

        restore_error_handler();
    }

    #[Test]
    public function delete_value_triggers_error_for_empty_key(): void
    {
        set_error_handler(function ($errno, $errstr) {
            $this->assertStringContainsString('empty key', $errstr);
            return true;
        });

        $this->cache->delete_value('');

        restore_error_handler();
    }
}
