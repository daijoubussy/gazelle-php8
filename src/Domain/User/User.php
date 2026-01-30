<?php

declare(strict_types=1);

namespace Gazelle\Domain\User;

use Gazelle\Domain\Common\AggregateRoot;
use Gazelle\Domain\Common\EntityId;
use Gazelle\Domain\User\Events\UserCreated;
use Gazelle\Domain\User\Events\UserPasswordChanged;
use Gazelle\Domain\User\Events\UserPromoted;
use Gazelle\Domain\User\ValueObjects\Email;
use Gazelle\Domain\User\ValueObjects\Password;
use Gazelle\Domain\User\ValueObjects\TransferStats;
use Gazelle\Domain\User\ValueObjects\UserClass;
use Gazelle\Domain\User\ValueObjects\Username;

/**
 * User Aggregate Root
 *
 * Represents a tracker user with their identity, credentials,
 * and transfer statistics.
 */
final class User extends AggregateRoot
{
    private function __construct(
        private readonly UserId $id,
        private Username $username,
        private Email $email,
        private Password $password,
        private UserClass $class,
        private TransferStats $stats,
        private bool $enabled,
        private ?\DateTimeImmutable $lastLogin,
        private readonly \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt
    ) {}

    /**
     * Create a new user
     */
    public static function create(
        UserId $id,
        Username $username,
        Email $email,
        Password $password,
        UserClass $class = UserClass::User
    ): self {
        $now = new \DateTimeImmutable();

        $user = new self(
            id: $id,
            username: $username,
            email: $email,
            password: $password,
            class: $class,
            stats: TransferStats::zero(),
            enabled: true,
            lastLogin: null,
            createdAt: $now,
            updatedAt: $now
        );

        $user->recordEvent(new UserCreated($id, $username, $email));

        return $user;
    }

    /**
     * Hydrate from persistence
     *
     * @param array<string, mixed> $data
     */
    public static function fromPersistence(array $data): self
    {
        $user = new self(
            id: UserId::fromInt((int) $data['id']),
            username: Username::fromString($data['username']),
            email: Email::fromString($data['email']),
            password: Password::fromHash($data['password_hash']),
            class: UserClass::from((int) $data['class']),
            stats: TransferStats::create(
                (int) $data['uploaded'],
                (int) $data['downloaded'],
                (int) $data['bonus_points']
            ),
            enabled: (bool) $data['enabled'],
            lastLogin: $data['last_login']
                ? new \DateTimeImmutable($data['last_login'])
                : null,
            createdAt: new \DateTimeImmutable($data['created_at']),
            updatedAt: new \DateTimeImmutable($data['updated_at'])
        );

        $user->setVersion((int) ($data['version'] ?? 0));

        return $user;
    }

    public function id(): EntityId
    {
        return $this->id;
    }

    public function userId(): UserId
    {
        return $this->id;
    }

    public function username(): Username
    {
        return $this->username;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function class(): UserClass
    {
        return $this->class;
    }

    public function stats(): TransferStats
    {
        return $this->stats;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function lastLogin(): ?\DateTimeImmutable
    {
        return $this->lastLogin;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Verify password
     */
    public function verifyPassword(string $plaintext): bool
    {
        return $this->password->verify($plaintext);
    }

    /**
     * Change password
     */
    public function changePassword(Password $newPassword): void
    {
        $this->password = $newPassword;
        $this->touch();

        $this->recordEvent(new UserPasswordChanged($this->id));
    }

    /**
     * Update email
     */
    public function updateEmail(Email $email): void
    {
        $this->email = $email;
        $this->touch();
    }

    /**
     * Promote to a higher class
     */
    public function promoteTo(UserClass $newClass): void
    {
        if ($newClass->value <= $this->class->value) {
            throw new \InvalidArgumentException('Can only promote to a higher class');
        }

        $oldClass = $this->class;
        $this->class = $newClass;
        $this->touch();

        $this->recordEvent(new UserPromoted($this->id, $oldClass, $newClass));
    }

    /**
     * Record a login
     */
    public function recordLogin(): void
    {
        $this->lastLogin = new \DateTimeImmutable();
        $this->touch();
    }

    /**
     * Add upload stats
     */
    public function addUpload(int $bytes): void
    {
        $this->stats = $this->stats->withAddedUpload($bytes);
        $this->touch();
    }

    /**
     * Add download stats
     */
    public function addDownload(int $bytes): void
    {
        $this->stats = $this->stats->withAddedDownload($bytes);
        $this->touch();
    }

    /**
     * Add bonus points
     */
    public function addBonusPoints(int $points): void
    {
        $this->stats = $this->stats->withAddedBonusPoints($points);
        $this->touch();
    }

    /**
     * Spend bonus points
     */
    public function spendBonusPoints(int $points): void
    {
        $this->stats = $this->stats->withSpentBonusPoints($points);
        $this->touch();
    }

    /**
     * Disable the account
     */
    public function disable(): void
    {
        $this->enabled = false;
        $this->touch();
    }

    /**
     * Enable the account
     */
    public function enable(): void
    {
        $this->enabled = true;
        $this->touch();
    }

    /**
     * Check if user can be promoted to the next class
     */
    public function canPromote(): bool
    {
        $nextClass = $this->getNextClass();

        if ($nextClass === null) {
            return false;
        }

        return $this->stats->uploaded() >= $nextClass->uploadRequirement()
            && $this->stats->hasRequiredRatio($nextClass->ratioRequirement());
    }

    /**
     * Check if user has permission
     */
    public function hasPermission(UserClass $required): bool
    {
        return $this->class->canPerform($required);
    }

    /**
     * Check if user is staff
     */
    public function isStaff(): bool
    {
        return $this->class->isStaff();
    }

    /**
     * Get password hash for persistence
     */
    public function passwordHash(): string
    {
        return $this->password->hash();
    }

    /**
     * Convert to persistence format
     *
     * @return array<string, mixed>
     */
    public function toPersistence(): array
    {
        return [
            'id' => $this->id->value(),
            'username' => (string) $this->username,
            'email' => (string) $this->email,
            'password_hash' => $this->password->hash(),
            'class' => $this->class->value,
            'uploaded' => $this->stats->uploaded(),
            'downloaded' => $this->stats->downloaded(),
            'bonus_points' => $this->stats->bonusPoints(),
            'enabled' => $this->enabled,
            'last_login' => $this->lastLogin?->format('Y-m-d H:i:s'),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
            'version' => $this->version(),
        ];
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    private function getNextClass(): ?UserClass
    {
        return match ($this->class) {
            UserClass::User => UserClass::Member,
            UserClass::Member => UserClass::PowerUser,
            UserClass::PowerUser => UserClass::Elite,
            UserClass::Elite => UserClass::TorrentMaster,
            default => null,
        };
    }
}
