<?php

declare(strict_types=1);

namespace Gazelle\Application\User\Services;

use Gazelle\Domain\Permission\Permission;
use Gazelle\Domain\Permission\PermissionId;
use Gazelle\Domain\Permission\PermissionRepository;
use Gazelle\Domain\Permission\PermissionSet;
use Gazelle\Infrastructure\Cache\CacheInterface;

/**
 * Permission Service
 *
 * Application service for permission operations.
 * Replaces legacy Permissions class and Users::get_classes().
 */
final class PermissionService
{
    private const CACHE_CLASSES = 'classes';
    private const CACHE_PERMISSIONS = 'permissions_%d';
    private const CACHE_DURATION = 0; // Permanent

    /** @var array<int, Permission>|null */
    private ?array $classesById = null;

    /** @var array<int, Permission>|null */
    private ?array $classesByLevel = null;

    public function __construct(
        private readonly PermissionRepository $permissionRepository,
        private readonly CacheInterface $cache
    ) {}

    /**
     * Get all classes indexed by ID and Level
     * Replaces Users::get_classes()
     *
     * @return array{0: array<int, Permission>, 1: array<int, Permission>}
     */
    public function getClasses(): array
    {
        if ($this->classesById !== null && $this->classesByLevel !== null) {
            return [$this->classesById, $this->classesByLevel];
        }

        $cached = $this->cache->get(self::CACHE_CLASSES);
        if (is_array($cached) && count($cached) === 2) {
            [$this->classesById, $this->classesByLevel] = $cached;
            return $cached;
        }

        $this->classesById = $this->permissionRepository->findAllById();
        $this->classesByLevel = $this->permissionRepository->findAllByLevel();

        $this->cache->set(
            self::CACHE_CLASSES,
            [$this->classesById, $this->classesByLevel],
            self::CACHE_DURATION
        );

        return [$this->classesById, $this->classesByLevel];
    }

    /**
     * Get a permission class by ID
     */
    public function getClass(int $classId): ?Permission
    {
        [$classes, ] = $this->getClasses();
        return $classes[$classId] ?? null;
    }

    /**
     * Get class name by ID
     */
    public function getClassName(int $classId): string
    {
        $class = $this->getClass($classId);
        return $class?->name() ?? 'Unknown';
    }

    /**
     * Get class abbreviation by ID
     */
    public function getClassAbbreviation(int $classId): string
    {
        $class = $this->getClass($classId);
        return $class?->abbreviation() ?? '?';
    }

    /**
     * Get class level by ID
     */
    public function getClassLevel(int $classId): int
    {
        $class = $this->getClass($classId);
        return $class?->level() ?? 0;
    }

    /**
     * Get permissions for a class
     * Replaces Permissions::get_permissions()
     */
    public function getPermissions(int $classId): PermissionSet
    {
        $cacheKey = sprintf(self::CACHE_PERMISSIONS, $classId);

        $cached = $this->cache->get($cacheKey);
        if ($cached instanceof PermissionSet) {
            return $cached;
        }

        $permission = $this->permissionRepository->findById(
            PermissionId::fromInt($classId)
        );

        if ($permission === null) {
            return PermissionSet::empty();
        }

        $permissions = $permission->permissions();
        $this->cache->set($cacheKey, $permissions, self::CACHE_DURATION);

        return $permissions;
    }

    /**
     * Check if a class has a specific permission
     *
     * @param array<string, bool>|null $customPermissions
     */
    public function hasPermission(
        int $classId,
        string $permission,
        ?array $customPermissions = null
    ): bool {
        // Check custom permissions first
        if ($customPermissions !== null && isset($customPermissions[$permission])) {
            return $customPermissions[$permission];
        }

        $permissions = $this->getPermissions($classId);
        return $permissions->has($permission);
    }

    /**
     * Get effective permissions for a user
     *
     * @param int $primaryClassId
     * @param array<int> $secondaryClassIds
     * @param array<string, bool>|null $customPermissions
     */
    public function getEffectivePermissions(
        int $primaryClassId,
        array $secondaryClassIds = [],
        ?array $customPermissions = null
    ): PermissionSet {
        $permissions = $this->getPermissions($primaryClassId);

        // Merge secondary class permissions
        foreach ($secondaryClassIds as $classId) {
            $secondary = $this->getPermissions($classId);
            $permissions = $permissions->merge($secondary);
        }

        // Apply custom permissions
        if ($customPermissions !== null) {
            $permissions = $permissions->withCustom($customPermissions);
        }

        return $permissions;
    }

    /**
     * Calculate effective class level
     *
     * @param int $primaryClassId
     * @param array<int> $secondaryClassIds
     */
    public function getEffectiveLevel(
        int $primaryClassId,
        array $secondaryClassIds = []
    ): int {
        $level = $this->getClassLevel($primaryClassId);

        foreach ($secondaryClassIds as $classId) {
            $secondaryLevel = $this->getClassLevel($classId);
            $level = max($level, $secondaryLevel);
        }

        return $level;
    }

    /**
     * Check if user is at least a certain class level
     */
    public function isAtLeast(int $userLevel, int $requiredLevel): bool
    {
        return $userLevel >= $requiredLevel;
    }

    /**
     * Invalidate permission cache
     */
    public function invalidateCache(): void
    {
        $this->cache->delete(self::CACHE_CLASSES);
        $this->classesById = null;
        $this->classesByLevel = null;
    }
}
