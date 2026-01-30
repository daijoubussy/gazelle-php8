<?php

declare(strict_types=1);

namespace Gazelle\Domain\Torrent\ValueObjects;

use Gazelle\Domain\Common\ValueObject;

/**
 * Bitrate Value Object
 */
final readonly class Bitrate extends ValueObject implements \Stringable
{
    private function __construct(
        private ?int $kbps,
        private bool $isVariable,
        private bool $isLossless
    ) {}

    public static function lossless(): self
    {
        return new self(null, false, true);
    }

    public static function cbr(int $kbps): self
    {
        if ($kbps < 1) {
            throw new \InvalidArgumentException('Bitrate must be positive');
        }
        return new self($kbps, false, false);
    }

    public static function vbr(int $averageKbps): self
    {
        if ($averageKbps < 1) {
            throw new \InvalidArgumentException('Bitrate must be positive');
        }
        return new self($averageKbps, true, false);
    }

    public static function v0(): self
    {
        return new self(245, true, false); // Approximate average for V0
    }

    public static function v2(): self
    {
        return new self(190, true, false); // Approximate average for V2
    }

    public function kbps(): ?int
    {
        return $this->kbps;
    }

    public function isVariable(): bool
    {
        return $this->isVariable;
    }

    public function isLossless(): bool
    {
        return $this->isLossless;
    }

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self
            && $this->kbps === $other->kbps
            && $this->isVariable === $other->isVariable
            && $this->isLossless === $other->isLossless;
    }

    public function __toString(): string
    {
        if ($this->isLossless) {
            return 'Lossless';
        }

        $rate = $this->kbps . ' kbps';

        if ($this->isVariable) {
            return $rate . ' (VBR)';
        }

        return $rate;
    }
}
