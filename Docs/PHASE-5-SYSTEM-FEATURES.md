# Fase 5 — System Features

## Included foundation
- Consistent SQLite backup through `VACUUM INTO`, avoiding unsafe raw copies of a live WAL database. SQLite documents `VACUUM INTO` as a consistent backup alternative and notes that the backup API is incremental. [web:224]
- Restore integrity validation using `PRAGMA integrity_check` and an atomic replacement flow.
- Database optimization via `PRAGMA optimize`, `ANALYZE`, `integrity_check`, and optional `VACUUM`.
- Zone optimization boundary through export + `named-checkzone` validation.
- Audit logs, activity logs, and notifications schema/repositories.
- API tokens generated with a random plaintext token, SHA-256 hash at rest, expiry, revocation fields, and scopes. Raw tokens must not be logged; security guidance recommends omitting or irreversibly hashing sensitive tokens in logs. [web:232]
- Initial API health endpoint and API documentation page.

## Operational warning
`VACUUM` can require substantial disk space and exclusive database work; schedule it during a maintenance window. WAL remains the default operational mode and should not be replaced by naive filesystem copying. [web:230]

## Remaining integration
Register the new repositories/services in the DI container, merge `Routes/system.php`, add migration execution, and add controller-level authorization before production deployment.
