<?php

declare(strict_types=1);

use App\Http\Router;
use App\Services\Auth\AuthenticationService;
use App\Services\Auth\CsrfService;
use App\Support\View;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;

return [
    [
        'method' => 'GET',
        'path' => '/',
        'auth' => false,
        'rate_limit' => 'web',
        'handler' => static function (ServerRequestInterface $request): Response {
            $html = View::render('welcome', [
                'appName' => 'PHP-BindManager',
            ]);

            return new Response(200, ['Content-Type' => 'text/html; charset=UTF-8'], $html);
        },
    ],
    [
        'method' => 'GET',
        'path' => '/login',
        'auth' => false,
        'rate_limit' => 'web',
        'handler' => static function (ServerRequestInterface $request): Response {
            $html = View::render('auth/login', [
                'csrfToken' => ($_SESSION['_csrf']['value'] ?? ''),
            ]);

            return new Response(200, ['Content-Type' => 'text/html; charset=UTF-8'], $html);
        },
    ],
];
