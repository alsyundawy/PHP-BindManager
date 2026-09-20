<?php

declare(strict_types=1);

use App\Support\View;
use Nyholm\Psr7\Response;

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
        'path'       => '/login',
        'auth'       => false,
        'rate_limit' => 'web',
        'handler'    => static function () use ($htmlHeaders): Response {
            $html = View::render('auth/login', [
                'csrfToken' => ($_SESSION['_csrf']['value'] ?? ''),
            ]);

            return new Response(200, $htmlHeaders, $html);
        },
    ],
    [
        'method'     => 'GET',
        'path'       => '/dashboard',
        'auth'       => true,
        'rate_limit' => 'web',
        'handler'    => static function () use ($htmlHeaders): Response {
            $html = View::render('dashboard/index', [
                'appName' => 'PHP-BindManager',
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
