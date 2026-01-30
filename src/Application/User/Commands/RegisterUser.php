<?php

declare(strict_types=1);

namespace Gazelle\Application\User\Commands;

use Gazelle\Application\Common\Command;

/**
 * Register User Command
 */
final readonly class RegisterUser implements Command
{
    public function __construct(
        public string $username,
        public string $email,
        public string $password,
        public ?string $inviteCode = null
    ) {}
}
