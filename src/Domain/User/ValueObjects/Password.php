<?php

declare(strict_types=1);

namespace Gazelle\Domain\User\ValueObjects;

use Gazelle\Domain\Common\ValueObject;

/**
 * Password Value Object
 *
 * Securely hashed password. Never stores plaintext.
 */
final readonly class Password extends ValueObject
{
    private const MIN_LENGTH = 8;
    private const ALGORITHM = PASSWORD_ARGON2ID;
    private const OPTIONS = [
        'memory_cost' => 65536,
        'time_cost' => 4,
        'threads' => 3,
    ];

    private function __construct(
        private string $hash
    ) {}

    /**
     * Create from plaintext password (hashes it)
     */
    public static function fromPlaintext(string $password): self
    {
        self::validatePlaintext($password);

        $hash = password_hash($password, self::ALGORITHM, self::OPTIONS);

        return new self($hash);
    }

    /**
     * Create from an existing hash (for hydration)
     */
    public static function fromHash(string $hash): self
    {
        return new self($hash);
    }

    /**
     * Verify a plaintext password against this hash
     */
    public function verify(string $plaintext): bool
    {
        return password_verify($plaintext, $this->hash);
    }

    /**
     * Check if the hash needs rehashing
     */
    public function needsRehash(): bool
    {
        return password_needs_rehash($this->hash, self::ALGORITHM, self::OPTIONS);
    }

    /**
     * Get the hash for storage
     */
    public function hash(): string
    {
        return $this->hash;
    }

    public function equals(ValueObject $other): bool
    {
        // Passwords should never be compared directly
        return false;
    }

    /**
     * Validate plaintext password requirements
     */
    private static function validatePlaintext(string $password): void
    {
        if (strlen($password) < self::MIN_LENGTH) {
            throw new \InvalidArgumentException(
                'Password must be at least ' . self::MIN_LENGTH . ' characters'
            );
        }

        // Require mixed case, numbers, and special chars for security
        if (!preg_match('/[a-z]/', $password)) {
            throw new \InvalidArgumentException('Password must contain a lowercase letter');
        }

        if (!preg_match('/[A-Z]/', $password)) {
            throw new \InvalidArgumentException('Password must contain an uppercase letter');
        }

        if (!preg_match('/[0-9]/', $password)) {
            throw new \InvalidArgumentException('Password must contain a number');
        }
    }
}
