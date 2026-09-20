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

    /**
     * @param array<int, array<string, mixed>> $routes
     */
    public static function fromRoutes(array $routes): self
    {
        return new self($routes);
    }

    /**
     * @param array<int, string> $files
     */
    public static function loadFromFiles(array $files): self
    {
        /** @var array<int, array<string, mixed>> $allRoutes */
        $allRoutes = [];
        foreach ($files as $file) {
            if (file_exists($file)) {
                $routes = require $file;
                if (is_array($routes)) {
                    /** @var array<int, array<string, mixed>> $routes */
                    $allRoutes = array_merge($allRoutes, $routes);
                }
            }
        }

        return new self($allRoutes);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function match(ServerRequestInterface $request): RouteMatch
    {
        $method = strtoupper($request->getMethod());
        $path   = $request->getUri()->getPath();

        if ($path === '') {
            $path = '/';
        }

        foreach ($this->routes as $route) {
            $routeMethod = strtoupper((string) ($route['method'] ?? ''));
            $routePath   = (string) ($route['path'] ?? '');

            if ($routeMethod !== $method) {
                continue;
            }

            /** @var array<string, string> $params */
            $params  = [];
            $matched = false;

            if ($routePath === $path) {
                $matched = true;
            } elseif (str_contains($routePath, '{')) {
                $pattern = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?P<$1>[^/]+)', $routePath);
                if (is_string($pattern)) {
                    $regex = '#^' . $pattern . '$#';
                    if (preg_match($regex, $path, $matches) === 1) {
                        $matched = true;
                        foreach ($matches as $key => $value) {
                            if (is_string($key)) {
                                $params[$key] = $value;
                            }
                        }
                    }
                }
            }

            if (! $matched) {
                continue;
            }

            /** @var callable(ServerRequestInterface): ResponseInterface $handler */
            $handler = $route['handler'];
            $request = $request->withAttribute('route', $route)
                ->withAttribute('params', $params);

            foreach ($params as $paramKey => $paramValue) {
                $request = $request->withAttribute($paramKey, $paramValue);
            }

            return new RouteMatch($request, $handler, $route);
        }

        throw new HttpException('Not Found', 404);
    }

    public static function html(string $html, int $statusCode = 200): Response
    {
        return new Response($statusCode, ['Content-Type' => 'text/html; charset=UTF-8'], $html);
    }

    /**
     * @param mixed $data
     */
    public static function json(mixed $data, int $statusCode = 200): Response
    {
        $json = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);

        return new Response($statusCode, ['Content-Type' => 'application/json'], $json);
    }
}
