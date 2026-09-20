<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\Auth\LdapAuthService;
use PHPUnit\Framework\TestCase;

final class LdapAuthServiceTest extends TestCase
{
    public function testDisabledByDefault(): void
    {
        $ldap = new LdapAuthService();
        self::assertFalse($ldap->isEnabled());
        self::assertFalse($ldap->authenticate('alice', 'secret123'));
    }

    public function testReturnsFalseOnEmptyCredentials(): void
    {
        $ldap = new LdapAuthService(true, 'ldap.internal.net', 389, 'dc=corp,dc=local');
        self::assertTrue($ldap->isEnabled());
        self::assertFalse($ldap->authenticate('', ''));
        self::assertFalse($ldap->authenticate('alice', ''));
        self::assertFalse($ldap->authenticate('', 'secret'));
    }

    public function testGettersReturnConfiguredValues(): void
    {
        $ldap = new LdapAuthService(true, 'ldap.example.org', 636, 'dc=example,dc=org', 'sAMAccountName');
        self::assertSame('ldap.example.org', $ldap->getHost());
        self::assertSame(636, $ldap->getPort());
        self::assertSame('dc=example,dc=org', $ldap->getBaseDn());
    }
}
