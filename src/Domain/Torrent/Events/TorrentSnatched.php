<?php

declare(strict_types=1);

namespace Gazelle\Domain\Torrent\Events;

use Gazelle\Domain\Common\DomainEvent;
use Gazelle\Domain\Common\EntityId;
use Gazelle\Domain\Torrent\TorrentId;
use Gazelle\Domain\User\UserId;

/**
 * Torrent Snatched Event
 */
final readonly class TorrentSnatched extends DomainEvent
{
    public function __construct(
        private TorrentId $torrentId,
        private UserId $userId,
        \DateTimeImmutable $occurredAt = new \DateTimeImmutable()
    ) {
        parent::__construct($occurredAt);
    }

    public function aggregateId(): EntityId
    {
        return $this->torrentId;
    }

    public function torrentId(): TorrentId
    {
        return $this->torrentId;
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function toArray(): array
    {
        return [
            'torrent_id' => $this->torrentId->value(),
            'user_id' => $this->userId->value(),
            'occurred_at' => $this->occurredAt()->format('c'),
        ];
    }
}
