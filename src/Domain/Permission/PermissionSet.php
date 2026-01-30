<?php

declare(strict_types=1);

namespace Gazelle\Domain\Permission;

use Gazelle\Domain\Common\ValueObject;

/**
 * Permission Set Value Object
 *
 * Represents a set of granted permissions.
 */
final readonly class PermissionSet extends ValueObject
{
    /**
     * @param array<string, bool> $permissions
     */
    private function __construct(
        private array $permissions
    ) {}

    /**
     * Create an empty permission set
     */
    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * Create from array of permission => bool
     *
     * @param array<string, bool> $permissions
     */
    public static function fromArray(array $permissions): self
    {
        return new self(
            array_filter($permissions, fn($v) => $v === true)
        );
    }

    /**
     * Check if permission is granted
     */
    public function has(string $permission): bool
    {
        return $this->permissions[$permission] ?? false;
    }

    /**
     * Get all granted permissions
     *
     * @return array<string>
     */
    public function granted(): array
    {
        return array_keys(array_filter($this->permissions));
    }

    /**
     * Add a permission
     */
    public function with(string $permission): self
    {
        return new self([...$this->permissions, $permission => true]);
    }

    /**
     * Remove a permission
     */
    public function without(string $permission): self
    {
        $permissions = $this->permissions;
        unset($permissions[$permission]);
        return new self($permissions);
    }

    /**
     * Merge with another permission set
     */
    public function merge(PermissionSet $other): self
    {
        return new self([...$this->permissions, ...$other->permissions]);
    }

    /**
     * Override with custom permissions
     *
     * @param array<string, bool> $custom
     */
    public function withCustom(array $custom): self
    {
        $permissions = $this->permissions;

        foreach ($custom as $perm => $value) {
            if ($value) {
                $permissions[$perm] = true;
            } else {
                unset($permissions[$perm]);
            }
        }

        return new self($permissions);
    }

    /**
     * Serialize for database storage
     */
    public function serialize(): string
    {
        return serialize($this->permissions);
    }

    /**
     * Get raw array
     *
     * @return array<string, bool>
     */
    public function toArray(): array
    {
        return $this->permissions;
    }

    public function equals(ValueObject $other): bool
    {
        if (!$other instanceof self) {
            return false;
        }

        return $this->granted() === $other->granted();
    }
}
