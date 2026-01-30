<?php

declare(strict_types=1);

namespace Gazelle\Tests\Unit\Domain\Torrent\ValueObjects;

use Gazelle\Domain\Torrent\ValueObjects\FileSize;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FileSize::class)]
final class FileSizeTest extends TestCase
{
    // ========================================
    // Creation Tests
    // ========================================

    #[Test]
    public function it_creates_file_size_from_bytes(): void
    {
        $size = FileSize::fromBytes(1024);

        $this->assertInstanceOf(FileSize::class, $size);
        $this->assertSame(1024, $size->bytes());
    }

    #[Test]
    public function it_creates_file_size_from_kilobytes(): void
    {
        $size = FileSize::fromKilobytes(1.5);

        $this->assertSame(1536, $size->bytes()); // 1.5 * 1024
    }

    #[Test]
    public function it_creates_file_size_from_megabytes(): void
    {
        $size = FileSize::fromMegabytes(1);

        $this->assertSame(1048576, $size->bytes()); // 1024 * 1024
    }

    #[Test]
    public function it_creates_file_size_from_gigabytes(): void
    {
        $size = FileSize::fromGigabytes(1);

        $this->assertSame(1073741824, $size->bytes()); // 1024^3
    }

    #[Test]
    public function it_creates_zero_size(): void
    {
        $size = FileSize::zero();

        $this->assertSame(0, $size->bytes());
    }

    // ========================================
    // Validation Tests
    // ========================================

    #[Test]
    public function it_rejects_negative_bytes(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('cannot be negative');

        FileSize::fromBytes(-1);
    }

    #[Test]
    public function it_accepts_zero_bytes(): void
    {
        $size = FileSize::fromBytes(0);

        $this->assertSame(0, $size->bytes());
    }

    #[Test]
    public function it_accepts_large_sizes(): void
    {
        // 1 PB
        $size = FileSize::fromBytes(1125899906842624);

        $this->assertSame(1125899906842624, $size->bytes());
    }

    // ========================================
    // Conversion Tests
    // ========================================

    #[Test]
    public function it_converts_to_kilobytes(): void
    {
        $size = FileSize::fromBytes(2048);

        $this->assertEqualsWithDelta(2.0, $size->kilobytes(), 0.001);
    }

    #[Test]
    public function it_converts_to_megabytes(): void
    {
        $size = FileSize::fromBytes(5242880); // 5 MB

        $this->assertEqualsWithDelta(5.0, $size->megabytes(), 0.001);
    }

    #[Test]
    public function it_converts_to_gigabytes(): void
    {
        $size = FileSize::fromBytes(2147483648); // 2 GB

        $this->assertEqualsWithDelta(2.0, $size->gigabytes(), 0.001);
    }

    #[Test]
    public function it_handles_fractional_conversions(): void
    {
        $size = FileSize::fromBytes(1536); // 1.5 KB

        $this->assertEqualsWithDelta(1.5, $size->kilobytes(), 0.001);
    }

    // ========================================
    // Formatting Tests
    // ========================================

    #[Test]
    public function it_formats_bytes(): void
    {
        $size = FileSize::fromBytes(512);

        $this->assertSame('512 B', $size->format());
    }

    #[Test]
    public function it_formats_kilobytes(): void
    {
        $size = FileSize::fromBytes(1536);

        $this->assertSame('1.5 KB', $size->format());
    }

    #[Test]
    public function it_formats_megabytes(): void
    {
        $size = FileSize::fromMegabytes(25.5);

        $this->assertSame('25.5 MB', $size->format());
    }

    #[Test]
    public function it_formats_gigabytes(): void
    {
        $size = FileSize::fromGigabytes(3.75);

        $this->assertSame('3.75 GB', $size->format());
    }

    #[Test]
    public function it_formats_terabytes(): void
    {
        $size = FileSize::fromBytes(2199023255552); // 2 TB

        $this->assertSame('2 TB', $size->format());
    }

    #[Test]
    public function it_formats_zero(): void
    {
        $size = FileSize::zero();

        $this->assertSame('0 B', $size->format());
    }

    #[Test]
    public function it_formats_with_custom_precision(): void
    {
        $size = FileSize::fromBytes(1234567);

        $this->assertSame('1.18 MB', $size->format(2));
        $this->assertSame('1.177 MB', $size->format(3));
        $this->assertSame('1.2 MB', $size->format(1));
    }

    #[Test]
    #[DataProvider('formatProvider')]
    public function it_formats_various_sizes_correctly(int $bytes, string $expected): void
    {
        $size = FileSize::fromBytes($bytes);

        $this->assertSame($expected, $size->format());
    }

    public static function formatProvider(): array
    {
        return [
            'zero' => [0, '0 B'],
            '1 byte' => [1, '1 B'],
            '1023 bytes' => [1023, '1023 B'],
            '1 KB' => [1024, '1 KB'],
            '1 MB' => [1048576, '1 MB'],
            '1 GB' => [1073741824, '1 GB'],
            '1 TB' => [1099511627776, '1 TB'],
        ];
    }

    // ========================================
    // Arithmetic Tests
    // ========================================

    #[Test]
    public function it_adds_file_sizes(): void
    {
        $size1 = FileSize::fromBytes(1000);
        $size2 = FileSize::fromBytes(500);

        $result = $size1->add($size2);

        $this->assertSame(1500, $result->bytes());
    }

    #[Test]
    public function it_adds_preserves_immutability(): void
    {
        $size1 = FileSize::fromBytes(1000);
        $size2 = FileSize::fromBytes(500);

        $result = $size1->add($size2);

        $this->assertSame(1000, $size1->bytes()); // Original unchanged
        $this->assertSame(500, $size2->bytes()); // Original unchanged
        $this->assertSame(1500, $result->bytes()); // New instance
    }

    #[Test]
    public function it_subtracts_file_sizes(): void
    {
        $size1 = FileSize::fromBytes(1000);
        $size2 = FileSize::fromBytes(300);

        $result = $size1->subtract($size2);

        $this->assertSame(700, $result->bytes());
    }

    #[Test]
    public function it_subtracts_clamps_to_zero(): void
    {
        $size1 = FileSize::fromBytes(100);
        $size2 = FileSize::fromBytes(500);

        $result = $size1->subtract($size2);

        // Should not go negative
        $this->assertSame(0, $result->bytes());
    }

    #[Test]
    public function it_subtracts_preserves_immutability(): void
    {
        $size1 = FileSize::fromBytes(1000);
        $size2 = FileSize::fromBytes(300);

        $result = $size1->subtract($size2);

        $this->assertSame(1000, $size1->bytes()); // Original unchanged
    }

    // ========================================
    // Comparison Tests
    // ========================================

    #[Test]
    public function it_compares_greater_than(): void
    {
        $larger = FileSize::fromBytes(1000);
        $smaller = FileSize::fromBytes(500);

        $this->assertTrue($larger->isGreaterThan($smaller));
        $this->assertFalse($smaller->isGreaterThan($larger));
    }

    #[Test]
    public function it_compares_less_than(): void
    {
        $larger = FileSize::fromBytes(1000);
        $smaller = FileSize::fromBytes(500);

        $this->assertTrue($smaller->isLessThan($larger));
        $this->assertFalse($larger->isLessThan($smaller));
    }

    #[Test]
    public function it_compares_equal_sizes(): void
    {
        $size1 = FileSize::fromBytes(1000);
        $size2 = FileSize::fromBytes(1000);

        $this->assertFalse($size1->isGreaterThan($size2));
        $this->assertFalse($size1->isLessThan($size2));
    }

    // ========================================
    // Equality Tests
    // ========================================

    #[Test]
    public function it_equals_same_size(): void
    {
        $size1 = FileSize::fromBytes(1024);
        $size2 = FileSize::fromBytes(1024);

        $this->assertTrue($size1->equals($size2));
    }

    #[Test]
    public function it_equals_same_size_different_creation(): void
    {
        $size1 = FileSize::fromBytes(1048576);
        $size2 = FileSize::fromMegabytes(1);

        $this->assertTrue($size1->equals($size2));
    }

    #[Test]
    public function it_not_equals_different_size(): void
    {
        $size1 = FileSize::fromBytes(1024);
        $size2 = FileSize::fromBytes(2048);

        $this->assertFalse($size1->equals($size2));
    }

    // ========================================
    // String Conversion Tests
    // ========================================

    #[Test]
    public function it_converts_to_string(): void
    {
        $size = FileSize::fromMegabytes(5.5);

        $this->assertSame('5.5 MB', (string) $size);
    }

    #[Test]
    public function it_implements_stringable(): void
    {
        $size = FileSize::fromBytes(1024);

        $this->assertInstanceOf(\Stringable::class, $size);
    }

    // ========================================
    // Immutability Tests
    // ========================================

    #[Test]
    public function it_is_immutable(): void
    {
        $size = FileSize::fromBytes(1024);

        $reflection = new \ReflectionClass($size);
        $this->assertTrue($reflection->isReadOnly());
    }

    // ========================================
    // Edge Cases
    // ========================================

    #[Test]
    public function it_handles_exactly_one_unit_boundary(): void
    {
        // Exactly 1 KB
        $kb = FileSize::fromBytes(1024);
        $this->assertSame('1 KB', $kb->format());

        // Exactly 1 MB
        $mb = FileSize::fromBytes(1048576);
        $this->assertSame('1 MB', $mb->format());
    }

    #[Test]
    public function it_handles_just_under_unit_boundary(): void
    {
        // 1023 bytes (just under 1 KB)
        $size = FileSize::fromBytes(1023);
        $this->assertSame('1023 B', $size->format());
    }

    #[Test]
    public function it_handles_maximum_int(): void
    {
        $size = FileSize::fromBytes(PHP_INT_MAX);

        $this->assertSame(PHP_INT_MAX, $size->bytes());
    }
}
