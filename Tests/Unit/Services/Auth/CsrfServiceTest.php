<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Auth;

use App\Services\Auth\CsrfService;
use App\Support\Config;
use PHPUnit\Framework\TestCase;

final class CsrfServiceTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        $_SESSION = [];
    }

    public function testTokenCanBeGeneratedAndValidated(): void
    {
        $service = new CsrfService(new Config(['security' => ['csrf_token_lifetime' => 3600]]));
        $token = $service->token();

        self::assertNotSame('', $token);
        self::assertTrue($service->validateToken($token));
        self::assertFalse($service->validateToken('invalid-token'));
    }
}
