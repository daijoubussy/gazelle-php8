<?php

declare(strict_types=1);

namespace Gazelle\Infrastructure\Persistence;

use Gazelle\Domain\Common\AggregateRoot;
use Gazelle\Domain\Common\EntityId;
use Gazelle\Domain\User\User;
use Gazelle\Domain\User\UserId;
use Gazelle\Domain\User\UserNotFoundException;
use Gazelle\Domain\User\UserRepository;
use Gazelle\Domain\User\ValueObjects\Email;
use Gazelle\Domain\User\ValueObjects\UserClass;
use Gazelle\Domain\User\ValueObjects\Username;

/**
 * User Repository Implementation
 *
 * Database implementation of the UserRepository interface.
 */
final readonly class UserRepositoryImpl implements UserRepository
{
    public function __construct(
        private DatabaseConnection $db
    ) {}

    public function findById(EntityId|UserId $id): ?User
    {
        $data = $this->db->fetchOne(
            'SELECT * FROM users WHERE id = :id',
            ['id' => $id->value()]
        );

        if ($data === null) {
            return null;
        }

        return User::fromPersistence($data);
    }

    public function getById(EntityId|UserId $id): User
    {
        $user = $this->findById($id);

        if ($user === null) {
            throw UserNotFoundException::withId($id instanceof UserId ? $id : UserId::fromInt((int) $id->value()));
        }

        return $user;
    }

    public function findByUsername(Username $username): ?User
    {
        $data = $this->db->fetchOne(
            'SELECT * FROM users WHERE username = :username',
            ['username' => (string) $username]
        );

        if ($data === null) {
            return null;
        }

        return User::fromPersistence($data);
    }

    public function findByEmail(Email $email): ?User
    {
        $data = $this->db->fetchOne(
            'SELECT * FROM users WHERE email = :email',
            ['email' => (string) $email]
        );

        if ($data === null) {
            return null;
        }

        return User::fromPersistence($data);
    }

    public function usernameExists(Username $username): bool
    {
        $count = $this->db->fetchColumn(
            'SELECT COUNT(*) FROM users WHERE username = :username',
            ['username' => (string) $username]
        );

        return (int) $count > 0;
    }

    public function emailExists(Email $email): bool
    {
        $count = $this->db->fetchColumn(
            'SELECT COUNT(*) FROM users WHERE email = :email',
            ['email' => (string) $email]
        );

        return (int) $count > 0;
    }

    public function nextId(): UserId
    {
        // For MySQL with auto-increment, we'll get the ID after insert
        // This is a placeholder that will be replaced by the actual ID
        $id = $this->db->fetchColumn(
            'SELECT COALESCE(MAX(id), 0) + 1 FROM users'
        );

        return UserId::fromInt((int) $id);
    }

    public function save(AggregateRoot|User $aggregate): void
    {
        $data = $aggregate->toPersistence();

        $exists = $this->db->fetchColumn(
            'SELECT 1 FROM users WHERE id = :id',
            ['id' => $data['id']]
        );

        if ($exists) {
            $this->update($data);
        } else {
            $this->insert($data);
        }

        $aggregate->incrementVersion();
    }

    public function remove(AggregateRoot|User $aggregate): void
    {
        $this->db->execute(
            'DELETE FROM users WHERE id = :id',
            ['id' => $aggregate->userId()->value()]
        );
    }

    public function findByClass(UserClass $class): iterable
    {
        $rows = $this->db->fetchAll(
            'SELECT * FROM users WHERE class = :class ORDER BY username',
            ['class' => $class->value]
        );

        foreach ($rows as $row) {
            yield User::fromPersistence($row);
        }
    }

    public function findEligibleForPromotion(): iterable
    {
        // Find users who meet the requirements for their next class
        $rows = $this->db->fetchAll(<<<SQL
            SELECT * FROM users
            WHERE enabled = 1
            AND class < :maxClass
            ORDER BY uploaded DESC
            LIMIT 1000
        SQL, ['maxClass' => UserClass::TorrentMaster->value]);

        foreach ($rows as $row) {
            $user = User::fromPersistence($row);
            if ($user->canPromote()) {
                yield $user;
            }
        }
    }

    public function count(): int
    {
        return (int) $this->db->fetchColumn('SELECT COUNT(*) FROM users');
    }

    public function countEnabled(): int
    {
        return (int) $this->db->fetchColumn(
            'SELECT COUNT(*) FROM users WHERE enabled = 1'
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function insert(array $data): void
    {
        $this->db->insert(<<<SQL
            INSERT INTO users (
                id, username, email, password_hash, class,
                uploaded, downloaded, bonus_points, enabled,
                last_login, created_at, updated_at, version
            ) VALUES (
                :id, :username, :email, :password_hash, :class,
                :uploaded, :downloaded, :bonus_points, :enabled,
                :last_login, :created_at, :updated_at, :version
            )
        SQL, $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function update(array $data): void
    {
        $this->db->execute(<<<SQL
            UPDATE users SET
                username = :username,
                email = :email,
                password_hash = :password_hash,
                class = :class,
                uploaded = :uploaded,
                downloaded = :downloaded,
                bonus_points = :bonus_points,
                enabled = :enabled,
                last_login = :last_login,
                updated_at = :updated_at,
                version = :version
            WHERE id = :id
        SQL, $data);
    }
}
