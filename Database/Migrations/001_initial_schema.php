<?php

declare(strict_types=1);

use PDO;

return [
    <<<'SQL'
    CREATE TABLE IF NOT EXISTS roles (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL UNIQUE,
        description TEXT,
        permissions TEXT NOT NULL,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    );
    SQL,
    <<<'SQL'
    CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        role_id INTEGER NOT NULL,
        username TEXT NOT NULL UNIQUE,
        email TEXT NOT NULL UNIQUE,
        password_hash TEXT NOT NULL,
        last_login_at TEXT DEFAULT NULL,
        last_login_ip TEXT DEFAULT NULL,
        failed_attempts INTEGER NOT NULL DEFAULT 0,
        locked_until TEXT DEFAULT NULL,
        is_active INTEGER NOT NULL DEFAULT 1,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT
    );
    SQL,
    <<<'SQL'
    CREATE TABLE IF NOT EXISTS user_sessions (
        session_id TEXT PRIMARY KEY,
        user_id INTEGER NOT NULL,
        ip_address TEXT NOT NULL,
        user_agent TEXT NOT NULL,
        last_activity_at INTEGER NOT NULL,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    );
    SQL,
    <<<'SQL'
    CREATE TABLE IF NOT EXISTS rate_limits (
        identifier TEXT NOT NULL,
        action TEXT NOT NULL,
        attempts INTEGER NOT NULL DEFAULT 0,
        reset_at INTEGER NOT NULL,
        PRIMARY KEY (identifier, action)
    );
    SQL,
    <<<'SQL'
    CREATE TABLE IF NOT EXISTS audit_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER DEFAULT NULL,
        action TEXT NOT NULL,
        entity_type TEXT NOT NULL,
        entity_id TEXT DEFAULT NULL,
        old_value TEXT DEFAULT NULL,
        new_value TEXT DEFAULT NULL,
        ip_address TEXT NOT NULL,
        user_agent TEXT NOT NULL,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    );
    SQL,
    <<<'SQL'
    CREATE INDEX IF NOT EXISTS idx_users_username ON users(username);
    SQL,
    <<<'SQL'
    CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
    SQL,
    <<<'SQL'
    CREATE INDEX IF NOT EXISTS idx_user_sessions_user_id ON user_sessions(user_id);
    SQL,
    <<<'SQL'
    CREATE INDEX IF NOT EXISTS idx_rate_limits_identifier_action ON rate_limits(identifier, action);
    SQL,
    <<<'SQL'
    CREATE INDEX IF NOT EXISTS idx_audit_logs_user_id ON audit_logs(user_id);
    SQL,
];
