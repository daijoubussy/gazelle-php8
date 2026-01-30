<?php

declare(strict_types=1);

namespace Gazelle\Domain\Common;

/**
 * Domain Event
 *
 * Represents something that happened in the domain. Events are
 * immutable facts that have already occurred.
 */
abstract readonly class DomainEvent
{
    public function __construct(
        private \DateTimeImmutable $occurredAt = new \DateTimeImmutable()
    ) {}

    /**
     * When the event occurred
     */
    public function occurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }

    /**
     * Get the event name for serialization
     */
    public function eventName(): string
    {
        $class = static::class;
        $parts = explode('\\', $class);
        return end($parts);
    }

    /**
     * Get the aggregate ID this event relates to
     */
    abstract public function aggregateId(): EntityId;

    /**
     * Serialize event data to array
     *
     * @return array<string, mixed>
     */
    abstract public function toArray(): array;
}
