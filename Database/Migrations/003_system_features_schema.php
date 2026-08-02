<?php

declare(strict_types=1);

return [
    <<<'SQL'
    CREATE TABLE IF NOT EXISTS backups (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        backup_type TEXT NOT NULL CHECK(backup_type IN ('database','zone','full')),
        source_name TEXT NOT NULL,
        file_path TEXT NOT NULL,
        sha256 TEXT NOT NULL,
        size_bytes INTEGER NOT NULL,
        created_by INTEGER DEFAULT NULL,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE SET NULL
    );
    SQL,
    <<<'SQL'
    CREATE TABLE IF NOT EXISTS activity_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER DEFAULT NULL,
        category TEXT NOT NULL,
        action TEXT NOT NULL,
        message TEXT NOT NULL,
        context TEXT DEFAULT NULL,
        ip_address TEXT DEFAULT NULL,
        user_agent TEXT DEFAULT NULL,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL
    );
    SQL,
    <<<'SQL'
    CREATE TABLE IF NOT EXISTS notifications (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER DEFAULT NULL,
        severity TEXT NOT NULL DEFAULT 'info' CHECK(severity IN ('info','success','warning','error')),
        title TEXT NOT NULL,
        message TEXT NOT NULL,
        read_at TEXT DEFAULT NULL,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
    );
    SQL,
    <<<'SQL'
    CREATE TABLE IF NOT EXISTS api_tokens (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        name TEXT NOT NULL,
        token_hash TEXT NOT NULL UNIQUE,
        scopes TEXT NOT NULL,
        expires_at TEXT DEFAULT NULL,
        last_used_at TEXT DEFAULT NULL,
        revoked_at TEXT DEFAULT NULL,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
    );
    SQL,
    <<<'SQL'
    CREATE TABLE IF NOT EXISTS system_metrics (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        metric_name TEXT NOT NULL,
        metric_value REAL NOT NULL,
        dimensions TEXT DEFAULT NULL,
        recorded_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    );
    SQL,
    <<<'SQL'
    CREATE INDEX IF NOT EXISTS idx_activity_logs_created_at ON activity_logs(created_at);
    SQL,
    <<<'SQL'
    CREATE INDEX IF NOT EXISTS idx_notifications_user_read ON notifications(user_id, read_at);
    SQL,
    <<<'SQL'
    CREATE INDEX IF NOT EXISTS idx_system_metrics_name_time ON system_metrics(metric_name, recorded_at);
    SQL,
];
