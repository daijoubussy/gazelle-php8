<?php

declare(strict_types=1);

namespace Gazelle\Domain\Permission;

use Gazelle\Domain\Common\EntityId;

/**
 * Permission ID Value Object
 */
final readonly class PermissionId extends EntityId
{
    public static function fromInt(int $id): self
    {
        return new self($id);
    }
}
