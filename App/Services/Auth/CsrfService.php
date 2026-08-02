<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Support\Config;

final class CsrfService
{
    private const SESSION_KEY = '_csrf';

    public function __construct(private readonly Config $config)
    {
    }

    public function token(): string
    {
        $token = $_SESSION[self::SESSION_KEY]['value'] ?? null;
        $expiresAt = $_SESSION[self::SESSION_KEY]['expires_at'] ?? 0;

        if (! is_string($token) || (int) $expiresAt <= time()) {
            $token = bin2hex(random_bytes(32));
            $_SESSION[self::SESSION_KEY] = [
                'value' => $token,
                'expires_at' => time() + (int) $this->config->get('security.csrf_token_lifetime', 3600),
            ];
        }

        return $token;
    }

    public function validateToken(?string $token): bool
    {
        if ($token === null || $token === '') {
            return false;
        }

        $sessionToken = $_SESSION[self::SESSION_KEY]['value'] ?? null;
        $expiresAt = (int) ($_SESSION[self::SESSION_KEY]['expires_at'] ?? 0);

        if (! is_string($sessionToken) || $expiresAt <= time()) {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }
}
