<?php

declare(strict_types=1);

namespace App\Http;

use App\Exceptions\HttpException;
use InvalidArgumentException;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class Router
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private array $routes = [];

    /**
     * @param array<int, array<string, mixed>> $routes
     */
    private function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    public static function fromFile(string $file): self
    {
        $routes = require $file;

        if (! is_array($routes)) {
            throw new InvalidArgumentException('Routes file must return an array.');
        }

        return new self($routes);
    }

    public function match(ServerRequestInterface $request): RouteMatch
    {
        $method = strtoupper($request->getMethod());
        $path = rtrim($request->getUri()->getPath(), '/') ?: '/';

        foreach ($this->routes as $route) {
            $routeMethod = strtoupper((string) ($route['method'] ?? 'GET'));
            $routePath = rtrim((string) ($route['path'] ?? '/'), '/') ?: '/';

            if ($routeMethod !== $method || $routePath !== $path) {
                continue;
            }

            /** @var callable(ServerRequestInterface): ResponseInterface $handler */
            $handler = $route['handler'];
            $request = $request->withAttribute('route', $route);

            return new RouteMatch($request, $handler, $route);
        }

        throw new HttpException('Not Found', 404);
    }

    public static function html(string $html, int $statusCode = 200): ResponseInterface
    {
        return new Response($statusCode, ['Content-Type' => 'text/html; charset=UTF-8'], $html);
    }
}
