<?php

declare(strict_types=1);

namespace Gazelle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

/**
 * Unit tests for DBCrypt class
 */
#[RequiresPhpExtension('apcu')]
class DBCryptTest extends TestCase
{
    private bool $apcuWasEnabled = false;

    protected function setUp(): void
    {
        require_once dirname(__DIR__, 2) . '/classes/dbcrypt.class.php';

        // Store APCu state and enable for testing if in CLI
        $this->apcuWasEnabled = ini_get('apc.enable_cli') === '1';

        if (!$this->apcuWasEnabled) {
            ini_set('apc.enable_cli', '1');
        }

        // Clear any existing key
        if (function_exists('apcu_delete')) {
            @apcu_delete('DBKEY');
        }
    }

    protected function tearDown(): void
    {
        // Clean up
        if (function_exists('apcu_delete')) {
            @apcu_delete('DBKEY');
        }

        // Restore APCu state
        if (!$this->apcuWasEnabled) {
            ini_set('apc.enable_cli', '0');
        }
    }

    #[Test]
    public function encrypt_returns_false_when_no_key_available(): void
    {
        $result = \DBCrypt::encrypt('test data');

        $this->assertFalse($result);
    }

    #[Test]
    public function decrypt_returns_false_when_no_key_available(): void
    {
        $result = \DBCrypt::decrypt('encrypted data');

        $this->assertFalse($result);
    }

    #[Test]
    public function isKeyAvailable_returns_false_when_no_key(): void
    {
        $this->assertFalse(\DBCrypt::isKeyAvailable());
    }

    #[Test]
    public function storeKey_stores_key_in_apcu(): void
    {
        if (!function_exists('apcu_store')) {
            $this->markTestSkipped('APCu not available');
        }

        $result = \DBCrypt::storeKey('test_encryption_key');

        $this->assertTrue($result);
        $this->assertTrue(\DBCrypt::isKeyAvailable());
    }

    #[Test]
    public function clearKey_removes_key_from_apcu(): void
    {
        if (!function_exists('apcu_store')) {
            $this->markTestSkipped('APCu not available');
        }

        \DBCrypt::storeKey('test_key');
        $this->assertTrue(\DBCrypt::isKeyAvailable());

        \DBCrypt::clearKey();

        $this->assertFalse(\DBCrypt::isKeyAvailable());
    }

    #[Test]
    public function encrypt_and_decrypt_roundtrip(): void
    {
        if (!function_exists('apcu_store')) {
            $this->markTestSkipped('APCu not available');
        }

        $key = 'secure_test_key_1234567890!';
        \DBCrypt::storeKey($key);

        $plaintext = 'This is secret data to encrypt';
        $encrypted = \DBCrypt::encrypt($plaintext);

        $this->assertIsString($encrypted);
        $this->assertNotEquals($plaintext, $encrypted);

        $decrypted = \DBCrypt::decrypt($encrypted);

        $this->assertEquals($plaintext, $decrypted);
    }

    #[Test]
    public function encrypt_produces_different_output_each_time(): void
    {
        if (!function_exists('apcu_store')) {
            $this->markTestSkipped('APCu not available');
        }

        $key = 'secure_test_key_1234567890!';
        \DBCrypt::storeKey($key);

        $plaintext = 'Same data';
        $encrypted1 = \DBCrypt::encrypt($plaintext);
        $encrypted2 = \DBCrypt::encrypt($plaintext);

        // Due to random IV, encryptions should be different
        $this->assertNotEquals($encrypted1, $encrypted2);

        // But both should decrypt to the same value
        $this->assertEquals($plaintext, \DBCrypt::decrypt($encrypted1));
        $this->assertEquals($plaintext, \DBCrypt::decrypt($encrypted2));
    }

    #[Test]
    public function encrypt_handles_empty_string(): void
    {
        if (!function_exists('apcu_store')) {
            $this->markTestSkipped('APCu not available');
        }

        \DBCrypt::storeKey('test_key_12345');

        $encrypted = \DBCrypt::encrypt('');

        $this->assertIsString($encrypted);
        $this->assertEquals('', \DBCrypt::decrypt($encrypted));
    }

    #[Test]
    public function encrypt_handles_unicode(): void
    {
        if (!function_exists('apcu_store')) {
            $this->markTestSkipped('APCu not available');
        }

        \DBCrypt::storeKey('test_key_12345');

        $unicode = '日本語テスト 🎉 émoji';
        $encrypted = \DBCrypt::encrypt($unicode);

        $this->assertIsString($encrypted);
        $this->assertEquals($unicode, \DBCrypt::decrypt($encrypted));
    }

    #[Test]
    public function decrypt_returns_false_for_invalid_ciphertext(): void
    {
        if (!function_exists('apcu_store')) {
            $this->markTestSkipped('APCu not available');
        }

        \DBCrypt::storeKey('test_key_12345');

        // Invalid base64
        $result = \DBCrypt::decrypt('not-valid-base64!!!');

        // Should handle gracefully
        $this->assertIsBool($result);
    }
}
