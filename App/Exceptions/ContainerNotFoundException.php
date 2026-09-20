<?php

declare(strict_types=1);

namespace App\Exceptions;

use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;

final class ContainerNotFoundException extends RuntimeException implements NotFoundExceptionInterface
{
}
