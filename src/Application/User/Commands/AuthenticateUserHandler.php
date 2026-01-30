<?php

declare(strict_types=1);

namespace Gazelle\Application\User\Commands;

use Gazelle\Application\Common\Command;
use Gazelle\Application\Common\CommandHandler;
use Gazelle\Application\User\DTOs\AuthResultDTO;
use Gazelle\Application\User\Services\SessionService;
use Gazelle\Domain\User\UserRepository;
use Gazelle\Domain\User\ValueObjects\Username;

/**
 * Authenticate User Handler
 *
 * @implements CommandHandler<AuthenticateUser, AuthResultDTO>
 */
final readonly class AuthenticateUserHandler implements CommandHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private SessionService $sessionService
    ) {}

    public function handle(Command $command): AuthResultDTO
    {
        assert($command instanceof AuthenticateUser);

        $user = $this->userRepository->findByUsername(
            Username::fromString($command->username)
        );

        if ($user === null) {
            return AuthResultDTO::failed('Invalid username or password');
        }

        if (!$user->isEnabled()) {
            return AuthResultDTO::failed('Account is disabled');
        }

        if (!$user->verifyPassword($command->password)) {
            return AuthResultDTO::failed('Invalid username or password');
        }

        // Record login and create session
        $user->recordLogin();
        $this->userRepository->save($user);

        $session = $this->sessionService->create(
            $user->userId(),
            $command->ipAddress,
            $command->userAgent
        );

        return AuthResultDTO::success($session);
    }
}
