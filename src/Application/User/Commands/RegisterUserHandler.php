<?php

declare(strict_types=1);

namespace Gazelle\Application\User\Commands;

use Gazelle\Application\Common\Command;
use Gazelle\Application\Common\CommandHandler;
use Gazelle\Application\User\DTOs\UserDTO;
use Gazelle\Domain\User\User;
use Gazelle\Domain\User\UserRepository;
use Gazelle\Domain\User\ValueObjects\Email;
use Gazelle\Domain\User\ValueObjects\Password;
use Gazelle\Domain\User\ValueObjects\Username;
use Gazelle\Application\Common\EventDispatcher;

/**
 * Register User Handler
 *
 * @implements CommandHandler<RegisterUser, UserDTO>
 */
final readonly class RegisterUserHandler implements CommandHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private EventDispatcher $eventDispatcher
    ) {}

    public function handle(Command $command): UserDTO
    {
        assert($command instanceof RegisterUser);

        $username = Username::fromString($command->username);
        $email = Email::fromString($command->email);
        $password = Password::fromPlaintext($command->password);

        // Validate uniqueness
        if ($this->userRepository->usernameExists($username)) {
            throw new \DomainException('Username already exists');
        }

        if ($this->userRepository->emailExists($email)) {
            throw new \DomainException('Email already exists');
        }

        // Create the user
        $userId = $this->userRepository->nextId();
        $user = User::create($userId, $username, $email, $password);

        // Persist
        $this->userRepository->save($user);

        // Dispatch domain events
        foreach ($user->pullDomainEvents() as $event) {
            $this->eventDispatcher->dispatch($event);
        }

        return UserDTO::fromEntity($user);
    }
}
