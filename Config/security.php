<?php

declare(strict_types=1);

use App\Support\Env;

return [
    'csrf_token_lifetime' => (int) Env::get('CSRF_TOKEN_LIFETIME', 3600),
    'rate_limit_login' => (int) Env::get('RATE_LIMIT_LOGIN', 10),
    'brute_force_max' => (int) Env::get('BRUTE_FORCE_MAX', 5),
    'brute_force_lockout' => (int) Env::get('BRUTE_FORCE_LOCKOUT', 900),
    'password_min_length' => (int) Env::get('PASSWORD_MIN_LENGTH', 12),
];
