<?php

declare(strict_types=1);

namespace Tests\Unit\Http;

use App\Http\SecurityHeaders;
use PHPUnit\Framework\TestCase;

final class SecurityHeadersTest extends TestCase
{
    public function testHeadersForInsecureHttpDoesNotIncludeUpgradeDirectiveOrHsts(): void
    {
        $headers = SecurityHeaders::forResponse(false);

        self::assertArrayNotHasKey('Strict-Transport-Security', $headers);
        self::assertStringNotContainsString('upgrade-insecure-requests', $headers['Content-Security-Policy'] ?? '');
        self::assertSame('DENY', $headers['X-Frame-Options'] ?? null);
        self::assertSame('nosniff', $headers['X-Content-Type-Options'] ?? null);
    }

    public function testHeadersForSecureHttpsIncludesUpgradeDirectiveAndHsts(): void
    {
        $headers = SecurityHeaders::forResponse(true);

        self::assertArrayHasKey('Strict-Transport-Security', $headers);
        self::assertStringContainsString('upgrade-insecure-requests', $headers['Content-Security-Policy'] ?? '');
    }
}
