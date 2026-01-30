<?php

declare(strict_types=1);

namespace Gazelle\Domain\User;

use Gazelle\Domain\Common\EntityId;

/**
 * User ID Value Object
 */
final readonly class UserId extends EntityId
{
    public static function fromInt(int $id): self
    {
        return new self($id);
    }

    protected function validate(int|string $value): void
    {
        if (!is_int($value) || $value < 1) {
            throw new \InvalidArgumentException('User ID must be a positive integer');
        }
    }
}
