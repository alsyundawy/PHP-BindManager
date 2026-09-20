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
        $denied = $this->checkAccess($request);
        if ($denied !== null) {
            return $denied;
        }

        return $next($request);
    }

    private function checkAccess(ServerRequestInterface $request): ?ResponseInterface
    {
        $route = $request->getAttribute('route', []);
        if (! (bool) ($route['auth'] ?? false)) {
            return null;
        }

        $userRole      = (string) ($_SESSION['role'] ?? 'viewer');
        $requiredRole  = (string) ($route['role'] ?? '');
        $path          = $request->getUri()->getPath();
        $isApi         = str_starts_with($path, '/api');
        $isStateChange = in_array(
            strtoupper($request->getMethod()),
            ['POST', 'PUT', 'DELETE', 'PATCH'],
            true
        );

        $denied = null;
        if ($userRole !== 'admin' && $requiredRole === 'admin') {
            $denied = $this->buildForbiddenResponse($isApi, 'Administrator privileges required.');
        } elseif ($userRole === 'viewer' && $isStateChange) {
            $denied = $this->buildForbiddenResponse(
                $isApi,
                'Your account has read-only permissions.',
                'Viewer role has read-only permissions.'
            );
        }

        return $denied;
    }

    /**
     * @suppress PHP0409
     * @suppress PHP0410
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

            $apiResponse = new Response(403, ['Content-Type' => 'application/json'], $body);

            return $apiResponse;
        }

        $_SESSION['flash_error'] = $sessionMessage;

        $redirectResponse = new Response(302, ['Location' => '/dashboard']);

        return $redirectResponse;
    }
}
