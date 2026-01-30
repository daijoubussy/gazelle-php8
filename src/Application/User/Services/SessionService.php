<?php

declare(strict_types=1);

namespace Gazelle\Application\User\Services;

use Gazelle\Application\User\DTOs\SessionDTO;
use Gazelle\Domain\User\UserId;

/**
 * Session Service Interface
 *
 * Manages user sessions and authentication tokens.
 */
interface SessionService
{
    /**
     * Create a new session
     */
    public function create(UserId $userId, string $ipAddress, string $userAgent): SessionDTO;

    /**
     * Validate a session token
     */
    public function validate(string $token): ?UserId;

    /**
     * Invalidate a session
     */
    public function invalidate(string $token): void;

    /**
     * Invalidate all sessions for a user
     */
    public function invalidateAll(UserId $userId): void;
}
