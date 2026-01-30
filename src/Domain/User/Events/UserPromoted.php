<?php

declare(strict_types=1);

namespace Gazelle\Domain\User\Events;

use Gazelle\Domain\Common\DomainEvent;
use Gazelle\Domain\Common\EntityId;
use Gazelle\Domain\User\UserId;
use Gazelle\Domain\User\ValueObjects\UserClass;

/**
 * User Promoted Event
 */
final readonly class UserPromoted extends DomainEvent
{
    public function __construct(
        private UserId $userId,
        private UserClass $fromClass,
        private UserClass $toClass,
        \DateTimeImmutable $occurredAt = new \DateTimeImmutable()
    ) {
        parent::__construct($occurredAt);
    }

    public function aggregateId(): EntityId
    {
        return $this->userId;
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function fromClass(): UserClass
    {
        return $this->fromClass;
    }

    public function toClass(): UserClass
    {
        return $this->toClass;
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId->value(),
            'from_class' => $this->fromClass->value,
            'to_class' => $this->toClass->value,
            'occurred_at' => $this->occurredAt()->format('c'),
        ];
    }
}
