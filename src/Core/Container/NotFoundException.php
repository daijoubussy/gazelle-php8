<?php

declare(strict_types=1);

namespace Gazelle\Core\Container;

use Psr\Container\NotFoundExceptionInterface;

final class NotFoundException extends \Exception implements NotFoundExceptionInterface
{
}
