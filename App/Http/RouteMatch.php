<?php

declare(strict_types=1);

namespace App\Http;

use App\Exceptions\HttpException;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class RouteMatch
{
    /**
     * @param callable(ServerRequestInterface): ResponseInterface $handler
     * @param array<string, mixed> $route
     */
    public function __construct(
        public readonly ServerRequestInterface $request,
        private readonly $handler,
        private readonly array $route,
    ) {
    }

    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        return ($this->handler)($request);
    }

    /**
     * @return array<string, mixed>
     */
    public function route(): array
    {
        return $this->route;
    }
}
