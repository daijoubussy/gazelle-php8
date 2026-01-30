<?php

declare(strict_types=1);

namespace Gazelle\Domain\Torrent\Events;

use Gazelle\Domain\Common\DomainEvent;
use Gazelle\Domain\Common\EntityId;
use Gazelle\Domain\Torrent\TorrentGroupId;
use Gazelle\Domain\Torrent\TorrentId;
use Gazelle\Domain\User\UserId;

/**
 * Torrent Uploaded Event
 */
final readonly class TorrentUploaded extends DomainEvent
{
    public function __construct(
        private TorrentId $torrentId,
        private TorrentGroupId $groupId,
        private UserId $uploaderId,
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

    public function groupId(): TorrentGroupId
    {
        return $this->groupId;
    }

    public function uploaderId(): UserId
    {
        return $this->uploaderId;
    }

    public function toArray(): array
    {
        return [
            'torrent_id' => $this->torrentId->value(),
            'group_id' => $this->groupId->value(),
            'uploader_id' => $this->uploaderId->value(),
            'occurred_at' => $this->occurredAt()->format('c'),
        ];
    }
}
