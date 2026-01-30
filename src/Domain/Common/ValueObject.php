<?php

declare(strict_types=1);

namespace Gazelle\Domain\Common;

/**
 * Value Object Base
 *
 * Value objects are immutable and compared by their attributes,
 * not by identity. They represent descriptive aspects of the domain.
 */
abstract readonly class ValueObject
{
    /**
     * Compare with another value object
     */
    abstract public function equals(ValueObject $other): bool;

    /**
     * Prevent cloning - value objects should be created fresh
     */
    private function __clone(): void
    {
    }
}
