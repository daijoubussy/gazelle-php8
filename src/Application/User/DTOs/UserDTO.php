<?php

declare(strict_types=1);

namespace Gazelle\Application\User\DTOs;

use Gazelle\Domain\User\User;
use Gazelle\Domain\User\ValueObjects\UserClass;

/**
 * User Data Transfer Object
 *
 * Immutable representation of user data for the application layer.
 */
final readonly class UserDTO implements \JsonSerializable
{
    public function __construct(
        public int $id,
        public string $username,
        public string $email,
        public string $class,
        public int $classLevel,
        public int $uploaded,
        public int $downloaded,
        public float $ratio,
        public int $bonusPoints,
        public bool $enabled,
        public ?string $lastLogin,
        public string $createdAt
    ) {}

    public static function fromEntity(User $user): self
    {
        $stats = $user->stats();

        return new self(
            id: (int) $user->userId()->value(),
            username: (string) $user->username(),
            email: (string) $user->email(),
            class: $user->class()->label(),
            classLevel: $user->class()->value,
            uploaded: $stats->uploaded(),
            downloaded: $stats->downloaded(),
            ratio: $stats->ratio(),
            bonusPoints: $stats->bonusPoints(),
            enabled: $user->isEnabled(),
            lastLogin: $user->lastLogin()?->format('c'),
            createdAt: $user->createdAt()->format('c')
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'class' => $this->class,
            'stats' => [
                'uploaded' => $this->uploaded,
                'downloaded' => $this->downloaded,
                'ratio' => $this->ratio === INF ? 'Inf' : round($this->ratio, 2),
                'bonus_points' => $this->bonusPoints,
            ],
            'enabled' => $this->enabled,
            'last_login' => $this->lastLogin,
            'created_at' => $this->createdAt,
        ];
    }
}
