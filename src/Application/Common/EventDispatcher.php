<?php

declare(strict_types=1);

namespace Gazelle\Application\Common;

use Gazelle\Domain\Common\DomainEvent;

/**
 * Event Dispatcher Interface
 */
interface EventDispatcher
{
    /**
     * Dispatch a domain event to all registered listeners
     */
    public function dispatch(DomainEvent $event): void;

    /**
     * Register an event listener
     *
     * @param class-string<DomainEvent> $eventClass
     * @param callable(DomainEvent): void $listener
     */
    public function listen(string $eventClass, callable $listener): void;
}
