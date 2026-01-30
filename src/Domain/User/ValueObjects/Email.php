<?php

declare(strict_types=1);

namespace Gazelle\Domain\User\ValueObjects;

use Gazelle\Domain\Common\ValueObject;

/**
 * Email Value Object
 *
 * Immutable, validated email address.
 */
final readonly class Email extends ValueObject implements \Stringable
{
    private function __construct(
        private string $value
    ) {}

    public static function fromString(string $email): self
    {
        $email = strtolower(trim($email));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Invalid email address: {$email}");
        }

        return new self($email);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function domain(): string
    {
        return substr($this->value, strpos($this->value, '@') + 1);
    }

    public function localPart(): string
    {
        return substr($this->value, 0, strpos($this->value, '@'));
    }

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self && $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
