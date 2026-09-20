<?php

declare(strict_types=1);

use App\Support\View;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;

$htmlHeaders = ['Content-Type' => 'text/html; charset=UTF-8'];
$loginUri    = '/login';

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
        'handler'    => static function () use ($htmlHeaders): Response {
            $flashError = '';

            if (isset($_SESSION['flash_error']) && is_string($_SESSION['flash_error'])) {
                $flashError = $_SESSION['flash_error'];
                unset($_SESSION['flash_error']);
            }

            $html = View::render('auth/login', [
                'csrfToken'  => ($_SESSION['_csrf']['value'] ?? ''),
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

                return new Response(302, ['Location' => $loginUri]);
            } catch (\Throwable) {
                $_SESSION['flash_error'] = 'An unexpected error occurred.';

                return new Response(302, ['Location' => $loginUri]);
            }
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

            /** @psalm-suppress ForbiddenCode */
            $bind9Raw     = shell_exec('systemctl is-active named 2>/dev/null');
            $bind9Healthy = is_string($bind9Raw) && trim($bind9Raw) === 'active';
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
];
