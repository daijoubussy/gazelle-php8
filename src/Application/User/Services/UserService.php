<?php

declare(strict_types=1);

namespace Gazelle\Application\User\Services;

use Gazelle\Application\User\DTOs\UserDTO;
use Gazelle\Application\User\DTOs\UserHeavyInfoDTO;
use Gazelle\Domain\User\User;
use Gazelle\Domain\User\UserId;
use Gazelle\Domain\User\UserRepository;
use Gazelle\Domain\User\ValueObjects\Email;
use Gazelle\Domain\User\ValueObjects\Password;
use Gazelle\Domain\User\ValueObjects\Username;
use Gazelle\Infrastructure\Cache\CacheInterface;

/**
 * User Service
 *
 * Application service for user operations.
 * Replaces legacy Users static methods.
 */
final readonly class UserService
{
    private const CACHE_USER_INFO = 'user_info_%d';
    private const CACHE_USER_HEAVY = 'user_info_heavy_%d';
    private const CACHE_DURATION = 2592000; // 30 days

    public function __construct(
        private UserRepository $userRepository,
        private CacheInterface $cache,
        private PermissionService $permissionService
    ) {}

    /**
     * Get basic user info
     * Replaces Users::user_info()
     */
    public function getUserInfo(UserId $userId): ?UserDTO
    {
        $cacheKey = sprintf(self::CACHE_USER_INFO, $userId->value());

        $cached = $this->cache->get($cacheKey);
        if ($cached instanceof UserDTO) {
            return $cached;
        }

        $user = $this->userRepository->findById($userId);
        if ($user === null) {
            return null;
        }

        $dto = UserDTO::fromUser($user);
        $this->cache->set($cacheKey, $dto, self::CACHE_DURATION);

        return $dto;
    }

    /**
     * Get heavy user info (for logged-in user)
     * Replaces Users::user_heavy_info()
     */
    public function getUserHeavyInfo(UserId $userId): ?UserHeavyInfoDTO
    {
        $cacheKey = sprintf(self::CACHE_USER_HEAVY, $userId->value());

        $cached = $this->cache->get($cacheKey);
        if ($cached instanceof UserHeavyInfoDTO) {
            return $cached;
        }

        $user = $this->userRepository->findById($userId);
        if ($user === null) {
            return null;
        }

        $dto = UserHeavyInfoDTO::fromUser($user);
        $this->cache->set($cacheKey, $dto, self::CACHE_DURATION);

        return $dto;
    }

    /**
     * Invalidate user cache
     */
    public function invalidateUserCache(UserId $userId): void
    {
        $this->cache->delete(sprintf(self::CACHE_USER_INFO, $userId->value()));
        $this->cache->delete(sprintf(self::CACHE_USER_HEAVY, $userId->value()));
    }

    /**
     * Verify a password
     * Replaces Users::check_password()
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        if ($password === '' || $hash === '') {
            return false;
        }

        $prepared = str_replace("\0", '', hash('sha512', $password, true));
        return password_verify($prepared, $hash);
    }

    /**
     * Create a secure password hash
     * Replaces Users::make_sec_hash()
     */
    public function hashPassword(string $password): string
    {
        $prepared = str_replace("\0", '', hash('sha512', $password, true));
        return password_hash($prepared, PASSWORD_DEFAULT);
    }

    /**
     * Generate a random secret string
     * Replaces Users::make_secret()
     */
    public function generateSecret(int $length = 32): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $secret = '';

        for ($i = 0; $i < $length; $i++) {
            $secret .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return str_shuffle($secret);
    }

    /**
     * Format username for display
     * Replaces Users::format_username()
     */
    public function formatUsername(
        UserId $userId,
        bool $showBadges = false,
        bool $showWarned = true,
        bool $showEnabled = true,
        bool $showClass = false,
        bool $showTitle = false
    ): string {
        if ($userId->value() === 0) {
            return 'System';
        }

        $userInfo = $this->getUserInfo($userId);
        if ($userInfo === null || $userInfo->username === '') {
            return "Unknown [{$userId->value()}]";
        }

        $html = sprintf(
            '<a href="/user.php?id=%d">%s</a>',
            $userId->value(),
            htmlspecialchars($userInfo->username, ENT_QUOTES)
        );

        if ($showClass) {
            $className = $this->permissionService->getClassName($userInfo->permissionId);
            $html .= ' (' . htmlspecialchars($className, ENT_QUOTES) . ')';
        }

        if ($showTitle && $userInfo->title !== '') {
            $html .= ' <span class="user_title">(' . $userInfo->title . ')</span>';
        }

        return $html;
    }

    /**
     * Check if user has a specific permission
     */
    public function userCan(UserId $userId, string $permission): bool
    {
        $userInfo = $this->getUserInfo($userId);
        if ($userInfo === null) {
            return false;
        }

        return $this->permissionService->hasPermission(
            $userInfo->permissionId,
            $permission,
            $userInfo->customPermissions
        );
    }

    /**
     * Get user's effective class level
     */
    public function getEffectiveClass(UserId $userId): int
    {
        $userInfo = $this->getUserInfo($userId);
        if ($userInfo === null) {
            return 0;
        }

        return $userInfo->effectiveClass;
    }

    /**
     * Update user's site options
     * Replaces Users::update_site_options()
     *
     * @param array<string, mixed> $options
     */
    public function updateSiteOptions(UserId $userId, array $options): void
    {
        if (empty($options)) {
            return;
        }

        $user = $this->userRepository->findById($userId);
        if ($user === null) {
            return;
        }

        // Update and save
        // Implementation depends on User entity structure
        $this->userRepository->save($user);
        $this->invalidateUserCache($userId);
    }
}
