<?php

declare(strict_types=1);

namespace Gazelle\Domain\Torrent;

use Gazelle\Domain\Common\AggregateRoot;
use Gazelle\Domain\Common\EntityId;
use Gazelle\Domain\Torrent\Events\TorrentUploaded;
use Gazelle\Domain\Torrent\Events\TorrentSnatched;
use Gazelle\Domain\Torrent\ValueObjects\Bitrate;
use Gazelle\Domain\Torrent\ValueObjects\FileSize;
use Gazelle\Domain\Torrent\ValueObjects\FreeleechStatus;
use Gazelle\Domain\Torrent\ValueObjects\InfoHash;
use Gazelle\Domain\Torrent\ValueObjects\MediaFormat;
use Gazelle\Domain\User\UserId;

/**
 * Torrent Aggregate Root
 *
 * Represents a single torrent file with its metadata and stats.
 */
final class Torrent extends AggregateRoot
{
    private function __construct(
        private readonly TorrentId $id,
        private readonly TorrentGroupId $groupId,
        private readonly UserId $uploaderId,
        private InfoHash $infoHash,
        private MediaFormat $format,
        private Bitrate $bitrate,
        private FileSize $size,
        private int $fileCount,
        private FreeleechStatus $freeleechStatus,
        private int $seeders,
        private int $leechers,
        private int $snatches,
        private bool $isScene,
        private ?string $description,
        private readonly \DateTimeImmutable $uploadedAt,
        private ?\DateTimeImmutable $lastSeeded
    ) {}

    /**
     * Upload a new torrent
     */
    public static function upload(
        TorrentId $id,
        TorrentGroupId $groupId,
        UserId $uploaderId,
        InfoHash $infoHash,
        MediaFormat $format,
        Bitrate $bitrate,
        FileSize $size,
        int $fileCount,
        bool $isScene = false,
        ?string $description = null
    ): self {
        $torrent = new self(
            id: $id,
            groupId: $groupId,
            uploaderId: $uploaderId,
            infoHash: $infoHash,
            format: $format,
            bitrate: $bitrate,
            size: $size,
            fileCount: $fileCount,
            freeleechStatus: FreeleechStatus::Normal,
            seeders: 0,
            leechers: 0,
            snatches: 0,
            isScene: $isScene,
            description: $description,
            uploadedAt: new \DateTimeImmutable(),
            lastSeeded: null
        );

        $torrent->recordEvent(new TorrentUploaded($id, $groupId, $uploaderId));

        return $torrent;
    }

    /**
     * Hydrate from persistence
     *
     * @param array<string, mixed> $data
     */
    public static function fromPersistence(array $data): self
    {
        $torrent = new self(
            id: TorrentId::fromInt((int) $data['id']),
            groupId: TorrentGroupId::fromInt((int) $data['group_id']),
            uploaderId: UserId::fromInt((int) $data['uploader_id']),
            infoHash: InfoHash::fromHex($data['info_hash']),
            format: MediaFormat::from($data['format']),
            bitrate: self::hydrateBitrate($data),
            size: FileSize::fromBytes((int) $data['size']),
            fileCount: (int) $data['file_count'],
            freeleechStatus: FreeleechStatus::from($data['freeleech_status']),
            seeders: (int) $data['seeders'],
            leechers: (int) $data['leechers'],
            snatches: (int) $data['snatches'],
            isScene: (bool) $data['is_scene'],
            description: $data['description'],
            uploadedAt: new \DateTimeImmutable($data['uploaded_at']),
            lastSeeded: $data['last_seeded']
                ? new \DateTimeImmutable($data['last_seeded'])
                : null
        );

        $torrent->setVersion((int) ($data['version'] ?? 0));

        return $torrent;
    }

    public function id(): EntityId
    {
        return $this->id;
    }

    public function torrentId(): TorrentId
    {
        return $this->id;
    }

    public function groupId(): TorrentGroupId
    {
        return $this->groupId;
    }

    public function uploaderId(): UserId
    {
        return $this->uploaderId;
    }

    public function infoHash(): InfoHash
    {
        return $this->infoHash;
    }

    public function format(): MediaFormat
    {
        return $this->format;
    }

    public function bitrate(): Bitrate
    {
        return $this->bitrate;
    }

    public function size(): FileSize
    {
        return $this->size;
    }

    public function fileCount(): int
    {
        return $this->fileCount;
    }

    public function freeleechStatus(): FreeleechStatus
    {
        return $this->freeleechStatus;
    }

    public function seeders(): int
    {
        return $this->seeders;
    }

    public function leechers(): int
    {
        return $this->leechers;
    }

    public function snatches(): int
    {
        return $this->snatches;
    }

    public function isScene(): bool
    {
        return $this->isScene;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function uploadedAt(): \DateTimeImmutable
    {
        return $this->uploadedAt;
    }

    public function lastSeeded(): ?\DateTimeImmutable
    {
        return $this->lastSeeded;
    }

    public function isSeeded(): bool
    {
        return $this->seeders > 0;
    }

    public function isDead(): bool
    {
        return $this->seeders === 0 && $this->leechers === 0;
    }

    public function isFreeleech(): bool
    {
        return $this->freeleechStatus === FreeleechStatus::Free;
    }

    /**
     * Record a snatch
     */
    public function recordSnatch(UserId $userId): void
    {
        $this->snatches++;
        $this->recordEvent(new TorrentSnatched($this->id, $userId));
    }

    /**
     * Update peer counts from tracker
     */
    public function updatePeerCounts(int $seeders, int $leechers): void
    {
        $this->seeders = $seeders;
        $this->leechers = $leechers;

        if ($seeders > 0) {
            $this->lastSeeded = new \DateTimeImmutable();
        }
    }

    /**
     * Set freeleech status
     */
    public function setFreeleech(FreeleechStatus $status): void
    {
        $this->freeleechStatus = $status;
    }

    /**
     * Convert to persistence format
     *
     * @return array<string, mixed>
     */
    public function toPersistence(): array
    {
        return [
            'id' => $this->id->value(),
            'group_id' => $this->groupId->value(),
            'uploader_id' => $this->uploaderId->value(),
            'info_hash' => $this->infoHash->hex(),
            'format' => $this->format->value,
            'bitrate_kbps' => $this->bitrate->kbps(),
            'bitrate_variable' => $this->bitrate->isVariable(),
            'bitrate_lossless' => $this->bitrate->isLossless(),
            'size' => $this->size->bytes(),
            'file_count' => $this->fileCount,
            'freeleech_status' => $this->freeleechStatus->value,
            'seeders' => $this->seeders,
            'leechers' => $this->leechers,
            'snatches' => $this->snatches,
            'is_scene' => $this->isScene,
            'description' => $this->description,
            'uploaded_at' => $this->uploadedAt->format('Y-m-d H:i:s'),
            'last_seeded' => $this->lastSeeded?->format('Y-m-d H:i:s'),
            'version' => $this->version(),
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function hydrateBitrate(array $data): Bitrate
    {
        if ($data['bitrate_lossless']) {
            return Bitrate::lossless();
        }

        $kbps = (int) $data['bitrate_kbps'];

        if ($data['bitrate_variable']) {
            return Bitrate::vbr($kbps);
        }

        return Bitrate::cbr($kbps);
    }
}
