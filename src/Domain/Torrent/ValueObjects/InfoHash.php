<?php

declare(strict_types=1);

namespace Gazelle\Domain\Torrent\ValueObjects;

use Gazelle\Domain\Common\ValueObject;

/**
 * Info Hash Value Object
 *
 * 20-byte SHA1 hash identifying a torrent.
 */
final readonly class InfoHash extends ValueObject implements \Stringable
{
    private const LENGTH = 40; // Hex representation

    private function __construct(
        private string $value
    ) {}

    /**
     * Create from hex string
     */
    public static function fromHex(string $hex): self
    {
        $hex = strtolower(trim($hex));

        if (strlen($hex) !== self::LENGTH) {
            throw new \InvalidArgumentException(
                'Info hash must be exactly ' . self::LENGTH . ' hex characters'
            );
        }

        if (!ctype_xdigit($hex)) {
            throw new \InvalidArgumentException('Info hash must be valid hex');
        }

        return new self($hex);
    }

    /**
     * Create from binary
     */
    public static function fromBinary(string $binary): self
    {
        if (strlen($binary) !== 20) {
            throw new \InvalidArgumentException('Binary info hash must be 20 bytes');
        }

        return new self(bin2hex($binary));
    }

    public function hex(): string
    {
        return $this->value;
    }

    public function binary(): string
    {
        return hex2bin($this->value);
    }

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self && $this->value === $other->value;
    }

    public function __toString(): string
    {
        return strtoupper($this->value);
    }
}
