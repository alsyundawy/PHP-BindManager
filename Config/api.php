<?php

declare(strict_types=1);

use App\Support\Env;

return [
    'enabled' => Env::bool('API_ENABLED', true),
    'version' => Env::get('API_VERSION', 'v1'),
    'rate_limit' => (int) Env::get('API_RATE_LIMIT', 300),
    'token_lifetime' => (int) Env::get('API_TOKEN_LIFETIME', 31536000),
];
