<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class RbacMiddleware
{
    /**
     * @param callable(ServerRequestInterface): ResponseInterface $next
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, callable $next): ResponseInterface
    {
        $route = $request->getAttribute('route', []);

        if (! (bool) ($route['auth'] ?? false)) {
            return $next($request);
        }

        $userRole     = (string) ($_SESSION['role'] ?? 'viewer');
        $requiredRole = (string) ($route['role'] ?? '');
        $path         = $request->getUri()->getPath();
        $isApi        = str_starts_with($path, '/api');

        // Admin has full access — early return
        if ($userRole === 'admin') {
            return $next($request);
        }

        // Route requires admin and current user is not admin
        if ($requiredRole === 'admin') {
            return $this->buildForbiddenResponse(
                $isApi,
                'Administrator privileges required.'
            );
        }

        // Viewer cannot perform state-changing operations
        $isStateChange = in_array(
            strtoupper($request->getMethod()),
            ['POST', 'PUT', 'DELETE', 'PATCH'],
            true
        );

        if ($userRole === 'viewer' && $isStateChange) {
            return $this->buildForbiddenResponse(
                $isApi,
                'Your account has read-only permissions.',
                'Viewer role has read-only permissions.'
            );
        }

        return $next($request);
    }

    /**
     * @return ResponseInterface
     */
    private function buildForbiddenResponse(
        bool $isApi,
        string $sessionMessage,
        string $apiMessage = ''
    ): ResponseInterface {
        if ($isApi) {
            $body = json_encode(
                [
                    'error'   => 'Forbidden',
                    'message' => $apiMessage !== '' ? $apiMessage : $sessionMessage,
                ],
                JSON_THROW_ON_ERROR
            );

            return new Response(403, ['Content-Type' => 'application/json'], $body);
        }

        $_SESSION['flash_error'] = $sessionMessage;

        return new Response(302, ['Location' => '/dashboard']);
    }
}
