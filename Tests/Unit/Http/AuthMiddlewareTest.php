<?php

declare(strict_types=1);

namespace Tests\Unit\Http;

use App\Exceptions\AuthenticationException;
use App\Http\Middleware\AuthMiddleware;
use App\Repositories\Auth\LoginAttemptRepository;
use App\Repositories\Auth\SessionRepository;
use App\Repositories\Auth\UserRepository;
use App\Services\Auth\AuthenticationService;
use App\Services\Auth\RateLimiterService;
use App\Support\Config;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PDO;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class AuthMiddlewareTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($_SESSION['user_id']);
        parent::tearDown();
    }

    private function createAuthService(): AuthenticationService
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $userRepo    = new UserRepository($pdo);
        $sessionRepo = new SessionRepository($pdo);
        $limitRepo   = new LoginAttemptRepository($pdo);
        $config      = new Config(['security' => [], 'session' => []]);
        $rateLimiter = new RateLimiterService($limitRepo, $config);

        return new AuthenticationService($userRepo, $sessionRepo, $rateLimiter, $config);
    }

    public function testRedirectsToLoginWhenWebUserUnauthenticated(): void
    {
        unset($_SESSION['user_id']);
        $authService = $this->createAuthService();
        $middleware  = new AuthMiddleware($authService);
        $baseRequest = new ServerRequest('GET', '/dashboard');
        $request     = $baseRequest
            ->withAttribute('route', ['auth' => true])
            ->withHeader('Accept', 'text/html,application/xhtml+xml');

        $next = static fn (ServerRequestInterface $req): ResponseInterface => new Response(200, [], 'OK');

        $response = $middleware->process($request, $next);

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('/login', $response->getHeaderLine('Location'));
    }

    public function testThrowsAuthenticationExceptionWhenApiUserUnauthenticated(): void
    {
        unset($_SESSION['user_id']);
        $authService = $this->createAuthService();
        $middleware  = new AuthMiddleware($authService);
        $baseRequest = new ServerRequest('GET', '/api/v1/zones');
        $request     = $baseRequest
            ->withAttribute('route', ['auth' => true])
            ->withHeader('Accept', 'application/json');

        $next = static fn (ServerRequestInterface $req): ResponseInterface => new Response(200, [], 'OK');

        $this->expectException(AuthenticationException::class);
        $middleware->process($request, $next);
    }

    public function testPassesThroughWhenAuthenticated(): void
    {
        $_SESSION['user_id'] = 42;
        $authService         = $this->createAuthService();
        $middleware          = new AuthMiddleware($authService);
        $baseRequest         = new ServerRequest('GET', '/dashboard');
        $request             = $baseRequest
            ->withAttribute('route', ['auth' => true]);

        $next = static function (ServerRequestInterface $req): ResponseInterface {
            return new Response(200, [], 'dashboard-content');
        };

        $response = $middleware->process($request, $next);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('dashboard-content', (string) $response->getBody());
    }
}
