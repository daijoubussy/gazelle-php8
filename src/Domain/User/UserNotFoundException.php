<?php

declare(strict_types=1);

namespace Gazelle\Domain\User;

use Gazelle\Domain\Common\EntityNotFoundException;
use Gazelle\Domain\User\ValueObjects\Email;
use Gazelle\Domain\User\ValueObjects\Username;

/**
 * User Not Found Exception
 */
final class UserNotFoundException extends EntityNotFoundException
{
    public static function withId(UserId $id): self
    {
        return new self("User not found with ID: {$id}");
    }

    public static function withUsername(Username $username): self
    {
        return new self("User not found with username: {$username}");
    }

    public static function withEmail(Email $email): self
    {
        return new self("User not found with email: {$email}");
    }
}
