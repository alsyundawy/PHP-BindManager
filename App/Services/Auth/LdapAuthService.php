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
        if (! $this->enabled || $username === '' || $password === '') {
            return false;
        }

        if (! function_exists('ldap_connect') || ! function_exists('ldap_bind')) {
            return false;
        }

        $cleanUser = function_exists('ldap_escape')
            ? ldap_escape($username, '', LDAP_ESCAPE_DN)
            : addcslashes($username, ',=+<>#;\"\\');

        $userDn = sprintf('%s=%s,%s', $this->userAttribute, $cleanUser, $this->baseDn);

        $conn = @ldap_connect($this->host, $this->port);
        if ($conn === false) {
            return false;
        }

        if (function_exists('ldap_set_option')) {
            @ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
            @ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);
            if (defined('LDAP_OPT_NETWORK_TIMEOUT')) {
                @ldap_set_option($conn, LDAP_OPT_NETWORK_TIMEOUT, 5);
            }
        }

        $bindResult = @ldap_bind($conn, $userDn, $password);

        if (function_exists('ldap_unbind')) {
            $_ = @ldap_unbind($conn);
            unset($_);
        }

        return $bindResult === true;
    }
}
