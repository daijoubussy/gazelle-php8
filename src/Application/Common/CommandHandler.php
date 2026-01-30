<?php

declare(strict_types=1);

namespace Gazelle\Application\Common;

/**
 * Command Handler Interface
 *
 * Handles a single command type and performs the business logic.
 *
 * @template TCommand of Command
 * @template TResult
 */
interface CommandHandler
{
    /**
     * Handle the command
     *
     * @param TCommand $command
     * @return TResult
     */
    public function handle(Command $command): mixed;
}
