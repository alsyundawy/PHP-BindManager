<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Exceptions\AuthenticationException;
use App\Repositories\Auth\SessionRepository;
use App\Repositories\Auth\UserRepository;
use App\Support\Config;

final class AuthenticationService
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly SessionRepository $sessions,
        private readonly RateLimiterService $rateLimiter,
        private readonly Config $config,
    ) {
    }

    public function startSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_name((string) $this->config->get('session.name', 'pbm_session'));

        $sameSiteConfig = strtolower((string) $this->config->get('session.samesite', 'lax'));
        $sameSite       = match ($sameSiteConfig) {
            'none'   => 'None',
            'strict' => 'Strict',
            default  => 'Lax',
        };

        $secure   = (bool) $this->config->get('session.secure', true);
        $httpOnly = (bool) $this->config->get('session.httponly', true);

        ini_set('session.cookie_secure', $secure ? '1' : '0');
        ini_set('session.cookie_httponly', $httpOnly ? '1' : '0');
        ini_set('session.cookie_samesite', $sameSite);
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');

        session_set_cookie_params([ // NOSONAR
            'lifetime' => (int) $this->config->get('session.lifetime', 7200),
            'path'     => '/',
            'domain'   => '',
            'secure'   => $secure,
            'httponly' => $httpOnly,
            'samesite' => $sameSite,
        ]);

        session_start();
    }

    /**
     * @return array<string, mixed>
     */
    public function attempt(string $username, string $password, string $ipAddress, string $userAgent): array
    {
        if (! $this->rateLimiter->allow('login', $ipAddress)) {
            throw new AuthenticationException('Too many login attempts.');
        }

        $user = $this->users->findByUsername($username);
        if ($user === null) {
            $this->rateLimiter->hit('login', $ipAddress);

            throw new AuthenticationException('Invalid credentials.');
        }

        if (! isset($user['password_hash']) || ! password_verify($password, (string) $user['password_hash'])) {
            if (isset($user['id'])) {
                $this->users->incrementFailedAttempt(
                    (int) $user['id'],
                    (int) $this->config->get('security.brute_force_max', 5),
                    (int) $this->config->get('security.brute_force_lockout', 900)
                );
            }

            $this->rateLimiter->hit('login', $ipAddress);

            throw new AuthenticationException('Invalid credentials.');
        }

        if (
            isset($user['locked_until'])
            && is_string($user['locked_until'])
            && $user['locked_until'] !== ''
            && strtotime($user['locked_until']) > time()
        ) {
            throw new AuthenticationException('Account is temporarily locked.');
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['role']    = (string) ($user['role_name'] ?? 'viewer');

        $this->users->updateLastLogin((int) $user['id'], $ipAddress);

        $sessionId = session_id();
        if (is_string($sessionId) && $sessionId !== '') {
            $this->sessions->store($sessionId, (int) $user['id'], $ipAddress, $userAgent, time());
        }

        $this->rateLimiter->clear('login', $ipAddress);

        return $user;
    }

    public function logout(): void
    {
        $sessionId = session_id();
        if (is_string($sessionId) && $sessionId !== '') {
            $this->sessions->delete($sessionId);
        }

        $_SESSION = [];

        $useCookies = ini_get('session.use_cookies');
        if ($useCookies !== false && $useCookies !== '' && $useCookies !== '0') {
            $sessionName = session_name();
            if (is_string($sessionName)) {
                $params = session_get_cookie_params();
                setcookie(
                    $sessionName,
                    '',
                    time() - 42000,
                    $params['path'],
                    $params['domain'],
                    $params['secure'],
                    $params['httponly']
                );
            }
        }

        session_destroy();
    }

    public function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']) && is_int($_SESSION['user_id']);
    }
}
