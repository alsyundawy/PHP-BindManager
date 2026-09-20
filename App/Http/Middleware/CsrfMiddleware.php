<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Exceptions\CsrfViolationException;
use App\Services\Auth\CsrfService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class CsrfMiddleware
{
    public function __construct(private readonly CsrfService $csrfService)
    {
    }

    public function process(ServerRequestInterface $request, callable $next): ResponseInterface
    {
        if (! in_array(strtoupper($request->getMethod()), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            $this->csrfService->token();

            return $next($request);
        }

        /** @var array<string, mixed> $route */
        $route = (array) $request->getAttribute('route', []);
        if (isset($route['csrf']) && $route['csrf'] === false) {
            return $next($request);
        }

        $parsedBody = $request->getParsedBody();
        $token      = null;

        if (is_array($parsedBody)) {
            $token = $parsedBody['_csrf_token'] ?? null;
        }

        $token = is_string($token) ? $token : $request->getHeaderLine('X-CSRF-Token');

        if (! $this->csrfService->validateToken($token)) {
            throw new CsrfViolationException();
        }

        return $next($request);
    }
}
