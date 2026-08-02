<?php

declare(strict_types=1);

return [
    <<<'SQL'
    CREATE TABLE IF NOT EXISTS acls (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL UNIQUE,
        entries TEXT NOT NULL,
        description TEXT DEFAULT NULL,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    );
    SQL,
    <<<'SQL'
    CREATE TABLE IF NOT EXISTS dns_views (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL UNIQUE,
        match_clients TEXT NOT NULL,
        description TEXT DEFAULT NULL,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    );
    SQL,
    <<<'SQL'
    CREATE TABLE IF NOT EXISTS zones (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        zone_type TEXT NOT NULL CHECK(zone_type IN ('master','slave','forward','hint','stub','static-stub','redirect','delegation-only')),
        file_path TEXT NOT NULL,
        view_id INTEGER DEFAULT NULL,
        dnssec_enabled INTEGER NOT NULL DEFAULT 0,
        serial INTEGER NOT NULL DEFAULT 1,
        refresh INTEGER NOT NULL DEFAULT 3600,
        retry INTEGER NOT NULL DEFAULT 900,
        expire INTEGER NOT NULL DEFAULT 604800,
        minimum INTEGER NOT NULL DEFAULT 300,
        status TEXT NOT NULL DEFAULT 'draft' CHECK(status IN ('draft','active','error','disabled')),
        created_by INTEGER DEFAULT NULL,
        updated_by INTEGER DEFAULT NULL,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(name, view_id),
        FOREIGN KEY(view_id) REFERENCES dns_views(id) ON DELETE SET NULL,
        FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE SET NULL,
        FOREIGN KEY(updated_by) REFERENCES users(id) ON DELETE SET NULL
    );
    SQL,
    <<<'SQL'
    CREATE TABLE IF NOT EXISTS dns_records (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        zone_id INTEGER NOT NULL,
        name TEXT NOT NULL,
        record_type TEXT NOT NULL CHECK(record_type IN ('A','AAAA','CAA','CNAME','DS','HTTPS','MX','NAPTR','NS','PTR','SOA','SRV','SSHFP','SVCB','TLSA','TXT')),
        ttl INTEGER NOT NULL DEFAULT 3600 CHECK(ttl > 0 AND ttl <= 2147483647),
        priority INTEGER DEFAULT NULL,
        content TEXT NOT NULL,
        disabled INTEGER NOT NULL DEFAULT 0,
        created_by INTEGER DEFAULT NULL,
        updated_by INTEGER DEFAULT NULL,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(zone_id) REFERENCES zones(id) ON DELETE CASCADE,
        FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE SET NULL,
        FOREIGN KEY(updated_by) REFERENCES users(id) ON DELETE SET NULL
    );
    SQL,
    <<<'SQL'
    CREATE TABLE IF NOT EXISTS dnssec_keys (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        zone_id INTEGER NOT NULL,
        key_role TEXT NOT NULL CHECK(key_role IN ('ksk','zsk','csk')),
        key_tag INTEGER NOT NULL,
        algorithm INTEGER NOT NULL,
        key_file TEXT NOT NULL,
        public_key TEXT DEFAULT NULL,
        status TEXT NOT NULL DEFAULT 'active' CHECK(status IN ('active','retired','revoked')),
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(zone_id) REFERENCES zones(id) ON DELETE CASCADE
    );
    SQL,
    <<<'SQL'
    CREATE INDEX IF NOT EXISTS idx_zones_name ON zones(name);
    SQL,
    <<<'SQL'
    CREATE INDEX IF NOT EXISTS idx_records_zone_name_type ON dns_records(zone_id, name, record_type);
    SQL,
    <<<'SQL'
    CREATE INDEX IF NOT EXISTS idx_dnssec_keys_zone ON dnssec_keys(zone_id);
    SQL,
];
