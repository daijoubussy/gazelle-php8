<?php

declare(strict_types=1);

namespace Gazelle\Application\Common\Bus;

use Gazelle\Application\Common\Query;
use Gazelle\Application\Common\QueryHandler;
use Gazelle\Core\Container\Container;

/**
 * Query Bus
 *
 * Routes queries to their handlers.
 */
final readonly class QueryBus
{
    /** @var array<class-string<Query>, class-string<QueryHandler>> */
    private array $handlers;

    /**
     * @param array<class-string<Query>, class-string<QueryHandler>> $handlers
     */
    public function __construct(
        private Container $container,
        array $handlers = []
    ) {
        $this->handlers = $handlers;
    }

    /**
     * Dispatch a query to its handler
     */
    public function dispatch(Query $query): mixed
    {
        $queryClass = $query::class;

        if (!isset($this->handlers[$queryClass])) {
            throw new \RuntimeException("No handler registered for: {$queryClass}");
        }

        $handler = $this->container->get($this->handlers[$queryClass]);

        return $handler->handle($query);
    }
}
