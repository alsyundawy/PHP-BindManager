<?php

declare(strict_types=1);

use App\Support\Env;

return [
    'path' => Env::get('DB_PATH', dirname(__DIR__) . '/Database/bindmanager.sqlite'),
    'wal' => Env::bool('DB_WAL', true),
    'foreign_keys' => Env::bool('DB_FOREIGN_KEYS', true),
];
