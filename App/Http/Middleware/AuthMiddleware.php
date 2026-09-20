<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Exceptions\AuthenticationException;
use App\Services\Auth\AuthenticationService;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class AuthMiddleware
{
    public function __construct(private readonly AuthenticationService $authenticationService)
    {
    }

    /**
     * @param callable(ServerRequestInterface): ResponseInterface $next
     */
    public function process(ServerRequestInterface $request, callable $next): ResponseInterface
    {
        $route        = $request->getAttribute('route', []);
        $authRequired = (bool) ($route['auth'] ?? false);

        if (! $authRequired) {
            return $next($request);
        }

        if ($this->authenticationService->isAuthenticated()) {
            return $next($request);
        }

        $path   = $request->getUri()->getPath();
        $accept = $request->getHeaderLine('Accept');

        if (
            str_starts_with($path, '/api')
            || (str_contains($accept, 'application/json') && ! str_contains($accept, 'text/html'))
        ) {
            throw new AuthenticationException();
        }

        $redirect = new Response(302, ['Location' => '/login']);

        return $redirect;
    }
}
