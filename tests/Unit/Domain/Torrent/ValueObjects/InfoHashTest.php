<?php

declare(strict_types=1);

namespace Gazelle\Tests\Unit\Domain\Torrent\ValueObjects;

use Gazelle\Domain\Torrent\ValueObjects\InfoHash;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(InfoHash::class)]
final class InfoHashTest extends TestCase
{
    private const VALID_HEX = 'a94a8fe5ccb19ba61c4c0873d391e987982fbbd3';
    private const VALID_BINARY = "\xa9\x4a\x8f\xe5\xcc\xb1\x9b\xa6\x1c\x4c\x08\x73\xd3\x91\xe9\x87\x98\x2f\xbb\xd3";

    // ========================================
    // Creation from Hex Tests
    // ========================================

    #[Test]
    public function it_creates_info_hash_from_valid_hex(): void
    {
        $hash = InfoHash::fromHex(self::VALID_HEX);

        $this->assertInstanceOf(InfoHash::class, $hash);
        $this->assertSame(self::VALID_HEX, $hash->hex());
    }

    #[Test]
    public function it_normalizes_hex_to_lowercase(): void
    {
        $hash = InfoHash::fromHex('A94A8FE5CCB19BA61C4C0873D391E987982FBBD3');

        $this->assertSame(self::VALID_HEX, $hash->hex());
    }

    #[Test]
    public function it_trims_whitespace_from_hex(): void
    {
        $hash = InfoHash::fromHex('  ' . self::VALID_HEX . '  ');

        $this->assertSame(self::VALID_HEX, $hash->hex());
    }

    #[Test]
    #[DataProvider('validHexProvider')]
    public function it_accepts_valid_hex_strings(string $hex): void
    {
        $hash = InfoHash::fromHex($hex);

        $this->assertInstanceOf(InfoHash::class, $hash);
    }

    public static function validHexProvider(): array
    {
        return [
            'lowercase' => ['a94a8fe5ccb19ba61c4c0873d391e987982fbbd3'],
            'uppercase' => ['A94A8FE5CCB19BA61C4C0873D391E987982FBBD3'],
            'mixed case' => ['A94a8Fe5cCb19Ba61c4c0873D391e987982FbbD3'],
            'all zeros' => ['0000000000000000000000000000000000000000'],
            'all fs' => ['ffffffffffffffffffffffffffffffffffffffff'],
        ];
    }

    // ========================================
    // Creation from Binary Tests
    // ========================================

    #[Test]
    public function it_creates_info_hash_from_binary(): void
    {
        $hash = InfoHash::fromBinary(self::VALID_BINARY);

        $this->assertInstanceOf(InfoHash::class, $hash);
        $this->assertSame(self::VALID_HEX, $hash->hex());
    }

    #[Test]
    public function it_converts_binary_to_hex_correctly(): void
    {
        $binary = hex2bin(self::VALID_HEX);
        $hash = InfoHash::fromBinary($binary);

        $this->assertSame(self::VALID_HEX, $hash->hex());
    }

    // ========================================
    // Validation Tests - Hex
    // ========================================

    #[Test]
    public function it_rejects_hex_too_short(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('exactly 40 hex characters');

        InfoHash::fromHex('a94a8fe5ccb19ba61c4c0873d391e987982fbbd'); // 39 chars
    }

    #[Test]
    public function it_rejects_hex_too_long(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('exactly 40 hex characters');

        InfoHash::fromHex('a94a8fe5ccb19ba61c4c0873d391e987982fbbd3a'); // 41 chars
    }

    #[Test]
    public function it_rejects_empty_hex(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        InfoHash::fromHex('');
    }

    #[Test]
    #[DataProvider('invalidHexProvider')]
    public function it_rejects_invalid_hex_characters(string $invalidHex): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('must be valid hex');

        InfoHash::fromHex($invalidHex);
    }

    public static function invalidHexProvider(): array
    {
        return [
            'with g' => ['g94a8fe5ccb19ba61c4c0873d391e987982fbbd3'],
            'with space' => ['a94a8fe5ccb19ba6 c4c0873d391e987982fbbd3'],
            'with special' => ['a94a8fe5ccb19ba6!c4c0873d391e987982fbbd3'],
            'with hyphen' => ['a94a8fe5-ccb19ba6-1c4c0873-d391e987-982fbbd3'],
        ];
    }

    // ========================================
    // Validation Tests - Binary
    // ========================================

    #[Test]
    public function it_rejects_binary_too_short(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('must be 20 bytes');

        InfoHash::fromBinary(str_repeat("\x00", 19));
    }

    #[Test]
    public function it_rejects_binary_too_long(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('must be 20 bytes');

        InfoHash::fromBinary(str_repeat("\x00", 21));
    }

    #[Test]
    public function it_rejects_empty_binary(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        InfoHash::fromBinary('');
    }

    // ========================================
    // Conversion Tests
    // ========================================

    #[Test]
    public function it_returns_hex_representation(): void
    {
        $hash = InfoHash::fromHex(self::VALID_HEX);

        $this->assertSame(self::VALID_HEX, $hash->hex());
    }

    #[Test]
    public function it_returns_binary_representation(): void
    {
        $hash = InfoHash::fromHex(self::VALID_HEX);

        $this->assertSame(self::VALID_BINARY, $hash->binary());
    }

    #[Test]
    public function it_roundtrips_hex_to_binary_to_hex(): void
    {
        $original = InfoHash::fromHex(self::VALID_HEX);
        $fromBinary = InfoHash::fromBinary($original->binary());

        $this->assertSame($original->hex(), $fromBinary->hex());
    }

    #[Test]
    public function it_roundtrips_binary_to_hex_to_binary(): void
    {
        $original = InfoHash::fromBinary(self::VALID_BINARY);
        $fromHex = InfoHash::fromHex($original->hex());

        $this->assertSame($original->binary(), $fromHex->binary());
    }

    // ========================================
    // Equality Tests
    // ========================================

    #[Test]
    public function it_equals_same_hash(): void
    {
        $hash1 = InfoHash::fromHex(self::VALID_HEX);
        $hash2 = InfoHash::fromHex(self::VALID_HEX);

        $this->assertTrue($hash1->equals($hash2));
    }

    #[Test]
    public function it_equals_same_hash_different_case_input(): void
    {
        $hash1 = InfoHash::fromHex(self::VALID_HEX);
        $hash2 = InfoHash::fromHex(strtoupper(self::VALID_HEX));

        $this->assertTrue($hash1->equals($hash2));
    }

    #[Test]
    public function it_equals_hash_from_binary(): void
    {
        $hash1 = InfoHash::fromHex(self::VALID_HEX);
        $hash2 = InfoHash::fromBinary(self::VALID_BINARY);

        $this->assertTrue($hash1->equals($hash2));
    }

    #[Test]
    public function it_not_equals_different_hash(): void
    {
        $hash1 = InfoHash::fromHex(self::VALID_HEX);
        $hash2 = InfoHash::fromHex('b94a8fe5ccb19ba61c4c0873d391e987982fbbd3');

        $this->assertFalse($hash1->equals($hash2));
    }

    // ========================================
    // String Conversion Tests
    // ========================================

    #[Test]
    public function it_converts_to_uppercase_string(): void
    {
        $hash = InfoHash::fromHex(self::VALID_HEX);

        // __toString returns uppercase for display
        $this->assertSame(strtoupper(self::VALID_HEX), (string) $hash);
    }

    #[Test]
    public function it_implements_stringable(): void
    {
        $hash = InfoHash::fromHex(self::VALID_HEX);

        $this->assertInstanceOf(\Stringable::class, $hash);
    }

    // ========================================
    // Immutability Tests
    // ========================================

    #[Test]
    public function it_is_immutable(): void
    {
        $hash = InfoHash::fromHex(self::VALID_HEX);

        $reflection = new \ReflectionClass($hash);
        $this->assertTrue($reflection->isReadOnly());
    }

    // ========================================
    // Edge Cases
    // ========================================

    #[Test]
    public function it_handles_all_zero_hash(): void
    {
        $hash = InfoHash::fromHex(str_repeat('0', 40));

        $this->assertSame(str_repeat('0', 40), $hash->hex());
        $this->assertSame(str_repeat("\x00", 20), $hash->binary());
    }

    #[Test]
    public function it_handles_all_f_hash(): void
    {
        $hash = InfoHash::fromHex(str_repeat('f', 40));

        $this->assertSame(str_repeat('f', 40), $hash->hex());
        $this->assertSame(str_repeat("\xff", 20), $hash->binary());
    }
}
