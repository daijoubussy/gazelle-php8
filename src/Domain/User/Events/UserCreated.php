<?php

declare(strict_types=1);

namespace Gazelle\Domain\User\Events;

use Gazelle\Domain\Common\DomainEvent;
use Gazelle\Domain\Common\EntityId;
use Gazelle\Domain\User\UserId;
use Gazelle\Domain\User\ValueObjects\Email;
use Gazelle\Domain\User\ValueObjects\Username;

/**
 * User Created Event
 */
final readonly class UserCreated extends DomainEvent
{
    public function __construct(
        private UserId $userId,
        private Username $username,
        private Email $email,
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

    public function username(): Username
    {
        return $this->username;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId->value(),
            'username' => (string) $this->username,
            'email' => (string) $this->email,
            'occurred_at' => $this->occurredAt()->format('c'),
        ];
    }
}
