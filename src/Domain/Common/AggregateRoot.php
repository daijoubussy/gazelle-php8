<?php

declare(strict_types=1);

namespace Gazelle\Domain\Common;

/**
 * Aggregate Root
 *
 * An aggregate root is the entry point to an aggregate - a cluster
 * of domain objects that are treated as a single unit.
 *
 * Rules:
 * - Only aggregate roots can be fetched from repositories
 * - References to other aggregates are by ID only
 * - Invariants are enforced within the aggregate boundary
 */
abstract class AggregateRoot extends Entity
{
    private int $version = 0;

    /**
     * Get the aggregate version for optimistic locking
     */
    public function version(): int
    {
        return $this->version;
    }

    /**
     * Increment version after persistence
     */
    public function incrementVersion(): void
    {
        $this->version++;
    }

    /**
     * Set version when hydrating from persistence
     */
    protected function setVersion(int $version): void
    {
        $this->version = $version;
    }
}
