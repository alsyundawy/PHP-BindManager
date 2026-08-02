<?php

declare(strict_types=1);

namespace App\Container;

use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;
use Throwable;

final class Container implements ContainerInterface
{
    /**
     * @var array<string, callable(self): mixed>
     */
    private array $bindings = [];

    /**
     * @var array<string, mixed>
     */
    private array $instances = [];

    public function get(string $id): mixed
    {
        if (array_key_exists($id, $this->instances)) {
            return $this->instances[$id];
        }

        if (! $this->has($id)) {
            throw new class(sprintf('Service "%s" is not bound.', $id)) extends RuntimeException implements NotFoundExceptionInterface {
            };
        }

        try {
            $this->instances[$id] = ($this->bindings[$id])($this);
        } catch (Throwable $throwable) {
            throw new class('Failed to resolve service.', 0, $throwable) extends RuntimeException implements ContainerExceptionInterface {
            };
        }

        return $this->instances[$id];
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->bindings);
    }

    /**
     * @param callable(self): mixed $resolver
     */
    public function set(string $id, callable $resolver): void
    {
        if ($id === '') {
            throw new InvalidArgumentException('Service id cannot be empty.');
        }

        $this->bindings[$id] = $resolver;
    }
}
