<?php

declare(strict_types=1);

namespace Gazelle\Domain\Permission;

use Gazelle\Domain\Common\Entity;

/**
 * Permission Entity
 *
 * Represents a permission class (user level/role).
 */
final class Permission extends Entity
{
    private function __construct(
        private readonly PermissionId $id,
        private readonly string $name,
        private readonly string $abbreviation,
        private readonly int $level,
        private readonly bool $isSecondary,
        private readonly PermissionSet $permissions,
        private readonly ?string $permittedForums
    ) {}

    public static function create(
        PermissionId $id,
        string $name,
        string $abbreviation,
        int $level,
        bool $isSecondary = false,
        ?PermissionSet $permissions = null,
        ?string $permittedForums = null
    ): self {
        return new self(
            $id,
            $name,
            $abbreviation,
            $level,
            $isSecondary,
            $permissions ?? PermissionSet::empty(),
            $permittedForums
        );
    }

    /**
     * Reconstitute from database
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $permissions = PermissionSet::empty();

        if (!empty($data['Values'])) {
            $values = is_string($data['Values'])
                ? unserialize($data['Values'])
                : $data['Values'];

            if (is_array($values)) {
                $permissions = PermissionSet::fromArray($values);
            }
        }

        return new self(
            PermissionId::fromInt((int) $data['ID']),
            (string) $data['Name'],
            (string) ($data['Abbreviation'] ?? ''),
            (int) $data['Level'],
            (bool) ($data['Secondary'] ?? false),
            $permissions,
            $data['PermittedForums'] ?? null
        );
    }

    public function id(): PermissionId
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function abbreviation(): string
    {
        return $this->abbreviation;
    }

    public function level(): int
    {
        return $this->level;
    }

    public function isSecondary(): bool
    {
        return $this->isSecondary;
    }

    public function permissions(): PermissionSet
    {
        return $this->permissions;
    }

    public function permittedForums(): ?string
    {
        return $this->permittedForums;
    }

    /**
     * Check if this permission level can perform an action
     */
    public function can(string $permission): bool
    {
        return $this->permissions->has($permission);
    }

    /**
     * Check if this level is at least the given level
     */
    public function isAtLeast(int $level): bool
    {
        return $this->level >= $level;
    }

    /**
     * Check if this level outranks another
     */
    public function outranks(Permission $other): bool
    {
        return $this->level > $other->level;
    }
}
