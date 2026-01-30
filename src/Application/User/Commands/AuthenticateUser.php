<?php

declare(strict_types=1);

namespace Gazelle\Application\User\Commands;

use Gazelle\Application\Common\Command;

/**
 * Authenticate User Command
 */
final readonly class AuthenticateUser implements Command
{
    public function __construct(
        public string $username,
        public string $password,
        public string $ipAddress,
        public string $userAgent
    ) {}
}
