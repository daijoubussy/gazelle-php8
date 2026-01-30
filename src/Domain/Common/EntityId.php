<?php

declare(strict_types=1);

namespace Gazelle\Domain\Common;

/**
 * Entity ID Value Object
 *
 * Base class for all entity identifiers. IDs are immutable
 * and can be compared for equality.
 */
abstract readonly class EntityId implements \Stringable
{
    protected function __construct(
        private int|string $value
    ) {
        $this->validate($value);
    }

    /**
     * Create from a raw value
     */
    public static function fromValue(int|string $value): static
    {
        return new static($value);
    }

    /**
     * Generate a new unique ID
     */
    public static function generate(): static
    {
        // Default to auto-increment style - override for UUID
        throw new \RuntimeException('Cannot generate ID for this type');
    }

    /**
     * Get the raw value
     */
    public function value(): int|string
    {
        return $this->value;
    }

    /**
     * Compare with another ID
     */
    public function equals(EntityId $other): bool
    {
        return $this::class === $other::class
            && $this->value === $other->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }

    /**
     * Validate the ID value
     */
    protected function validate(int|string $value): void
    {
        // Override in subclass for specific validation
    }
}
