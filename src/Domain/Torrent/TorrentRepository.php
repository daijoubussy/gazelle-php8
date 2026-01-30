<?php

declare(strict_types=1);

namespace Gazelle\Domain\Torrent;

use Gazelle\Domain\Common\Repository;
use Gazelle\Domain\Torrent\ValueObjects\InfoHash;
use Gazelle\Domain\User\UserId;

/**
 * Torrent Repository Interface
 *
 * @extends Repository<Torrent>
 */
interface TorrentRepository extends Repository
{
    /**
     * Find a torrent by ID
     */
    public function findById(TorrentId $id): ?Torrent;

    /**
     * Get a torrent by ID or throw
     *
     * @throws TorrentNotFoundException
     */
    public function getById(TorrentId $id): Torrent;

    /**
     * Find a torrent by info hash
     */
    public function findByInfoHash(InfoHash $infoHash): ?Torrent;

    /**
     * Find torrents by group ID
     *
     * @return iterable<Torrent>
     */
    public function findByGroupId(TorrentGroupId $groupId): iterable;

    /**
     * Find torrents uploaded by a user
     *
     * @return iterable<Torrent>
     */
    public function findByUploader(UserId $uploaderId, int $limit = 50, int $offset = 0): iterable;

    /**
     * Get the next available torrent ID
     */
    public function nextId(): TorrentId;

    /**
     * Check if an info hash already exists
     */
    public function infoHashExists(InfoHash $infoHash): bool;

    /**
     * Find dead torrents (no seeders)
     *
     * @return iterable<Torrent>
     */
    public function findDead(int $limit = 100): iterable;

    /**
     * Count total torrents
     */
    public function count(): int;
}
