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

        session_name((string) $this->config->get('session.name'));
        session_set_cookie_params([
            'lifetime' => (int) $this->config->get('session.lifetime'),
            'path' => '/',
            'secure' => (bool) $this->config->get('session.secure'),
            'httponly' => (bool) $this->config->get('session.httponly'),
            'samesite' => (string) $this->config->get('session.samesite'),
        ]);
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        session_start();
    }

    public function attempt(string $username, string $password, string $ipAddress, string $userAgent): array
    {
        if (! $this->rateLimiter->allow('login', $ipAddress)) {
            throw new AuthenticationException('Too many login attempts.');
        }

        $user = $this->users->findByUsername($username);
        if ($user === null || ! isset($user['password_hash']) || ! password_verify($password, (string) $user['password_hash'])) {
            if ($user !== null && isset($user['id'])) {
                $this->users->incrementFailedAttempt(
                    (int) $user['id'],
                    (int) $this->config->get('security.brute_force_max'),
                    (int) $this->config->get('security.brute_force_lockout')
                );
            }

            $this->rateLimiter->hit('login', $ipAddress);
            throw new AuthenticationException('Invalid credentials.');
        }

        if (! empty($user['locked_until']) && strtotime((string) $user['locked_until']) > time()) {
            throw new AuthenticationException('Account is temporarily locked.');
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['role'] = (string) ($user['role_name'] ?? 'viewer');

        $this->users->updateLastLogin((int) $user['id'], $ipAddress);
        $this->sessions->store(session_id(), (int) $user['id'], $ipAddress, $userAgent, time());
        $this->rateLimiter->clear('login', $ipAddress);

        return $user;
    }

    public function logout(): void
    {
        $sessionId = session_id();
        if ($sessionId !== '') {
            $this->sessions->delete($sessionId);
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', (bool) $params['secure'], (bool) $params['httponly']);
        }

        session_destroy();
    }

    public function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']) && is_int($_SESSION['user_id']);
    }
}
