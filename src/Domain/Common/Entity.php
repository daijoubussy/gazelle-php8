<?php

declare(strict_types=1);

namespace Gazelle\Domain\Common;

/**
 * Base Entity
 *
 * All domain entities extend this class. Entities have identity
 * and are compared by their ID, not their attributes.
 */
abstract class Entity
{
    /** @var array<DomainEvent> */
    private array $domainEvents = [];

    /**
     * Get the entity's unique identifier
     */
    abstract public function id(): EntityId;

    /**
     * Compare entities by identity
     */
    public function equals(Entity $other): bool
    {
        return $this::class === $other::class
            && $this->id()->equals($other->id());
    }

    /**
     * Record a domain event
     */
    protected function recordEvent(DomainEvent $event): void
    {
        $this->domainEvents[] = $event;
    }

    /**
     * Get and clear recorded domain events
     *
     * @return array<DomainEvent>
     */
    public function pullDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];
        return $events;
    }
}
