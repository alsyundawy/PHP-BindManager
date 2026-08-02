<?php

declare(strict_types=1);

use App\Support\Env;

return [
    'level' => Env::get('LOG_LEVEL', 'warning'),
    'path' => Env::get('LOG_PATH', dirname(__DIR__) . '/Storage/Logs'),
    'max_files' => (int) Env::get('LOG_MAX_FILES', 30),
];
