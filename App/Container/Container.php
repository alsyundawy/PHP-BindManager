<?php

declare(strict_types=1);

namespace App\Container;

use App\Exceptions\ContainerNotFoundException;
use App\Exceptions\ContainerResolutionException;
use Psr\Container\ContainerInterface;
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
            throw new ContainerNotFoundException(sprintf('Service "%s" is not bound.', $id));
        }

        try {
            $this->instances[$id] = ($this->bindings[$id])($this);
        } catch (Throwable $throwable) {
            throw new ContainerResolutionException('Failed to resolve service.', 0, $throwable);
        }

        return $this->instances[$id];
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->bindings);
    }

    /**
     * @param callable(self): mixed $factory
     */
    public function set(string $id, callable $factory): void
    {
        $this->bindings[$id] = $factory;
        unset($this->instances[$id]);
    }
}
