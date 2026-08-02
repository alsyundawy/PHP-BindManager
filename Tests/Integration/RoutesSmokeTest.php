<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;

final class RoutesSmokeTest extends TestCase
{
    public function testRoutesFileContainsLoginAndDashboardPaths(): void
    {
        $routes = require dirname(__DIR__, 2) . '/Routes/web.php';
        $paths = array_map(static fn(array $route): string => (string) $route['path'], $routes);

        self::assertContains('/login', $paths);
        self::assertContains('/dashboard', $paths);
        self::assertContains('/logout', $paths);
    }
}
