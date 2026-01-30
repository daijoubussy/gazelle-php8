<?php

declare(strict_types=1);

namespace Gazelle\Application\Common;

/**
 * Query Handler Interface
 *
 * @template TQuery of Query
 * @template TResult
 */
interface QueryHandler
{
    /**
     * Handle the query
     *
     * @param TQuery $query
     * @return TResult
     */
    public function handle(Query $query): mixed;
}
