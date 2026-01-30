<?php

declare(strict_types=1);

namespace Gazelle\Application\Torrent\Services;

use Gazelle\Application\Torrent\DTOs\TorrentDTO;
use Gazelle\Application\Torrent\DTOs\TorrentGroupDTO;
use Gazelle\Domain\Torrent\Torrent;
use Gazelle\Domain\Torrent\TorrentId;
use Gazelle\Domain\Torrent\TorrentRepository;
use Gazelle\Domain\User\UserId;
use Gazelle\Infrastructure\Cache\CacheInterface;

/**
 * Torrent Service
 *
 * Application service for torrent operations.
 * Replaces legacy Torrents class.
 */
final readonly class TorrentService
{
    private const CACHE_TORRENT = 'torrent_%d';
    private const CACHE_GROUP = 'torrent_group_%d';
    private const CACHE_DURATION = 86400; // 24 hours

    public function __construct(
        private TorrentRepository $torrentRepository,
        private CacheInterface $cache
    ) {}

    /**
     * Get a torrent by ID
     * Replaces Torrents::get_torrent()
     */
    public function getTorrent(TorrentId $id): ?TorrentDTO
    {
        $cacheKey = sprintf(self::CACHE_TORRENT, $id->value());

        $cached = $this->cache->get($cacheKey);
        if ($cached instanceof TorrentDTO) {
            return $cached;
        }

        $torrent = $this->torrentRepository->findById($id);
        if ($torrent === null) {
            return null;
        }

        $dto = TorrentDTO::fromTorrent($torrent);
        $this->cache->set($cacheKey, $dto, self::CACHE_DURATION);

        return $dto;
    }

    /**
     * Get multiple torrents by IDs
     * Replaces Torrents::get_groups()
     *
     * @param array<TorrentId> $ids
     * @return array<int, TorrentDTO>
     */
    public function getTorrents(array $ids): array
    {
        $result = [];
        $toFetch = [];

        // Check cache first
        foreach ($ids as $id) {
            $cacheKey = sprintf(self::CACHE_TORRENT, $id->value());
            $cached = $this->cache->get($cacheKey);

            if ($cached instanceof TorrentDTO) {
                $result[$id->value()] = $cached;
            } else {
                $toFetch[] = $id;
            }
        }

        // Fetch missing from repository
        if (!empty($toFetch)) {
            $torrents = $this->torrentRepository->findByIds($toFetch);

            foreach ($torrents as $torrent) {
                $dto = TorrentDTO::fromTorrent($torrent);
                $result[$torrent->id()->value()] = $dto;

                $cacheKey = sprintf(self::CACHE_TORRENT, $torrent->id()->value());
                $this->cache->set($cacheKey, $dto, self::CACHE_DURATION);
            }
        }

        return $result;
    }

    /**
     * Check if torrent is freeleech
     */
    public function isFreeleech(TorrentId $id): bool
    {
        $torrent = $this->getTorrent($id);
        return $torrent?->freeleechType !== null && $torrent->freeleechType !== 'none';
    }

    /**
     * Record a snatch
     */
    public function recordSnatch(TorrentId $torrentId, UserId $userId): void
    {
        $torrent = $this->torrentRepository->findById($torrentId);
        if ($torrent === null) {
            return;
        }

        $torrent->recordSnatch($userId);
        $this->torrentRepository->save($torrent);
        $this->invalidateCache($torrentId);
    }

    /**
     * Update torrent seeders count
     */
    public function updateSeeders(TorrentId $id, int $count): void
    {
        $torrent = $this->torrentRepository->findById($id);
        if ($torrent === null) {
            return;
        }

        $torrent->updateSeeders($count);
        $this->torrentRepository->save($torrent);
        $this->invalidateCache($id);
    }

    /**
     * Update torrent leechers count
     */
    public function updateLeechers(TorrentId $id, int $count): void
    {
        $torrent = $this->torrentRepository->findById($id);
        if ($torrent === null) {
            return;
        }

        $torrent->updateLeechers($count);
        $this->torrentRepository->save($torrent);
        $this->invalidateCache($id);
    }

    /**
     * Delete a torrent
     */
    public function deleteTorrent(TorrentId $id, UserId $deletedBy, string $reason): void
    {
        $torrent = $this->torrentRepository->findById($id);
        if ($torrent === null) {
            return;
        }

        // Log the deletion
        // Delete from repository
        $this->torrentRepository->delete($id);
        $this->invalidateCache($id);
    }

    /**
     * Get user's snatched torrents
     *
     * @return array<TorrentId>
     */
    public function getUserSnatches(UserId $userId, int $limit = 50, int $offset = 0): array
    {
        return $this->torrentRepository->findSnatchedByUser($userId, $limit, $offset);
    }

    /**
     * Get user's uploaded torrents
     *
     * @return array<TorrentDTO>
     */
    public function getUserUploads(UserId $userId, int $limit = 50, int $offset = 0): array
    {
        $torrents = $this->torrentRepository->findByUploader($userId, $limit, $offset);

        return array_map(
            fn(Torrent $t) => TorrentDTO::fromTorrent($t),
            $torrents
        );
    }

    /**
     * Get user's seeding torrents
     *
     * @return array<TorrentId>
     */
    public function getUserSeeding(UserId $userId): array
    {
        return $this->torrentRepository->findSeedingByUser($userId);
    }

    /**
     * Get user's leeching torrents
     *
     * @return array<TorrentId>
     */
    public function getUserLeeching(UserId $userId): array
    {
        return $this->torrentRepository->findLeechingByUser($userId);
    }

    /**
     * Search torrents
     *
     * @param array<string, mixed> $criteria
     * @return array<TorrentDTO>
     */
    public function search(array $criteria, int $limit = 50, int $offset = 0): array
    {
        $torrents = $this->torrentRepository->search($criteria, $limit, $offset);

        return array_map(
            fn(Torrent $t) => TorrentDTO::fromTorrent($t),
            $torrents
        );
    }

    /**
     * Invalidate torrent cache
     */
    public function invalidateCache(TorrentId $id): void
    {
        $this->cache->delete(sprintf(self::CACHE_TORRENT, $id->value()));
    }

    /**
     * Get torrent file path
     */
    public function getTorrentFilePath(TorrentId $id): ?string
    {
        $torrent = $this->torrentRepository->findById($id);
        if ($torrent === null) {
            return null;
        }

        // Return path to .torrent file
        return sprintf(
            '%s/%d.torrent',
            '/var/www/torrents',
            $id->value()
        );
    }
}
