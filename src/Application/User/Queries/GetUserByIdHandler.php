<?php

declare(strict_types=1);

namespace Gazelle\Application\User\Queries;

use Gazelle\Application\Common\Query;
use Gazelle\Application\Common\QueryHandler;
use Gazelle\Application\User\DTOs\UserDTO;
use Gazelle\Domain\User\UserId;
use Gazelle\Domain\User\UserRepository;

/**
 * Get User By ID Handler
 *
 * @implements QueryHandler<GetUserById, UserDTO|null>
 */
final readonly class GetUserByIdHandler implements QueryHandler
{
    public function __construct(
        private UserRepository $userRepository
    ) {}

    public function handle(Query $query): ?UserDTO
    {
        assert($query instanceof GetUserById);

        $user = $this->userRepository->findById(
            UserId::fromInt($query->userId)
        );

        if ($user === null) {
            return null;
        }

        return UserDTO::fromEntity($user);
    }
}
