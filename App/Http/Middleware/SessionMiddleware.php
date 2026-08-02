<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\Auth\AuthenticationService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class SessionMiddleware
{
    public function __construct(private readonly AuthenticationService $authenticationService)
    {
    }

    public function process(ServerRequestInterface $request, callable $next): ResponseInterface
    {
        $this->authenticationService->startSession();

        return $next($request);
    }
}
