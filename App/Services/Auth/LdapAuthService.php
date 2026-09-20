<?php

declare(strict_types=1);

namespace App\Services\Auth;

final class LdapAuthService
{
    public function __construct(
        private readonly bool $enabled = false,
        private readonly string $host = 'ldap.example.com',
        private readonly int $port = 389,
        private readonly string $baseDn = 'dc=example,dc=com',
        private readonly string $userAttribute = 'uid'
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getBaseDn(): string
    {
        return $this->baseDn;
    }

    /**
     * Authenticate credentials against an LDAP directory server.
     */
    public function authenticate(string $username, string $password): bool
    {
        if (! $this->canAuthenticate($username, $password)) {
            return false;
        }

        return $this->performBind($username, $password);
    }

    private function canAuthenticate(string $username, string $password): bool
    {
        return $this->enabled
            && $username !== ''
            && $password !== ''
            && extension_loaded('ldap');
    }

    private function performBind(string $username, string $password): bool
    {
        $cleanUser = addcslashes($username, ',=+<>#;\"\\');
        $userDn    = sprintf('%s=%s,%s', $this->userAttribute, $cleanUser, $this->baseDn);

        /** @var callable $connect */
        $connect = 'ldap_connect';
        /** @var \LDAP\Connection|false $conn */
        $conn = @$connect($this->host, $this->port);
        if ($conn === false) {
            return false;
        }

        /** @var callable $setOpt */
        $setOpt       = 'ldap_set_option';
        $optVersion   = defined('LDAP_OPT_PROTOCOL_VERSION') ? constant('LDAP_OPT_PROTOCOL_VERSION') : 17;
        $optReferrals = defined('LDAP_OPT_REFERRALS') ? constant('LDAP_OPT_REFERRALS') : 8;
        @$setOpt($conn, $optVersion, 3);
        @$setOpt($conn, $optReferrals, 0);

        /** @var callable $bind */
        $bind       = 'ldap_bind';
        $bindResult = @$bind($conn, $userDn, $password);

        /** @var callable $unbind */
        $unbind = 'ldap_unbind';
        $_      = @$unbind($conn);
        unset($_);

        return $bindResult === true;
    }
}
