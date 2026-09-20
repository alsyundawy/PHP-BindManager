<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\Auth\TotpService;
use PHPUnit\Framework\TestCase;

final class TotpServiceTest extends TestCase
{
    private TotpService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TotpService();
    }

    public function testGeneratesValidSecret(): void
    {
        $secret = $this->service->generateSecret(16);
        self::assertSame(16, strlen($secret));
        self::assertMatchesRegularExpression('/^[A-Z2-7]+$/', $secret);
    }

    public function testGeneratesSixDigitOtp(): void
    {
        $secret = 'JBSWY3DPEHPK3PXP';
        $otp    = $this->service->getOtp($secret, 1000);
        self::assertSame(6, strlen($otp));
        self::assertMatchesRegularExpression('/^\d{6}$/', $otp);
    }

    public function testVerifiesCorrectOtp(): void
    {
        $secret = 'JBSWY3DPEHPK3PXP';
        $otp    = $this->service->getOtp($secret);
        self::assertTrue($this->service->verify($secret, $otp));
    }

    public function testRejectsIncorrectOtp(): void
    {
        $secret = 'JBSWY3DPEHPK3PXP';
        self::assertFalse($this->service->verify($secret, '000000'));
    }

    public function testBuildsProvisioningUri(): void
    {
        $secret = 'JBSWY3DPEHPK3PXP';
        $uri    = $this->service->getProvisioningUri($secret, 'admin@example.com', 'PHP-BindManager');

        self::assertStringStartsWith('otpauth://totp/PHP-BindManager:admin%40example.com?', $uri);
        self::assertStringContainsString('secret=JBSWY3DPEHPK3PXP', $uri);
        self::assertStringContainsString('issuer=PHP-BindManager', $uri);
    }
}
