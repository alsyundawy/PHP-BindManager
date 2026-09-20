<?php

declare(strict_types=1);

use App\Support\View;
use Nyholm\Psr7\Response;

return [
    [
        'method'     => 'GET',
        'path'       => '/zones',
        'auth'       => true,
        'rate_limit' => 'web',
        'handler'    => static fn (): Response => new Response(
            200,
            ['Content-Type' => 'text/html; charset=UTF-8'],
            View::render('zones/index')
        ),
    ],
    [
        'method'     => 'GET',
        'path'       => '/records',
        'auth'       => true,
        'rate_limit' => 'web',
        'handler'    => static fn (): Response => new Response(
            200,
            ['Content-Type' => 'text/html; charset=UTF-8'],
            View::render('records/index')
        ),
    ],
];
