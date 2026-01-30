<?php

declare(strict_types=1);

namespace Gazelle\Tests\Unit\Domain\User\ValueObjects;

use Gazelle\Domain\User\ValueObjects\Email;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Email::class)]
final class EmailTest extends TestCase
{
    // ========================================
    // Creation Tests
    // ========================================

    #[Test]
    public function it_creates_email_from_valid_string(): void
    {
        $email = Email::fromString('user@example.com');

        $this->assertInstanceOf(Email::class, $email);
        $this->assertSame('user@example.com', $email->value());
    }

    #[Test]
    public function it_normalizes_email_to_lowercase(): void
    {
        $email = Email::fromString('User@EXAMPLE.COM');

        $this->assertSame('user@example.com', $email->value());
    }

    #[Test]
    public function it_trims_whitespace_from_email(): void
    {
        $email = Email::fromString('  user@example.com  ');

        $this->assertSame('user@example.com', $email->value());
    }

    #[Test]
    #[DataProvider('validEmailProvider')]
    public function it_accepts_valid_email_formats(string $validEmail): void
    {
        $email = Email::fromString($validEmail);

        $this->assertInstanceOf(Email::class, $email);
    }

    public static function validEmailProvider(): array
    {
        return [
            'simple' => ['user@example.com'],
            'with subdomain' => ['user@mail.example.com'],
            'with plus' => ['user+tag@example.com'],
            'with dots' => ['first.last@example.com'],
            'with numbers' => ['user123@example.com'],
            'with hyphen domain' => ['user@my-domain.com'],
            'long tld' => ['user@example.technology'],
            'short local' => ['a@b.co'],
        ];
    }

    // ========================================
    // Validation Tests
    // ========================================

    #[Test]
    #[DataProvider('invalidEmailProvider')]
    public function it_rejects_invalid_email_formats(string $invalidEmail): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email address');

        Email::fromString($invalidEmail);
    }

    public static function invalidEmailProvider(): array
    {
        return [
            'empty string' => [''],
            'no at sign' => ['userexample.com'],
            'no domain' => ['user@'],
            'no local part' => ['@example.com'],
            'double at' => ['user@@example.com'],
            'spaces' => ['user @example.com'],
            'no tld' => ['user@example'],
            'invalid chars' => ['user<>@example.com'],
        ];
    }

    // ========================================
    // Domain Extraction Tests
    // ========================================

    #[Test]
    public function it_extracts_domain_correctly(): void
    {
        $email = Email::fromString('user@example.com');

        $this->assertSame('example.com', $email->domain());
    }

    #[Test]
    public function it_extracts_domain_with_subdomain(): void
    {
        $email = Email::fromString('user@mail.example.com');

        $this->assertSame('mail.example.com', $email->domain());
    }

    // ========================================
    // Local Part Extraction Tests
    // ========================================

    #[Test]
    public function it_extracts_local_part_correctly(): void
    {
        $email = Email::fromString('user@example.com');

        $this->assertSame('user', $email->localPart());
    }

    #[Test]
    public function it_extracts_local_part_with_plus(): void
    {
        $email = Email::fromString('user+tag@example.com');

        $this->assertSame('user+tag', $email->localPart());
    }

    #[Test]
    public function it_extracts_local_part_with_dots(): void
    {
        $email = Email::fromString('first.middle.last@example.com');

        $this->assertSame('first.middle.last', $email->localPart());
    }

    // ========================================
    // Equality Tests
    // ========================================

    #[Test]
    public function it_equals_same_email(): void
    {
        $email1 = Email::fromString('user@example.com');
        $email2 = Email::fromString('user@example.com');

        $this->assertTrue($email1->equals($email2));
    }

    #[Test]
    public function it_equals_same_email_different_case(): void
    {
        $email1 = Email::fromString('user@example.com');
        $email2 = Email::fromString('USER@EXAMPLE.COM');

        // After normalization, they should be equal
        $this->assertTrue($email1->equals($email2));
    }

    #[Test]
    public function it_not_equals_different_email(): void
    {
        $email1 = Email::fromString('user1@example.com');
        $email2 = Email::fromString('user2@example.com');

        $this->assertFalse($email1->equals($email2));
    }

    // ========================================
    // String Conversion Tests
    // ========================================

    #[Test]
    public function it_converts_to_string(): void
    {
        $email = Email::fromString('user@example.com');

        $this->assertSame('user@example.com', (string) $email);
    }

    #[Test]
    public function it_implements_stringable(): void
    {
        $email = Email::fromString('user@example.com');

        $this->assertInstanceOf(\Stringable::class, $email);
    }

    // ========================================
    // Immutability Tests
    // ========================================

    #[Test]
    public function it_is_immutable(): void
    {
        $email = Email::fromString('user@example.com');

        // Value object should be readonly/immutable
        $reflection = new \ReflectionClass($email);
        $this->assertTrue($reflection->isReadOnly());
    }
}
