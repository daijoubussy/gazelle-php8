<?php

declare(strict_types=1);

namespace Gazelle\Domain\Common;

/**
 * Entity Not Found Exception
 *
 * Thrown when an entity cannot be found in a repository.
 */
final class EntityNotFoundException extends \DomainException
{
    public static function withId(string $entityType, EntityId $id): self
    {
        return new self("{$entityType} not found with ID: {$id}");
    }

    public static function withCriteria(string $entityType, string $criteria): self
    {
        return new self("{$entityType} not found matching: {$criteria}");
    }
}
