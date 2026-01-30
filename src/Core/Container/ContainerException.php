<?php

declare(strict_types=1);

namespace Gazelle\Core\Container;

use Psr\Container\ContainerExceptionInterface;

final class ContainerException extends \Exception implements ContainerExceptionInterface
{
}
