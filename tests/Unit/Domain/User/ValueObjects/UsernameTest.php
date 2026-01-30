<?php

declare(strict_types=1);

namespace Gazelle\Tests\Unit\Domain\User\ValueObjects;

use Gazelle\Domain\User\ValueObjects\Username;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Username::class)]
final class UsernameTest extends TestCase
{
    // ========================================
    // Creation Tests
    // ========================================

    #[Test]
    public function it_creates_username_from_valid_string(): void
    {
        $username = Username::fromString('ValidUser');

        $this->assertInstanceOf(Username::class, $username);
        $this->assertSame('ValidUser', $username->value());
    }

    #[Test]
    public function it_trims_whitespace(): void
    {
        $username = Username::fromString('  ValidUser  ');

        $this->assertSame('ValidUser', $username->value());
    }

    #[Test]
    #[DataProvider('validUsernameProvider')]
    public function it_accepts_valid_usernames(string $validUsername): void
    {
        $username = Username::fromString($validUsername);

        $this->assertInstanceOf(Username::class, $username);
    }

    public static function validUsernameProvider(): array
    {
        return [
            'lowercase' => ['validuser'],
            'uppercase' => ['VALIDUSER'],
            'mixed case' => ['ValidUser'],
            'with numbers' => ['User123'],
            'with underscore' => ['valid_user'],
            'minimum length' => ['abc'],
            'maximum length' => ['abcdefghijklmnopqrst'], // 20 chars
            'numbers only' => ['123456'],
            'underscores only' => ['___'],
            'mixed all' => ['User_123_Test'],
        ];
    }

    // ========================================
    // Length Validation Tests
    // ========================================

    #[Test]
    public function it_rejects_username_too_short(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('at least 3 characters');

        Username::fromString('ab');
    }

    #[Test]
    public function it_rejects_single_character(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Username::fromString('a');
    }

    #[Test]
    public function it_rejects_empty_username(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Username::fromString('');
    }

    #[Test]
    public function it_rejects_username_too_long(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('cannot exceed 20 characters');

        Username::fromString('abcdefghijklmnopqrstu'); // 21 chars
    }

    #[Test]
    public function it_accepts_exactly_minimum_length(): void
    {
        $username = Username::fromString('abc'); // 3 chars

        $this->assertSame('abc', $username->value());
    }

    #[Test]
    public function it_accepts_exactly_maximum_length(): void
    {
        $username = Username::fromString('abcdefghijklmnopqrst'); // 20 chars

        $this->assertSame('abcdefghijklmnopqrst', $username->value());
    }

    // ========================================
    // Character Validation Tests
    // ========================================

    #[Test]
    #[DataProvider('invalidCharacterProvider')]
    public function it_rejects_invalid_characters(string $invalidUsername): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('only contain letters, numbers, and underscores');

        Username::fromString($invalidUsername);
    }

    public static function invalidCharacterProvider(): array
    {
        return [
            'with space' => ['valid user'],
            'with hyphen' => ['valid-user'],
            'with dot' => ['valid.user'],
            'with at sign' => ['valid@user'],
            'with special chars' => ['valid!user'],
            'with unicode' => ['valïduser'],
            'with emoji' => ['user🎉'],
            'with plus' => ['user+tag'],
            'with hash' => ['user#1'],
        ];
    }

    // ========================================
    // Equality Tests
    // ========================================

    #[Test]
    public function it_equals_same_username(): void
    {
        $username1 = Username::fromString('ValidUser');
        $username2 = Username::fromString('ValidUser');

        $this->assertTrue($username1->equals($username2));
    }

    #[Test]
    public function it_equals_case_insensitive(): void
    {
        $username1 = Username::fromString('ValidUser');
        $username2 = Username::fromString('validuser');

        // Username equality should be case-insensitive
        $this->assertTrue($username1->equals($username2));
    }

    #[Test]
    public function it_equals_uppercase_variant(): void
    {
        $username1 = Username::fromString('validuser');
        $username2 = Username::fromString('VALIDUSER');

        $this->assertTrue($username1->equals($username2));
    }

    #[Test]
    public function it_not_equals_different_username(): void
    {
        $username1 = Username::fromString('User1');
        $username2 = Username::fromString('User2');

        $this->assertFalse($username1->equals($username2));
    }

    // ========================================
    // String Conversion Tests
    // ========================================

    #[Test]
    public function it_converts_to_string(): void
    {
        $username = Username::fromString('ValidUser');

        $this->assertSame('ValidUser', (string) $username);
    }

    #[Test]
    public function it_preserves_original_case_in_value(): void
    {
        $username = Username::fromString('ValidUser');

        // value() should preserve original case
        $this->assertSame('ValidUser', $username->value());
    }

    #[Test]
    public function it_implements_stringable(): void
    {
        $username = Username::fromString('ValidUser');

        $this->assertInstanceOf(\Stringable::class, $username);
    }

    // ========================================
    // Immutability Tests
    // ========================================

    #[Test]
    public function it_is_immutable(): void
    {
        $username = Username::fromString('ValidUser');

        $reflection = new \ReflectionClass($username);
        $this->assertTrue($reflection->isReadOnly());
    }

    // ========================================
    // Edge Cases
    // ========================================

    #[Test]
    public function it_handles_whitespace_only_input(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Username::fromString('   ');
    }

    #[Test]
    public function it_counts_length_after_trim(): void
    {
        // "  ab  " trimmed is "ab" which is 2 chars (too short)
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('at least 3 characters');

        Username::fromString('  ab  ');
    }
}
