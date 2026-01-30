<?php

declare(strict_types=1);

namespace Gazelle\Domain\Common;

/**
 * Repository Interface
 *
 * Base interface for all repositories. Repositories provide
 * collection-like access to aggregates.
 *
 * @template T of AggregateRoot
 */
interface Repository
{
    /**
     * Find an aggregate by its ID
     *
     * @return T|null
     */
    public function findById(EntityId $id): ?AggregateRoot;

    /**
     * Get an aggregate by ID or throw
     *
     * @return T
     * @throws EntityNotFoundException
     */
    public function getById(EntityId $id): AggregateRoot;

    /**
     * Persist an aggregate
     *
     * @param T $aggregate
     */
    public function save(AggregateRoot $aggregate): void;

    /**
     * Remove an aggregate
     *
     * @param T $aggregate
     */
    public function remove(AggregateRoot $aggregate): void;
}
