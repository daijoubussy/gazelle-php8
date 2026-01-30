<?php

declare(strict_types=1);

namespace Gazelle\Application\Common\Bus;

use Gazelle\Application\Common\Command;
use Gazelle\Application\Common\CommandHandler;
use Gazelle\Core\Container\Container;

/**
 * Command Bus
 *
 * Routes commands to their handlers.
 */
final readonly class CommandBus
{
    /** @var array<class-string<Command>, class-string<CommandHandler>> */
    private array $handlers;

    /**
     * @param array<class-string<Command>, class-string<CommandHandler>> $handlers
     */
    public function __construct(
        private Container $container,
        array $handlers = []
    ) {
        $this->handlers = $handlers;
    }

    /**
     * Dispatch a command to its handler
     */
    public function dispatch(Command $command): mixed
    {
        $commandClass = $command::class;

        if (!isset($this->handlers[$commandClass])) {
            throw new \RuntimeException("No handler registered for: {$commandClass}");
        }

        $handler = $this->container->get($this->handlers[$commandClass]);

        return $handler->handle($command);
    }
}
