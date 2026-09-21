<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class RbacMiddleware
{
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory = new Psr17Factory()
    ) {
    }
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
     * @suppress PHP0412
     * @return ResponseInterface
     */
    private function buildForbiddenResponse(
        bool $isApi,
        string $sessionMessage,
        string $apiMessage = ''
    ): ResponseInterface {
        if ($isApi) {
            $streamFactory = new Psr17Factory();
            $body          = json_encode(
                [
                    'error'   => 'Forbidden',
                    'message' => $apiMessage !== '' ? $apiMessage : $sessionMessage,
                ],
                JSON_THROW_ON_ERROR
            );

            return $this->responseFactory->createResponse(403)
                ->withHeader('Content-Type', 'application/json')
                ->withBody($streamFactory->createStream($body));
        }

        $_SESSION['flash_error'] = $sessionMessage;

        return $this->responseFactory->createResponse(302)->withHeader('Location', '/dashboard');
    }
}
