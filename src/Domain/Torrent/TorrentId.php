<?php

declare(strict_types=1);

namespace Gazelle\Domain\Torrent;

use Gazelle\Domain\Common\EntityId;

/**
 * Torrent ID Value Object
 */
final readonly class TorrentId extends EntityId
{
    public static function fromInt(int $id): self
    {
        return new self($id);
    }

    protected function validate(int|string $value): void
    {
        if (!is_int($value) || $value < 1) {
            throw new \InvalidArgumentException('Torrent ID must be a positive integer');
        }
    }
}
