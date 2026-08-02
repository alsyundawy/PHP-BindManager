# Fase 5 — System Features

The system feature foundation covers backup/restore, database and zone optimization, statistics surfaces, audit/activity persistence, notifications, API token hashing, and API documentation.

## Safety
- SQLite `VACUUM INTO` creates a separate backup copy without mutating the live database; SQLite documents it as a backup alternative that produces a compact copy. [web:224]
- `PRAGMA optimize`, VACUUM, and WAL checkpoint are exposed through an explicit service boundary and must be restricted to authorized operators.
- BIND statistics should be collected through configured `statistics-file`, `statistics-channel`, or controlled `rndc stats`; BIND documents `rndc stats` as the mechanism for dumping server statistics. [web:285]
- API tokens are displayed only once and stored as SHA-256 digests; revocation and expiry are checked during authentication.
- Backup restore uses a staged temporary file and atomic rename; production restore still requires maintenance-mode checks and a verified backup checksum.

## Included
- System feature migration.
- BackupService.
- DatabaseOptimizer and ZoneOptimizer.
- ApiTokenService.
- Activity and notification repositories.
- System and API documentation views.
