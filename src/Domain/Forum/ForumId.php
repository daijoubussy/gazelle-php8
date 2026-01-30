<?php

declare(strict_types=1);

namespace Gazelle\Domain\Forum;

use Gazelle\Domain\Common\EntityId;

/**
 * Forum ID Value Object
 */
final readonly class ForumId extends EntityId
{
    public static function fromInt(int $id): self
    {
        return new self($id);
    }
}
