<?php

declare(strict_types=1);

namespace Gazelle\Application\User\DTOs;

/**
 * Session DTO
 */
final readonly class SessionDTO implements \JsonSerializable
{
    public function __construct(
        public string $token,
        public int $userId,
        public string $expiresAt
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'token' => $this->token,
            'user_id' => $this->userId,
            'expires_at' => $this->expiresAt,
        ];
    }
}
