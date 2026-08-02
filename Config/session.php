<?php

declare(strict_types=1);

use App\Support\Env;

return [
    'name' => Env::get('SESSION_NAME', 'pbm_session'),
    'lifetime' => (int) Env::get('SESSION_LIFETIME', 7200),
    'secure' => Env::bool('SESSION_SECURE', true),
    'httponly' => Env::bool('SESSION_HTTPONLY', true),
    'samesite' => Env::get('SESSION_SAMESITE', 'Strict'),
];
