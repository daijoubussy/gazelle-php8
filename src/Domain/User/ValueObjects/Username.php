<?php

declare(strict_types=1);

namespace Gazelle\Domain\User\ValueObjects;

use Gazelle\Domain\Common\ValueObject;

/**
 * Username Value Object
 *
 * Validated, normalized username.
 */
final readonly class Username extends ValueObject implements \Stringable
{
    private const MIN_LENGTH = 3;
    private const MAX_LENGTH = 20;
    private const PATTERN = '/^[a-zA-Z0-9_]+$/';

    private function __construct(
        private string $value
    ) {}

    public static function fromString(string $username): self
    {
        $username = trim($username);

        if (strlen($username) < self::MIN_LENGTH) {
            throw new \InvalidArgumentException(
                'Username must be at least ' . self::MIN_LENGTH . ' characters'
            );
        }

        if (strlen($username) > self::MAX_LENGTH) {
            throw new \InvalidArgumentException(
                'Username cannot exceed ' . self::MAX_LENGTH . ' characters'
            );
        }

        if (!preg_match(self::PATTERN, $username)) {
            throw new \InvalidArgumentException(
                'Username can only contain letters, numbers, and underscores'
            );
        }

        return new self($username);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self
            && strtolower($this->value) === strtolower($other->value);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
