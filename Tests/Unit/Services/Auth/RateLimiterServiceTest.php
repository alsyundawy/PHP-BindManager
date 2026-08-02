<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Auth;

use App\Repositories\Auth\LoginAttemptRepository;
use App\Services\Auth\RateLimiterService;
use App\Support\Config;
use PDO;
use PHPUnit\Framework\TestCase;

final class RateLimiterServiceTest extends TestCase
{
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec('CREATE TABLE rate_limits (identifier TEXT NOT NULL, action TEXT NOT NULL, attempts INTEGER NOT NULL DEFAULT 0, reset_at INTEGER NOT NULL, PRIMARY KEY(identifier, action))');
    }

    public function testItBlocksAfterConfiguredLimit(): void
    {
        $repo = new LoginAttemptRepository($this->pdo);
        $service = new RateLimiterService($repo, new Config(['security' => ['rate_limit_login' => 2], 'api' => ['rate_limit' => 300]]));

        self::assertTrue($service->allow('login', '127.0.0.1'));
        $service->hit('login', '127.0.0.1');
        self::assertTrue($service->allow('login', '127.0.0.1'));
        $service->hit('login', '127.0.0.1');
        self::assertFalse($service->allow('login', '127.0.0.1'));
    }
}
