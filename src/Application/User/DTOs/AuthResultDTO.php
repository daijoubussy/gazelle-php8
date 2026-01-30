<?php

declare(strict_types=1);

namespace Gazelle\Application\User\DTOs;

/**
 * Authentication Result DTO
 */
final readonly class AuthResultDTO implements \JsonSerializable
{
    private function __construct(
        public bool $success,
        public ?SessionDTO $session,
        public ?string $error
    ) {}

    public static function success(SessionDTO $session): self
    {
        return new self(true, $session, null);
    }

    public static function failed(string $error): self
    {
        return new self(false, null, $error);
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        if ($this->success) {
            return [
                'success' => true,
                'session' => $this->session,
            ];
        }

        return [
            'success' => false,
            'error' => $this->error,
        ];
    }
}
