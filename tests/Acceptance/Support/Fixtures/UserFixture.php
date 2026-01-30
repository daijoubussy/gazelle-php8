<?php

declare(strict_types=1);

namespace Gazelle\Tests\Acceptance\Support\Fixtures;

use Gazelle\Tests\Acceptance\Support\Helpers\TestDatabase;
use Gazelle\Tests\Acceptance\Support\Helpers\DeterministicFaker;

/**
 * User Fixture Factory
 *
 * Creates deterministic user records for testing.
 * All users are created with known, predictable values.
 */
final class UserFixture
{
    /** Default test password (used for all test users) */
    public const DEFAULT_PASSWORD = 'TestPassword123!';

    /** User class IDs */
    public const CLASS_USER = 1;
    public const CLASS_MEMBER = 2;
    public const CLASS_POWER_USER = 3;
    public const CLASS_ELITE = 4;
    public const CLASS_VIP = 5;
    public const CLASS_TORRENT_MASTER = 6;
    public const CLASS_MOD = 10;
    public const CLASS_ADMIN = 11;
    public const CLASS_SYSOP = 15;

    /** @var array<string, int> Role to class ID mapping */
    private const ROLE_MAP = [
        'user' => self::CLASS_USER,
        'member' => self::CLASS_MEMBER,
        'power_user' => self::CLASS_POWER_USER,
        'elite' => self::CLASS_ELITE,
        'vip' => self::CLASS_VIP,
        'torrent_master' => self::CLASS_TORRENT_MASTER,
        'moderator' => self::CLASS_MOD,
        'admin' => self::CLASS_ADMIN,
        'sysop' => self::CLASS_SYSOP,
    ];

    /**
     * Create a basic registered user
     *
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    public static function createRegisteredUser(
        TestDatabase $db,
        DeterministicFaker $faker,
        array $overrides = []
    ): array {
        $username = $overrides['username'] ?? $faker->username();
        $email = $overrides['email'] ?? $faker->email();
        $password = $overrides['password'] ?? self::DEFAULT_PASSWORD;

        $userId = $db->insert('users_main', [
            'Username' => $username,
            'Email' => $email,
            'PassHash' => password_hash($password, PASSWORD_DEFAULT),
            'Enabled' => '1',
            'Class' => self::CLASS_USER,
            'Uploaded' => $overrides['uploaded'] ?? 0,
            'Downloaded' => $overrides['downloaded'] ?? 0,
            'BonusPoints' => $overrides['bonus_points'] ?? 0,
            'Invites' => $overrides['invites'] ?? 0,
            'JoinDate' => $overrides['join_date'] ?? date('Y-m-d H:i:s'),
            'LastAccess' => $overrides['last_access'] ?? date('Y-m-d H:i:s'),
            'IP' => $overrides['ip'] ?? '127.0.0.1',
        ]);

        // Create user info record
        $db->insert('users_info', [
            'UserID' => $userId,
            'StyleID' => 1,
            'Avatar' => '',
            'AdminComment' => '',
            'Info' => '',
            'Paranoia' => serialize([]),
        ]);

        return [
            'id' => $userId,
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'enabled' => true,
            'class' => self::CLASS_USER,
        ];
    }

    /**
     * Create a user with specific role
     *
     * @return array<string, mixed>
     */
    public static function createUserWithRole(
        TestDatabase $db,
        DeterministicFaker $faker,
        string $role
    ): array {
        $classId = self::ROLE_MAP[strtolower($role)] ?? self::CLASS_USER;

        $user = self::createRegisteredUser($db, $faker);

        $db->update('users_main', ['Class' => $classId], ['ID' => $user['id']]);

        $user['class'] = $classId;
        $user['role'] = $role;

        return $user;
    }

    /**
     * Create a power user (has upload/download stats)
     *
     * @return array<string, mixed>
     */
    public static function createPowerUser(
        TestDatabase $db,
        DeterministicFaker $faker
    ): array {
        return self::createRegisteredUser($db, $faker, [
            'uploaded' => 50 * 1024 * 1024 * 1024, // 50 GB
            'downloaded' => 25 * 1024 * 1024 * 1024, // 25 GB
            'bonus_points' => 10000,
        ]);
    }

    /**
     * Create a disabled user
     *
     * @return array<string, mixed>
     */
    public static function createDisabledUser(
        TestDatabase $db,
        DeterministicFaker $faker
    ): array {
        $user = self::createRegisteredUser($db, $faker);

        $db->update('users_main', ['Enabled' => '0'], ['ID' => $user['id']]);

        $user['enabled'] = false;

        return $user;
    }

    /**
     * Create a banned user
     *
     * @return array<string, mixed>
     */
    public static function createBannedUser(
        TestDatabase $db,
        DeterministicFaker $faker
    ): array {
        $user = self::createRegisteredUser($db, $faker);

        $db->update('users_main', ['Enabled' => '2'], ['ID' => $user['id']]);

        $user['enabled'] = false;
        $user['banned'] = true;

        return $user;
    }

    /**
     * Create multiple users
     *
     * @return array<array<string, mixed>>
     */
    public static function createMany(
        TestDatabase $db,
        DeterministicFaker $faker,
        int $count
    ): array {
        $users = [];

        for ($i = 0; $i < $count; $i++) {
            $users[] = self::createRegisteredUser($db, $faker);
        }

        return $users;
    }

    /**
     * Create user with specific upload/download ratio
     *
     * @return array<string, mixed>
     */
    public static function createUserWithRatio(
        TestDatabase $db,
        DeterministicFaker $faker,
        float $ratio
    ): array {
        $downloaded = 10 * 1024 * 1024 * 1024; // 10 GB base
        $uploaded = (int) ($downloaded * $ratio);

        return self::createRegisteredUser($db, $faker, [
            'uploaded' => $uploaded,
            'downloaded' => $downloaded,
        ]);
    }
}
