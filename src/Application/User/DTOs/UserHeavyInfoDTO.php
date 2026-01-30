<?php

declare(strict_types=1);

namespace Gazelle\Application\User\DTOs;

use Gazelle\Domain\User\User;

/**
 * User Heavy Info DTO
 *
 * Contains detailed user information for the logged-in user.
 * This is a heavier dataset than UserDTO, cached separately.
 */
final readonly class UserHeavyInfoDTO
{
    /**
     * @param array<string, bool> $customPermissions
     * @param array<string, mixed> $siteOptions
     * @param array<int, int>|null $customForums
     */
    public function __construct(
        public int $id,
        public int $invites,
        public string $torrentPass,
        public string $ip,
        public array $customPermissions,
        public bool $canLeech,
        public string $authKey,
        public ?string $ratioWatchEnds,
        public ?int $ratioWatchDownload,
        public int $styleId,
        public ?string $styleUrl,
        public bool $disableInvites,
        public bool $disablePosting,
        public bool $disableUpload,
        public bool $disableWiki,
        public bool $disableAvatar,
        public bool $disablePm,
        public bool $disablePoints,
        public bool $disablePromotion,
        public bool $disableRequests,
        public bool $disableForums,
        public bool $disableTagging,
        public array $siteOptions,
        public bool $downloadAlt,
        public ?int $lastReadNews,
        public ?int $lastReadBlog,
        public ?array $customForums,
        public int $flTokens,
        public int $bonusPoints,
        public int $hnr,
        public int $permissionId
    ) {}

    /**
     * Create from User entity
     */
    public static function fromUser(User $user): self
    {
        // This would need more user data than currently in the entity
        // For now, create with default values
        return new self(
            id: $user->id()->value(),
            invites: 0,
            torrentPass: '',
            ip: '',
            customPermissions: [],
            canLeech: true,
            authKey: '',
            ratioWatchEnds: null,
            ratioWatchDownload: null,
            styleId: 0,
            styleUrl: null,
            disableInvites: false,
            disablePosting: false,
            disableUpload: false,
            disableWiki: false,
            disableAvatar: false,
            disablePm: false,
            disablePoints: false,
            disablePromotion: false,
            disableRequests: false,
            disableForums: false,
            disableTagging: false,
            siteOptions: [],
            downloadAlt: false,
            lastReadNews: null,
            lastReadBlog: null,
            customForums: null,
            flTokens: 0,
            bonusPoints: $user->stats()->bonusPoints(),
            hnr: 0,
            permissionId: 0
        );
    }

    /**
     * Create from database row
     *
     * @param array<string, mixed> $row
     */
    public static function fromDatabaseRow(array $row): self
    {
        $customPermissions = [];
        if (!empty($row['CustomPermissions'])) {
            $unserialized = @unserialize($row['CustomPermissions']);
            if (is_array($unserialized)) {
                $customPermissions = $unserialized;
            }
        }

        $siteOptions = [];
        if (!empty($row['SiteOptions'])) {
            $unserialized = @unserialize($row['SiteOptions']);
            if (is_array($unserialized)) {
                $siteOptions = $unserialized;
            }
        }

        return new self(
            id: (int) $row['ID'],
            invites: (int) ($row['Invites'] ?? 0),
            torrentPass: (string) ($row['torrent_pass'] ?? ''),
            ip: (string) ($row['IP'] ?? ''),
            customPermissions: $customPermissions,
            canLeech: (bool) ($row['CanLeech'] ?? true),
            authKey: (string) ($row['AuthKey'] ?? ''),
            ratioWatchEnds: $row['RatioWatchEnds'] ?? null,
            ratioWatchDownload: $row['RatioWatchDownload'] !== null
                ? (int) $row['RatioWatchDownload']
                : null,
            styleId: (int) ($row['StyleID'] ?? 0),
            styleUrl: $row['StyleURL'] ?? null,
            disableInvites: (bool) ($row['DisableInvites'] ?? false),
            disablePosting: (bool) ($row['DisablePosting'] ?? false),
            disableUpload: (bool) ($row['DisableUpload'] ?? false),
            disableWiki: (bool) ($row['DisableWiki'] ?? false),
            disableAvatar: (bool) ($row['DisableAvatar'] ?? false),
            disablePm: (bool) ($row['DisablePM'] ?? false),
            disablePoints: (bool) ($row['DisablePoints'] ?? false),
            disablePromotion: (bool) ($row['DisablePromotion'] ?? false),
            disableRequests: (bool) ($row['DisableRequests'] ?? false),
            disableForums: (bool) ($row['DisableForums'] ?? false),
            disableTagging: (bool) ($row['DisableTagging'] ?? false),
            siteOptions: $siteOptions,
            downloadAlt: (bool) ($row['DownloadAlt'] ?? false),
            lastReadNews: $row['LastReadNews'] !== null
                ? (int) $row['LastReadNews']
                : null,
            lastReadBlog: $row['LastReadBlog'] !== null
                ? (int) $row['LastReadBlog']
                : null,
            customForums: $row['CustomForums'] ?? null,
            flTokens: (int) ($row['FLTokens'] ?? 0),
            bonusPoints: (int) ($row['BonusPoints'] ?? 0),
            hnr: (int) ($row['HnR'] ?? 0),
            permissionId: (int) ($row['PermissionID'] ?? 0)
        );
    }

    /**
     * Check if a specific feature is disabled
     */
    public function isDisabled(string $feature): bool
    {
        return match ($feature) {
            'invites' => $this->disableInvites,
            'posting' => $this->disablePosting,
            'upload' => $this->disableUpload,
            'wiki' => $this->disableWiki,
            'avatar' => $this->disableAvatar,
            'pm' => $this->disablePm,
            'points' => $this->disablePoints,
            'promotion' => $this->disablePromotion,
            'requests' => $this->disableRequests,
            'forums' => $this->disableForums,
            'tagging' => $this->disableTagging,
            default => false,
        };
    }

    /**
     * Get a site option value
     */
    public function getSiteOption(string $key, mixed $default = null): mixed
    {
        return $this->siteOptions[$key] ?? $default;
    }

    /**
     * Check if user can access a specific forum
     */
    public function canAccessForum(int $forumId, int $minLevel): bool
    {
        // Check custom forum access first
        if ($this->customForums !== null && isset($this->customForums[$forumId])) {
            return $this->customForums[$forumId] === 1;
        }

        // Default to level check
        return true; // Actual check would compare against user level
    }
}
