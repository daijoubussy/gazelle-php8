<?php

declare(strict_types=1);

namespace Gazelle\Domain\User\Events;

use Gazelle\Domain\Common\DomainEvent;
use Gazelle\Domain\Common\EntityId;
use Gazelle\Domain\User\UserId;

/**
 * User Password Changed Event
 */
final readonly class UserPasswordChanged extends DomainEvent
{
    public function __construct(
        private UserId $userId,
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

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId->value(),
            'occurred_at' => $this->occurredAt()->format('c'),
        ];
    }
}
