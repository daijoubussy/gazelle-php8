<?php

declare(strict_types=1);

namespace Gazelle\Application\User\Queries;

use Gazelle\Application\Common\Query;

/**
 * Get User By ID Query
 */
final readonly class GetUserById implements Query
{
    public function __construct(
        public int $userId
    ) {}
}
