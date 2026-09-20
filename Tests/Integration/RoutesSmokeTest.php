<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Http\Router;
use PHPUnit\Framework\TestCase;

final class RoutesSmokeTest extends TestCase
{
    public function testWebRoutesFileContainsAllRequiredPaths(): void
    {
        $routes = Router::fromFile(dirname(__DIR__, 2) . '/Routes/web.php')->getRoutes();
        $paths  = array_map(static fn (array $route): string => (string) $route['path'], $routes);

        self::assertContains('/', $paths);
        self::assertContains('/login', $paths);
        self::assertContains('/dashboard', $paths);
        self::assertContains('/logout', $paths);
    }

    public function testDnsRoutesFileContainsAllCrudAndDeployPaths(): void
    {
        $routes = Router::fromFile(dirname(__DIR__, 2) . '/Routes/dns.php')->getRoutes();
        $paths  = array_map(static fn (array $route): string => (string) $route['path'], $routes);

        self::assertContains('/zones', $paths);
        self::assertContains('/zones/create', $paths);
        self::assertContains('/zones/{id}', $paths);
        self::assertContains('/zones/{id}/deploy', $paths);
        self::assertContains('/zones/{id}/delete', $paths);
        self::assertContains('/records', $paths);
        self::assertContains('/records/create', $paths);
        self::assertContains('/records/{id}/delete', $paths);
    }

    public function testSystemRoutesFileContainsSystemAndApiDocs(): void
    {
        $routes = Router::fromFile(dirname(__DIR__, 2) . '/Routes/system.php')->getRoutes();
        $paths  = array_map(static fn (array $route): string => (string) $route['path'], $routes);

        self::assertContains('/system', $paths);
        self::assertContains('/api/docs', $paths);
    }

    public function testApiRoutesFileContainsAllV1Endpoints(): void
    {
        $routes = Router::fromFile(dirname(__DIR__, 2) . '/Routes/api.php')->getRoutes();
        $paths  = array_map(static fn (array $route): string => (string) $route['path'], $routes);

        self::assertContains('/api/v1/system/health', $paths);
        self::assertContains('/api/v1/zones', $paths);
        self::assertContains('/api/v1/zones/{id}', $paths);
        self::assertContains('/api/v1/zones/{id}/deploy', $paths);
        self::assertContains('/api/v1/records', $paths);
        self::assertContains('/api/v1/records/{id}', $paths);
    }
}
