<?php

declare(strict_types=1);

use App\Support\Env;

return [
    'name'     => Env::get('APP_NAME', 'PHP-BindManager'),
    'version'  => Env::get('APP_VERSION', '1.0.1'),
    'env'      => Env::get('APP_ENV', 'production'),
    'debug'    => Env::bool('APP_DEBUG', false),
    'url'      => Env::get('APP_URL', 'http://localhost'),
    'timezone' => Env::get('APP_TIMEZONE', 'UTC'),
    'locale'   => Env::get('APP_LOCALE', 'en'),
];
