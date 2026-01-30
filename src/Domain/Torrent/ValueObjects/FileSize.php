<?php

declare(strict_types=1);

namespace Gazelle\Domain\Torrent\ValueObjects;

use Gazelle\Domain\Common\ValueObject;

/**
 * File Size Value Object
 *
 * Represents a file size in bytes with formatting utilities.
 */
final readonly class FileSize extends ValueObject implements \Stringable
{
    private const UNITS = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

    private function __construct(
        private int $bytes
    ) {
        if ($bytes < 0) {
            throw new \InvalidArgumentException('File size cannot be negative');
        }
    }

    public static function fromBytes(int $bytes): self
    {
        return new self($bytes);
    }

    public static function fromKilobytes(float $kb): self
    {
        return new self((int) ($kb * 1024));
    }

    public static function fromMegabytes(float $mb): self
    {
        return new self((int) ($mb * 1024 * 1024));
    }

    public static function fromGigabytes(float $gb): self
    {
        return new self((int) ($gb * 1024 * 1024 * 1024));
    }

    public static function zero(): self
    {
        return new self(0);
    }

    public function bytes(): int
    {
        return $this->bytes;
    }

    public function kilobytes(): float
    {
        return $this->bytes / 1024;
    }

    public function megabytes(): float
    {
        return $this->bytes / (1024 * 1024);
    }

    public function gigabytes(): float
    {
        return $this->bytes / (1024 * 1024 * 1024);
    }

    /**
     * Format with appropriate unit
     */
    public function format(int $precision = 2): string
    {
        $bytes = $this->bytes;

        if ($bytes === 0) {
            return '0 B';
        }

        $unitIndex = 0;
        while ($bytes >= 1024 && $unitIndex < count(self::UNITS) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }

        return round($bytes, $precision) . ' ' . self::UNITS[$unitIndex];
    }

    public function add(FileSize $other): self
    {
        return new self($this->bytes + $other->bytes);
    }

    public function subtract(FileSize $other): self
    {
        return new self(max(0, $this->bytes - $other->bytes));
    }

    public function isGreaterThan(FileSize $other): bool
    {
        return $this->bytes > $other->bytes;
    }

    public function isLessThan(FileSize $other): bool
    {
        return $this->bytes < $other->bytes;
    }

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self && $this->bytes === $other->bytes;
    }

    public function __toString(): string
    {
        return $this->format();
    }
}
