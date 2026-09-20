<?php

declare(strict_types=1);

use App\Support\View;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;

$htmlHeaders      = ['Content-Type' => 'text/html; charset=UTF-8'];
$loginUri         = '/login';
$routeProfile     = '/profile';
$routeProfileTotp = '/profile/totp';
$routeUsers       = '/users';
$routeUsersCreate = '/users/create';

return [
    [
        'method'     => 'GET',
        'path'       => '/',
        'auth'       => false,
        'rate_limit' => 'web',
        'handler'    => static function () use ($htmlHeaders): Response {
            $html = View::render('welcome', [
                'appName' => 'PHP-BindManager',
            ]);

            return new Response(200, $htmlHeaders, $html);
        },
    ],
    [
        'method'     => 'GET',
        'path'       => $loginUri,
        'auth'       => false,
        'rate_limit' => 'web',
        'handler'    => static function (
            ServerRequestInterface $request
        ) use ($htmlHeaders): Response {
            $flashError = '';

            if (isset($_SESSION['flash_error']) && is_string($_SESSION['flash_error'])) {
                $flashError = $_SESSION['flash_error'];
                unset($_SESSION['flash_error']);
            }

            /** @var \App\Container\Container|null $container */
            $container = $request->getAttribute('container');
            /** @var \App\Services\Auth\CsrfService|null $csrfService */
            $csrfService = $container instanceof \App\Container\Container
                ? $container->get(\App\Services\Auth\CsrfService::class)
                : null;
            $csrfToken = $csrfService instanceof \App\Services\Auth\CsrfService
                ? $csrfService->token()
                : (string) ($_SESSION['_csrf']['value'] ?? '');

            $html = View::render('auth/login', [
                'csrfToken'  => $csrfToken,
                'flashError' => $flashError,
            ]);

            return new Response(200, $htmlHeaders, $html);
        },
    ],
    [
        'method'     => 'POST',
        'path'       => $loginUri,
        'auth'       => false,
        'rate_limit' => 'web',
        'handler'    => static function (
            ServerRequestInterface $request
        ) use ($loginUri): Response {
            $body      = (array) ($request->getParsedBody() ?? []);
            $username  = trim((string) ($body['username'] ?? ''));
            $password  = (string) ($body['password'] ?? '');
            $ip        = (string) ($request->getServerParams()['REMOTE_ADDR'] ?? '127.0.0.1');
            $userAgent = (string) ($request->getServerParams()['HTTP_USER_AGENT'] ?? '');
            /** @var \App\Container\Container $container */
            $container = $request->getAttribute('container');

            try {
                /** @var \App\Services\Auth\AuthenticationService $auth */
                $auth = $container->get(\App\Services\Auth\AuthenticationService::class);
                $auth->attempt($username, $password, $ip, $userAgent);

                return new Response(302, ['Location' => '/dashboard']);
            } catch (\App\Exceptions\AuthenticationException $e) {
                $_SESSION['flash_error'] = $e->getMessage();
            } catch (\Throwable) {
                $_SESSION['flash_error'] = 'An unexpected error occurred.';
            }

            return new Response(302, ['Location' => $loginUri]);
        },
    ],
    [
        'method'     => 'GET',
        'path'       => '/dashboard',
        'auth'       => true,
        'rate_limit' => 'web',
        'handler'    => static function (ServerRequestInterface $request) use ($htmlHeaders): Response {
            /** @var \App\Container\Container $container */
            $container = $request->getAttribute('container');
            /** @var \App\Repositories\Dns\ZoneRepository $zones */
            $zones = $container->get(\App\Repositories\Dns\ZoneRepository::class);
            /** @var \App\Repositories\Dns\RecordRepository $records */
            $records = $container->get(\App\Repositories\Dns\RecordRepository::class);

            $zoneList    = $zones->all();
            $zoneCount   = count($zoneList);
            $recordCount = 0;

            foreach ($zoneList as $zone) {
                $recordCount += count($records->forZone((int) ($zone['id'] ?? 0)));
            }

            $bind9Healthy = isBind9Active();
            $recentZones  = array_slice($zoneList, 0, 5);

            $html = View::render('dashboard/index', [
                'zoneCount'    => $zoneCount,
                'recordCount'  => $recordCount,
                'bind9Healthy' => $bind9Healthy,
                'recentZones'  => $recentZones,
            ]);

            return new Response(200, $htmlHeaders, $html);
        },
    ],
    [
        'method'     => 'POST',
        'path'       => '/logout',
        'auth'       => false,
        'rate_limit' => 'web',
        'handler'    => static function () use ($loginUri): Response {
            if (session_status() === PHP_SESSION_ACTIVE) {
                $_SESSION = [];
                session_destroy();
            }

            return new Response(302, ['Location' => $loginUri]);
        },
    ],
    [
        'method'     => 'GET',
        'path'       => '/logout',
        'auth'       => false,
        'rate_limit' => 'web',
        'handler'    => static function () use ($loginUri): Response {
            if (session_status() === PHP_SESSION_ACTIVE) {
                $_SESSION = [];
                session_destroy();
            }

            return new Response(302, ['Location' => $loginUri]);
        },
    ],
    // --- Profile ---
    [
        'method'     => 'GET',
        'path'       => $routeProfile,
        'auth'       => true,
        'rate_limit' => 'web',
        'handler'    => static function (ServerRequestInterface $req) use ($htmlHeaders): Response {
            /** @var \App\Container\Container $c */
            $c = $req->getAttribute('container');
            /** @var \App\Repositories\Auth\UserRepository $userRepo */
            $userRepo = $c->get(\App\Repositories\Auth\UserRepository::class);
            $userId   = (int) ($_SESSION['user_id'] ?? 0);
            $user     = $userRepo->findById($userId);
            $csrf     = (string) ($_SESSION['_csrf']['value'] ?? '');

            $html = View::render('profile/index', [
                'user'      => $user ?? [],
                'csrfToken' => $csrf,
            ]);

            return new Response(200, $htmlHeaders, $html);
        },
    ],
    [
        'method'     => 'POST',
        'path'       => $routeProfile,
        'auth'       => true,
        'rate_limit' => 'web',
        'handler'    => static function (ServerRequestInterface $req) use ($routeProfile): Response {
            /** @var \App\Container\Container $c */
            $c = $req->getAttribute('container');
            /** @var \App\Repositories\Auth\UserRepository $userRepo */
            $userRepo = $c->get(\App\Repositories\Auth\UserRepository::class);
            $userId   = (int) ($_SESSION['user_id'] ?? 0);
            $body     = (array) ($req->getParsedBody() ?? []);
            $username = trim((string) ($body['username'] ?? ''));
            $email    = trim((string) ($body['email'] ?? ''));

            if ($username === '' || $email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                $_SESSION['flash_error'] = 'Valid username and email are required.';

                return new Response(302, ['Location' => $routeProfile]);
            }

            $userRepo->update($userId, [
                'username' => $username,
                'email'    => $email,
            ]);

            $_SESSION['flash_success'] = 'Profile updated successfully.';

            return new Response(302, ['Location' => $routeProfile]);
        },
    ],
    [
        'method'     => 'POST',
        'path'       => '/profile/password',
        'auth'       => true,
        'rate_limit' => 'web',
        'handler'    => static function (ServerRequestInterface $req) use ($routeProfile): Response {
            /** @var \App\Container\Container $c */
            $c = $req->getAttribute('container');
            /** @var \App\Repositories\Auth\UserRepository $userRepo */
            $userRepo = $c->get(\App\Repositories\Auth\UserRepository::class);
            $userId   = (int) ($_SESSION['user_id'] ?? 0);
            $user     = $userRepo->findById($userId);
            $body     = (array) ($req->getParsedBody() ?? []);

            $current = (string) ($body['current_password'] ?? '');
            $newPass = (string) ($body['new_password'] ?? '');
            $confirm = (string) ($body['confirm_password'] ?? '');

            if ($user === null || ! password_verify($current, (string) ($user['password_hash'] ?? ''))) {
                $_SESSION['flash_error'] = 'Current password is incorrect.';

                return new Response(302, ['Location' => $routeProfile]);
            }

            if (strlen($newPass) < 8 || $newPass !== $confirm) {
                $_SESSION['flash_error'] = 'New password must be >= 8 characters and match confirmation.';

                return new Response(302, ['Location' => $routeProfile]);
            }

            $algo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT;
            $hash = password_hash($newPass, $algo);
            $userRepo->updatePassword($userId, $hash);

            $_SESSION['flash_success'] = 'Password updated successfully.';

            return new Response(302, ['Location' => $routeProfile]);
        },
    ],
    // --- Profile 2FA TOTP ---
    [
        'method'     => 'GET',
        'path'       => $routeProfileTotp,
        'auth'       => true,
        'rate_limit' => 'web',
        'handler'    => static function (ServerRequestInterface $req) use ($htmlHeaders): Response {
            /** @var \App\Container\Container $c */
            $c = $req->getAttribute('container');
            /** @var \App\Services\Auth\TotpService $totpService */
            $totpService = $c->get(\App\Services\Auth\TotpService::class);
            /** @var \App\Repositories\Auth\UserRepository $userRepo */
            $userRepo = $c->get(\App\Repositories\Auth\UserRepository::class);

            $userId = (int) ($_SESSION['user_id'] ?? 0);
            $user   = $userRepo->findById($userId);
            $secret = $totpService->generateSecret();
            $email  = (string) ($user['email'] ?? 'admin@example.com');
            $uri    = $totpService->getProvisioningUri($secret, $email, 'PHP-BindManager');
            $csrf   = (string) ($_SESSION['_csrf']['value'] ?? '');

            $flashSuccess = isset($_SESSION['flash_success']) && is_string($_SESSION['flash_success'])
                ? $_SESSION['flash_success'] : null;
            $flashError = isset($_SESSION['flash_error']) && is_string($_SESSION['flash_error'])
                ? $_SESSION['flash_error'] : null;
            unset($_SESSION['flash_success'], $_SESSION['flash_error']);

            $html = View::render('profile/totp', [
                'secret'          => $secret,
                'provisioningUri' => $uri,
                'csrfToken'       => $csrf,
                'flashSuccess'    => $flashSuccess,
                'flashError'      => $flashError,
            ]);

            return new Response(200, $htmlHeaders, $html);
        },
    ],
    [
        'method'     => 'POST',
        'path'       => $routeProfileTotp,
        'auth'       => true,
        'rate_limit' => 'web',
        'handler'    => static function (ServerRequestInterface $req) use (
            $routeProfile,
            $routeProfileTotp
        ): Response {
            /** @var \App\Container\Container $c */
            $c = $req->getAttribute('container');
            /** @var \App\Services\Auth\TotpService $totpService */
            $totpService = $c->get(\App\Services\Auth\TotpService::class);
            $body        = (array) ($req->getParsedBody() ?? []);
            $secret      = trim((string) ($body['secret'] ?? ''));
            $code        = trim((string) ($body['code'] ?? ''));

            if ($secret === '' || ! $totpService->verify($secret, $code)) {
                $_SESSION['flash_error'] = 'Invalid 6-digit TOTP verification code. Please try again.';

                return new Response(302, ['Location' => $routeProfileTotp]);
            }

            $_SESSION['flash_success'] = 'Two-factor authentication (2FA) successfully verified and enabled!';

            return new Response(302, ['Location' => $routeProfile]);
        },
    ],
    // --- User Management (Admin Only) ---
    [
        'method'     => 'GET',
        'path'       => $routeUsers,
        'auth'       => true,
        'role'       => 'admin',
        'rate_limit' => 'web',
        'handler'    => static function (ServerRequestInterface $req) use ($htmlHeaders): Response {
            /** @var \App\Container\Container $c */
            $c = $req->getAttribute('container');
            /** @var \App\Repositories\Auth\UserRepository $userRepo */
            $userRepo = $c->get(\App\Repositories\Auth\UserRepository::class);
            $users    = $userRepo->all();
            $csrf     = (string) ($_SESSION['_csrf']['value'] ?? '');

            $html = View::render('users/index', [
                'users'     => $users,
                'csrfToken' => $csrf,
            ]);

            return new Response(200, $htmlHeaders, $html);
        },
    ],
    [
        'method'     => 'GET',
        'path'       => $routeUsersCreate,
        'auth'       => true,
        'role'       => 'admin',
        'rate_limit' => 'web',
        'handler'    => static function (ServerRequestInterface $req) use ($htmlHeaders): Response {
            /** @var \App\Container\Container $c */
            $c = $req->getAttribute('container');
            /** @var \App\Repositories\Auth\RoleRepository $roleRepo */
            $roleRepo = $c->get(\App\Repositories\Auth\RoleRepository::class);
            $roles    = $roleRepo->all();
            $csrf     = (string) ($_SESSION['_csrf']['value'] ?? '');

            $html = View::render('users/create', [
                'roles'     => $roles,
                'csrfToken' => $csrf,
            ]);

            return new Response(200, $htmlHeaders, $html);
        },
    ],
    [
        'method'     => 'POST',
        'path'       => $routeUsers,
        'auth'       => true,
        'role'       => 'admin',
        'rate_limit' => 'web',
        'handler'    => static function (ServerRequestInterface $req) use ($routeUsers, $routeUsersCreate): Response {
            /** @var \App\Container\Container $c */
            $c = $req->getAttribute('container');
            /** @var \App\Repositories\Auth\UserRepository $userRepo */
            $userRepo = $c->get(\App\Repositories\Auth\UserRepository::class);
            $body     = (array) ($req->getParsedBody() ?? []);

            $username = trim((string) ($body['username'] ?? ''));
            $email    = trim((string) ($body['email'] ?? ''));
            $password = (string) ($body['password'] ?? '');
            $roleId   = (int) ($body['role_id'] ?? 2);
            $isActive = isset($body['is_active']) ? 1 : 0;

            if ($username === '' || $email === '' || strlen($password) < 8) {
                $_SESSION['flash_error'] = 'All fields are required. Password must be >= 8 chars.';

                return new Response(302, ['Location' => $routeUsersCreate]);
            }

            if ($userRepo->findByUsername($username) !== null) {
                $_SESSION['flash_error'] = 'Username is already in use.';

                return new Response(302, ['Location' => $routeUsersCreate]);
            }

            $algo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT;
            $userRepo->create([
                'username'      => $username,
                'email'         => $email,
                'password_hash' => password_hash($password, $algo),
                'role_id'       => $roleId,
                'is_active'     => $isActive,
            ]);

            $_SESSION['flash_success'] = "User '{$username}' created successfully.";

            return new Response(302, ['Location' => $routeUsers]);
        },
    ],
    [
        'method'     => 'GET',
        'path'       => '/users/{id}/edit',
        'auth'       => true,
        'role'       => 'admin',
        'rate_limit' => 'web',
        'handler'    => static function (ServerRequestInterface $req) use ($htmlHeaders, $routeUsers): Response {
            /** @var \App\Container\Container $c */
            $c = $req->getAttribute('container');
            /** @var \App\Repositories\Auth\UserRepository $userRepo */
            $userRepo = $c->get(\App\Repositories\Auth\UserRepository::class);
            /** @var \App\Repositories\Auth\RoleRepository $roleRepo */
            $roleRepo = $c->get(\App\Repositories\Auth\RoleRepository::class);

            $uid  = (int) ($req->getAttribute('id') ?? 0);
            $user = $userRepo->findById($uid);

            if ($user === null) {
                $_SESSION['flash_error'] = 'User not found.';

                return new Response(302, ['Location' => $routeUsers]);
            }

            $roles = $roleRepo->all();
            $csrf  = (string) ($_SESSION['_csrf']['value'] ?? '');

            $html = View::render('users/edit', [
                'user'      => $user,
                'roles'     => $roles,
                'csrfToken' => $csrf,
            ]);

            return new Response(200, $htmlHeaders, $html);
        },
    ],
    [
        'method'     => 'POST',
        'path'       => '/users/{id}',
        'auth'       => true,
        'role'       => 'admin',
        'rate_limit' => 'web',
        'handler'    => static function (ServerRequestInterface $req) use ($routeUsers): Response {
            /** @var \App\Container\Container $c */
            $c = $req->getAttribute('container');
            /** @var \App\Repositories\Auth\UserRepository $userRepo */
            $userRepo = $c->get(\App\Repositories\Auth\UserRepository::class);

            $uid  = (int) ($req->getAttribute('id') ?? 0);
            $user = $userRepo->findById($uid);

            if ($user === null) {
                $_SESSION['flash_error'] = 'User not found.';

                return new Response(302, ['Location' => $routeUsers]);
            }

            $body     = (array) ($req->getParsedBody() ?? []);
            $username = trim((string) ($body['username'] ?? ''));
            $email    = trim((string) ($body['email'] ?? ''));
            $roleId   = (int) ($body['role_id'] ?? 2);
            $isActive = isset($body['is_active']) ? 1 : 0;
            $password = (string) ($body['password'] ?? '');

            $updateData = [
                'username'  => $username,
                'email'     => $email,
                'role_id'   => $roleId,
                'is_active' => $isActive,
            ];

            $userRepo->update($uid, $updateData);

            if (strlen($password) >= 8) {
                $algo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT;
                $userRepo->updatePassword($uid, password_hash($password, $algo));
            }

            $_SESSION['flash_success'] = "User '{$username}' updated successfully.";

            return new Response(302, ['Location' => $routeUsers]);
        },
    ],
    [
        'method'     => 'POST',
        'path'       => '/users/{id}/delete',
        'auth'       => true,
        'role'       => 'admin',
        'rate_limit' => 'web',
        'handler'    => static function (ServerRequestInterface $req) use ($routeUsers): Response {
            /** @var \App\Container\Container $c */
            $c = $req->getAttribute('container');
            /** @var \App\Repositories\Auth\UserRepository $userRepo */
            $userRepo = $c->get(\App\Repositories\Auth\UserRepository::class);
            $uid      = (int) ($req->getAttribute('id') ?? 0);

            if ($uid === (int) ($_SESSION['user_id'] ?? 0)) {
                $_SESSION['flash_error'] = 'You cannot delete your own account.';

                return new Response(302, ['Location' => $routeUsers]);
            }

            $userRepo->delete($uid);
            $_SESSION['flash_success'] = 'User deleted successfully.';

            return new Response(302, ['Location' => $routeUsers]);
        },
    ],
];
