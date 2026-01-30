<?php

declare(strict_types=1);

namespace Gazelle\Domain\Permission;

/**
 * Permission Repository Interface
 */
interface PermissionRepository
{
    /**
     * Find a permission by ID
     */
    public function findById(PermissionId $id): ?Permission;

    /**
     * Find a permission by level
     */
    public function findByLevel(int $level): ?Permission;

    /**
     * Get all permissions
     *
     * @return array<Permission>
     */
    public function findAll(): array;

    /**
     * Get all permissions indexed by ID
     *
     * @return array<int, Permission>
     */
    public function findAllById(): array;

    /**
     * Get all permissions indexed by level
     *
     * @return array<int, Permission>
     */
    public function findAllByLevel(): array;

    /**
     * Get primary (non-secondary) classes
     *
     * @return array<Permission>
     */
    public function findPrimaryClasses(): array;

    /**
     * Get secondary classes
     *
     * @return array<Permission>
     */
    public function findSecondaryClasses(): array;

    /**
     * Save a permission
     */
    public function save(Permission $permission): void;
}
