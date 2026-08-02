<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Exceptions\HttpException;
use App\Services\Auth\RateLimiterService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class RateLimitMiddleware
{
    public function __construct(private readonly RateLimiterService $rateLimiter)
    {
    }

    public function process(ServerRequestInterface $request, callable $next): ResponseInterface
    {
        $route = $request->getAttribute('route', []);
        $action = (string) ($route['rate_limit'] ?? 'web');
        $identifier = $this->resolveIdentifier($request);

        if (! $this->rateLimiter->allow($action, $identifier)) {
            throw new HttpException('Too Many Requests', 429);
        }

        return $next($request);
    }

    private function resolveIdentifier(ServerRequestInterface $request): string
    {
        $server = $request->getServerParams();

        return (string) ($server['REMOTE_ADDR'] ?? 'unknown');
    }
}
