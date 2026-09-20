<?php

declare(strict_types=1);

return [
    <<<'SQL'
        CREATE TABLE IF NOT EXISTS zone_templates (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL UNIQUE,
            description TEXT DEFAULT NULL,
            records_json TEXT NOT NULL,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        );
        SQL,
    <<<'SQL'
        CREATE TABLE IF NOT EXISTS zone_history (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            zone_id INTEGER NOT NULL,
            serial INTEGER NOT NULL DEFAULT 1,
            zone_content TEXT NOT NULL,
            change_summary TEXT DEFAULT NULL,
            created_by INTEGER DEFAULT NULL,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (zone_id) REFERENCES zones(id) ON DELETE CASCADE,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        );
        SQL,
    <<<'SQL'
        CREATE TABLE IF NOT EXISTS webhooks (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            url TEXT NOT NULL,
            secret TEXT DEFAULT NULL,
            events TEXT NOT NULL DEFAULT 'zone.updated,record.created,record.deleted',
            is_active INTEGER NOT NULL DEFAULT 1,
            last_status INTEGER DEFAULT NULL,
            last_triggered_at TEXT DEFAULT NULL,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        );
        SQL,
    <<<'SQL'
        CREATE INDEX IF NOT EXISTS idx_zone_history_zone_id ON zone_history(zone_id, created_at);
        SQL,
    <<<'SQL'
        CREATE INDEX IF NOT EXISTS idx_webhooks_active ON webhooks(is_active);
        SQL,
];
