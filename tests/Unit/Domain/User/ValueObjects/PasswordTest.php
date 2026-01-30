<?php

declare(strict_types=1);

namespace Gazelle\Tests\Unit\Domain\User\ValueObjects;

use Gazelle\Domain\User\ValueObjects\Password;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Password::class)]
final class PasswordTest extends TestCase
{
    // ========================================
    // Creation Tests
    // ========================================

    #[Test]
    public function it_creates_password_from_valid_plaintext(): void
    {
        $password = Password::fromPlaintext('ValidPass123');

        $this->assertInstanceOf(Password::class, $password);
    }

    #[Test]
    public function it_creates_password_from_existing_hash(): void
    {
        $hash = password_hash('TestPassword123', PASSWORD_ARGON2ID);
        $password = Password::fromHash($hash);

        $this->assertInstanceOf(Password::class, $password);
        $this->assertSame($hash, $password->hash());
    }

    #[Test]
    public function it_hashes_password_not_stores_plaintext(): void
    {
        $plaintext = 'ValidPass123';
        $password = Password::fromPlaintext($plaintext);

        // Hash should not equal plaintext
        $this->assertNotSame($plaintext, $password->hash());

        // Hash should be a valid password hash
        $this->assertStringStartsWith('$argon2id$', $password->hash());
    }

    // ========================================
    // Validation Tests - Length
    // ========================================

    #[Test]
    public function it_rejects_password_too_short(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('at least 8 characters');

        Password::fromPlaintext('Ab1'); // 3 chars
    }

    #[Test]
    public function it_rejects_7_character_password(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Password::fromPlaintext('Abcde1!'); // 7 chars
    }

    #[Test]
    public function it_accepts_8_character_password(): void
    {
        $password = Password::fromPlaintext('Abcdef12'); // 8 chars

        $this->assertInstanceOf(Password::class, $password);
    }

    // ========================================
    // Validation Tests - Complexity
    // ========================================

    #[Test]
    public function it_rejects_password_without_lowercase(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('lowercase letter');

        Password::fromPlaintext('UPPERCASE123');
    }

    #[Test]
    public function it_rejects_password_without_uppercase(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('uppercase letter');

        Password::fromPlaintext('lowercase123');
    }

    #[Test]
    public function it_rejects_password_without_number(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('must contain a number');

        Password::fromPlaintext('NoNumbersHere');
    }

    #[Test]
    #[DataProvider('validPasswordProvider')]
    public function it_accepts_valid_complex_passwords(string $validPassword): void
    {
        $password = Password::fromPlaintext($validPassword);

        $this->assertInstanceOf(Password::class, $password);
    }

    public static function validPasswordProvider(): array
    {
        return [
            'basic valid' => ['Password1'],
            'with special chars' => ['Password1!@#'],
            'longer password' => ['MyVeryLongPassword123'],
            'mixed complexity' => ['aB1cD2eF3'],
            'starts with number' => ['1Password'],
            'starts with lowercase' => ['password1A'],
            'unicode with requirements' => ['Pässwörd1'],
        ];
    }

    // ========================================
    // Verification Tests
    // ========================================

    #[Test]
    public function it_verifies_correct_password(): void
    {
        $plaintext = 'ValidPass123';
        $password = Password::fromPlaintext($plaintext);

        $this->assertTrue($password->verify($plaintext));
    }

    #[Test]
    public function it_rejects_incorrect_password(): void
    {
        $password = Password::fromPlaintext('ValidPass123');

        $this->assertFalse($password->verify('WrongPass123'));
    }

    #[Test]
    public function it_rejects_empty_verification(): void
    {
        $password = Password::fromPlaintext('ValidPass123');

        $this->assertFalse($password->verify(''));
    }

    #[Test]
    public function it_rejects_similar_password(): void
    {
        $password = Password::fromPlaintext('ValidPass123');

        // Case difference should fail
        $this->assertFalse($password->verify('validpass123'));
        $this->assertFalse($password->verify('VALIDPASS123'));
    }

    #[Test]
    public function it_verifies_password_from_hash(): void
    {
        $plaintext = 'TestPassword123';
        $hash = password_hash($plaintext, PASSWORD_ARGON2ID);
        $password = Password::fromHash($hash);

        $this->assertTrue($password->verify($plaintext));
    }

    // ========================================
    // Rehash Tests
    // ========================================

    #[Test]
    public function it_detects_when_rehash_not_needed(): void
    {
        $password = Password::fromPlaintext('ValidPass123');

        // Fresh password with current algorithm shouldn't need rehash
        $this->assertFalse($password->needsRehash());
    }

    #[Test]
    public function it_detects_when_rehash_needed_for_old_algorithm(): void
    {
        // Create hash with weaker algorithm (bcrypt)
        $hash = password_hash('ValidPass123', PASSWORD_BCRYPT);
        $password = Password::fromHash($hash);

        // Should need rehash to Argon2id
        $this->assertTrue($password->needsRehash());
    }

    // ========================================
    // Hash Retrieval Tests
    // ========================================

    #[Test]
    public function it_returns_hash_for_storage(): void
    {
        $password = Password::fromPlaintext('ValidPass123');
        $hash = $password->hash();

        $this->assertIsString($hash);
        $this->assertNotEmpty($hash);
    }

    #[Test]
    public function it_returns_consistent_hash(): void
    {
        $password = Password::fromPlaintext('ValidPass123');

        // Multiple calls should return same hash
        $hash1 = $password->hash();
        $hash2 = $password->hash();

        $this->assertSame($hash1, $hash2);
    }

    // ========================================
    // Equality Tests
    // ========================================

    #[Test]
    public function it_never_equals_another_password(): void
    {
        $password1 = Password::fromPlaintext('ValidPass123');
        $password2 = Password::fromPlaintext('ValidPass123');

        // Passwords should never be compared directly (security)
        $this->assertFalse($password1->equals($password2));
    }

    // ========================================
    // Determinism Tests (for testing)
    // ========================================

    #[Test]
    public function it_produces_different_hashes_for_same_password(): void
    {
        // Due to salting, same password should produce different hashes
        $password1 = Password::fromPlaintext('ValidPass123');
        $password2 = Password::fromPlaintext('ValidPass123');

        $this->assertNotSame($password1->hash(), $password2->hash());

        // But both should verify correctly
        $this->assertTrue($password1->verify('ValidPass123'));
        $this->assertTrue($password2->verify('ValidPass123'));
    }

    // ========================================
    // Edge Cases
    // ========================================

    #[Test]
    public function it_handles_very_long_password(): void
    {
        $longPassword = str_repeat('Aa1', 100); // 300 chars
        $password = Password::fromPlaintext($longPassword);

        $this->assertTrue($password->verify($longPassword));
    }

    #[Test]
    public function it_handles_unicode_in_password(): void
    {
        $password = Password::fromPlaintext('Pässwörd123');

        $this->assertTrue($password->verify('Pässwörd123'));
    }

    #[Test]
    public function it_handles_special_characters(): void
    {
        $password = Password::fromPlaintext('Pass123!@#$%^&*()');

        $this->assertTrue($password->verify('Pass123!@#$%^&*()'));
    }

    // ========================================
    // Immutability Tests
    // ========================================

    #[Test]
    public function it_is_immutable(): void
    {
        $password = Password::fromPlaintext('ValidPass123');

        $reflection = new \ReflectionClass($password);
        $this->assertTrue($reflection->isReadOnly());
    }
}
