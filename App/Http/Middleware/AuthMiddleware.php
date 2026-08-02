<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Exceptions\AuthenticationException;
use App\Services\Auth\AuthenticationService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class AuthMiddleware
{
    public function __construct(private readonly AuthenticationService $authenticationService)
    {
    }

    public function process(ServerRequestInterface $request, callable $next): ResponseInterface
    {
        $route = $request->getAttribute('route', []);
        $authRequired = (bool) ($route['auth'] ?? false);

        if ($authRequired && ! $this->authenticationService->isAuthenticated()) {
            throw new AuthenticationException();
        }

        return $next($request);
    }
}
