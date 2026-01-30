<?php

declare(strict_types=1);

namespace Gazelle\Application\Torrent\DTOs;

use Gazelle\Domain\Torrent\Torrent;

/**
 * Torrent DTO
 *
 * Data transfer object for torrent information.
 */
final readonly class TorrentDTO
{
    public function __construct(
        public int $id,
        public int $groupId,
        public int $uploaderId,
        public string $infoHash,
        public int $fileCount,
        public int $size,
        public ?string $format,
        public ?string $encoding,
        public ?int $bitrate,
        public bool $hasLog,
        public bool $hasCue,
        public ?int $logScore,
        public bool $scene,
        public bool $remastered,
        public ?int $remasterYear,
        public ?string $remasterTitle,
        public ?string $remasterRecordLabel,
        public ?string $remasterCatalogueNumber,
        public ?string $freeleechType,
        public ?string $freeleechWhen,
        public int $snatched,
        public int $seeders,
        public int $leechers,
        public \DateTimeImmutable $createdAt,
        public ?string $description,
        public ?string $filePath,
        public bool $isDeleted
    ) {}

    /**
     * Create from Torrent entity
     */
    public static function fromTorrent(Torrent $torrent): self
    {
        return new self(
            id: $torrent->id()->value(),
            groupId: $torrent->groupId()->value(),
            uploaderId: $torrent->uploaderId()->value(),
            infoHash: (string) $torrent->infoHash(),
            fileCount: $torrent->fileCount(),
            size: $torrent->size()->bytes(),
            format: $torrent->format()?->value,
            encoding: $torrent->bitrate()?->encoding(),
            bitrate: $torrent->bitrate()?->value(),
            hasLog: $torrent->hasLog(),
            hasCue: $torrent->hasCue(),
            logScore: $torrent->logScore(),
            scene: $torrent->isScene(),
            remastered: $torrent->isRemastered(),
            remasterYear: $torrent->remasterYear(),
            remasterTitle: $torrent->remasterTitle(),
            remasterRecordLabel: $torrent->remasterRecordLabel(),
            remasterCatalogueNumber: $torrent->remasterCatalogueNumber(),
            freeleechType: $torrent->freeleechStatus()->type(),
            freeleechWhen: $torrent->freeleechStatus()->when()?->format('c'),
            snatched: $torrent->snatched(),
            seeders: $torrent->seeders(),
            leechers: $torrent->leechers(),
            createdAt: $torrent->createdAt(),
            description: $torrent->description(),
            filePath: $torrent->filePath(),
            isDeleted: $torrent->isDeleted()
        );
    }

    /**
     * Create from database row
     *
     * @param array<string, mixed> $row
     */
    public static function fromDatabaseRow(array $row): self
    {
        return new self(
            id: (int) $row['ID'],
            groupId: (int) $row['GroupID'],
            uploaderId: (int) $row['UserID'],
            infoHash: bin2hex($row['info_hash'] ?? ''),
            fileCount: (int) ($row['FileCount'] ?? 0),
            size: (int) ($row['Size'] ?? 0),
            format: $row['Format'] ?? null,
            encoding: $row['Encoding'] ?? null,
            bitrate: $row['Bitrate'] !== null ? (int) $row['Bitrate'] : null,
            hasLog: (bool) ($row['HasLog'] ?? false),
            hasCue: (bool) ($row['HasCue'] ?? false),
            logScore: $row['LogScore'] !== null ? (int) $row['LogScore'] : null,
            scene: (bool) ($row['Scene'] ?? false),
            remastered: (bool) ($row['Remastered'] ?? false),
            remasterYear: $row['RemasterYear'] !== null ? (int) $row['RemasterYear'] : null,
            remasterTitle: $row['RemasterTitle'] ?? null,
            remasterRecordLabel: $row['RemasterRecordLabel'] ?? null,
            remasterCatalogueNumber: $row['RemasterCatalogueNumber'] ?? null,
            freeleechType: $row['FreeTorrent'] ?? null,
            freeleechWhen: $row['FreeLeechType'] ?? null,
            snatched: (int) ($row['Snatched'] ?? 0),
            seeders: (int) ($row['Seeders'] ?? 0),
            leechers: (int) ($row['Leechers'] ?? 0),
            createdAt: new \DateTimeImmutable($row['Time'] ?? 'now'),
            description: $row['Description'] ?? null,
            filePath: $row['FilePath'] ?? null,
            isDeleted: false
        );
    }

    /**
     * Get formatted size
     */
    public function formattedSize(): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = $this->size;
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get display name for this torrent
     */
    public function displayName(): string
    {
        $parts = [];

        if ($this->format) {
            $parts[] = $this->format;
        }

        if ($this->encoding) {
            $parts[] = $this->encoding;
        }

        if ($this->hasLog) {
            $log = 'Log';
            if ($this->logScore !== null) {
                $log .= " ({$this->logScore}%)";
            }
            $parts[] = $log;
        }

        if ($this->hasCue) {
            $parts[] = 'Cue';
        }

        if ($this->scene) {
            $parts[] = 'Scene';
        }

        return implode(' / ', $parts);
    }

    /**
     * Check if this is a perfect FLAC
     */
    public function isPerfectFlac(): bool
    {
        return $this->format === 'FLAC'
            && $this->hasLog
            && $this->hasCue
            && $this->logScore === 100;
    }

    /**
     * Check if currently seeded
     */
    public function isSeeded(): bool
    {
        return $this->seeders > 0;
    }

    /**
     * Get health ratio (seeders/leechers)
     */
    public function healthRatio(): float
    {
        if ($this->leechers === 0) {
            return $this->seeders > 0 ? INF : 0.0;
        }

        return $this->seeders / $this->leechers;
    }
}
