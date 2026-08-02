<?php

declare(strict_types=1);

namespace App\Http;

use App\Container\Container;
use App\Http\Middleware\AuthMiddleware;
use App\Http\Middleware\CsrfMiddleware;
use App\Http\Middleware\RateLimitMiddleware;
use App\Http\Middleware\SessionMiddleware;
use App\Services\Auth\AuthenticationService;
use App\Services\Auth\CsrfService;
use App\Services\Auth\RateLimiterService;
use App\Support\View;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class Kernel
{
    public function __construct(private readonly Container $container)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $router = $this->container->get(Router::class);
        $matched = $router->match($request);
        $request = $matched->request;

        $middlewareStack = [
            new SessionMiddleware($this->container->get(AuthenticationService::class)),
            new RateLimitMiddleware($this->container->get(RateLimiterService::class)),
            new CsrfMiddleware($this->container->get(CsrfService::class)),
            new AuthMiddleware($this->container->get(AuthenticationService::class)),
        ];

        $handler = array_reduce(
            array_reverse($middlewareStack),
            static function (callable $next, object $middleware): callable {
                return static fn (ServerRequestInterface $request): ResponseInterface => $middleware->process($request, $next);
            },
            static fn (ServerRequestInterface $request): ResponseInterface => $matched->dispatch($request)
        );

        return $handler($request);
    }

    public function errorResponse(int $statusCode, string $message): ResponseInterface
    {
        $html = View::render('errors/generic', [
            'statusCode' => $statusCode,
            'message' => $message,
        ]);

        return new Response($statusCode, ['Content-Type' => 'text/html; charset=UTF-8'], $html);
    }
}
