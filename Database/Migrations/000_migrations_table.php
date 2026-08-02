<?php

declare(strict_types=1);

return [
    <<<'SQL'
    CREATE TABLE IF NOT EXISTS migrations (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        filename TEXT NOT NULL UNIQUE,
        batch INTEGER NOT NULL,
        executed_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    );
    SQL,
];
