<?php

declare(strict_types=1);

namespace Gazelle\Infrastructure\Events;

use Gazelle\Application\Common\EventDispatcher;
use Gazelle\Core\Container\Container;
use Gazelle\Domain\Common\DomainEvent;

/**
 * Synchronous Event Dispatcher
 *
 * Dispatches events synchronously to registered listeners.
 */
final class SyncEventDispatcher implements EventDispatcher
{
    /** @var array<class-string<DomainEvent>, array<callable>> */
    private array $listeners = [];

    public function __construct(
        private readonly Container $container
    ) {}

    public function dispatch(DomainEvent $event): void
    {
        $eventClass = $event::class;

        // Dispatch to specific listeners
        foreach ($this->listeners[$eventClass] ?? [] as $listener) {
            $listener($event);
        }

        // Dispatch to wildcard listeners
        foreach ($this->listeners['*'] ?? [] as $listener) {
            $listener($event);
        }
    }

    public function listen(string $eventClass, callable $listener): void
    {
        $this->listeners[$eventClass][] = $listener;
    }

    /**
     * Register a listener class (will be resolved from container)
     *
     * @param class-string<DomainEvent> $eventClass
     * @param class-string $listenerClass
     */
    public function subscribe(string $eventClass, string $listenerClass): void
    {
        $this->listeners[$eventClass][] = function (DomainEvent $event) use ($listenerClass) {
            $listener = $this->container->get($listenerClass);
            $listener->handle($event);
        };
    }
}
