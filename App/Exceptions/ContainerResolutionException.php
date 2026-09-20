<?php

declare(strict_types=1);

namespace App\Exceptions;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

final class ContainerResolutionException extends RuntimeException implements ContainerExceptionInterface
{
}
