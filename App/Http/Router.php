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
     * @var array<string, array<int, array<string, mixed>>>
     */
    private static array $fileCache = [];

    /**
     * @param array<int, array<string, mixed>> $routes
     */
    private function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function readRoutesFile(string $file): array
    {
        if (isset(self::$fileCache[$file])) {
            return self::$fileCache[$file];
        }

        /** @psalm-suppress UnresolvableInclude */
        $routes = require_once $file;

        if (! is_array($routes)) {
            throw new InvalidArgumentException('Routes file must return an array.');
        }

        /** @var array<int, array<string, mixed>> $routes */
        self::$fileCache[$file] = $routes;

        return $routes;
    }

    public static function fromFile(string $file): self
    {
        return new self(self::readRoutesFile($file));
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
                $allRoutes = array_merge($allRoutes, self::readRoutesFile($file));
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
            if ($routeMethod !== $method) {
                continue;
            }

            $routePath = (string) ($route['path'] ?? '');
            $params    = $this->matchPath($routePath, $path);

            if ($params !== null) {
                return $this->createMatch($request, $route, $params);
            }
        }

        throw new HttpException('Not Found', 404);
    }

    /**
     * @return array<string, string>|null
     */
    private function matchPath(string $routePath, string $path): ?array
    {
        if ($routePath === $path) {
            return [];
        }

        if (! str_contains($routePath, '{')) {
            return null;
        }

        $pattern = preg_replace('/\{([a-zA-Z_]\w*)\}/', '(?P<$1>[^/]+)', $routePath);
        if (! is_string($pattern) || preg_match('#^' . $pattern . '$#', $path, $matches) !== 1) {
            return null;
        }

        /** @var array<string, string> $params */
        $params = [];
        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $params[$key] = $value;
            }
        }

        return $params;
    }

    /**
     * @param array<string, mixed> $route
     * @param array<string, string> $params
     */
    private function createMatch(ServerRequestInterface $request, array $route, array $params): RouteMatch
    {
        /** @var callable(ServerRequestInterface): ResponseInterface $handler */
        $handler = $route['handler'];
        $request = $request->withAttribute('route', $route)
            ->withAttribute('params', $params);

        foreach ($params as $paramKey => $paramValue) {
            $request = $request->withAttribute($paramKey, $paramValue);
        }

        return new RouteMatch($request, $handler, $route);
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
