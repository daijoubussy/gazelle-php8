<?php

declare(strict_types=1);

namespace Gazelle\Domain\User;

use Gazelle\Domain\Common\Repository;
use Gazelle\Domain\User\ValueObjects\Email;
use Gazelle\Domain\User\ValueObjects\Username;

/**
 * User Repository Interface
 *
 * Defines the contract for user persistence operations.
 * Implementations go in the Infrastructure layer.
 *
 * @extends Repository<User>
 */
interface UserRepository extends Repository
{
    /**
     * Find a user by ID
     */
    public function findById(UserId $id): ?User;

    /**
     * Get a user by ID or throw
     *
     * @throws UserNotFoundException
     */
    public function getById(UserId $id): User;

    /**
     * Find a user by username
     */
    public function findByUsername(Username $username): ?User;

    /**
     * Find a user by email
     */
    public function findByEmail(Email $email): ?User;

    /**
     * Check if a username exists
     */
    public function usernameExists(Username $username): bool;

    /**
     * Check if an email exists
     */
    public function emailExists(Email $email): bool;

    /**
     * Get the next available user ID
     */
    public function nextId(): UserId;

    /**
     * Find users by class
     *
     * @return iterable<User>
     */
    public function findByClass(ValueObjects\UserClass $class): iterable;

    /**
     * Find users eligible for promotion
     *
     * @return iterable<User>
     */
    public function findEligibleForPromotion(): iterable;

    /**
     * Count total users
     */
    public function count(): int;

    /**
     * Count enabled users
     */
    public function countEnabled(): int;
}
