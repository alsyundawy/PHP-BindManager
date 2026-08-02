# Fase 4 — DNS Management Features

This phase adds the domain model and service foundation for zones, records, DNSSEC, ACLs, views, forward/reverse zones, validation, and import/export.

## Design notes
- BIND zone types and ACL/view grammar follow the BIND 9 configuration reference. [web:211]
- `named-checkzone` is the validation boundary before a zone is activated; it checks zone syntax and integrity and returns non-zero on errors. [web:216]
- DNSSEC requires DNSKEY/RRSIG/NSEC or NSEC3 material and a chain-of-trust DS deployment; signing must be performed through controlled binaries and validated paths. [web:219]
- Zone file writes and command execution must remain behind a future SafeExec policy layer; this phase only provides the service boundary.

## Included
- SQLite schema for zones, records, DNSSEC keys, ACLs, and views.
- Typed zone and record validators.
- PDO repositories for zone and record operations.
- Zone export and `named-checkzone` validation service boundary.
- DNSSEC binary availability service boundary.
- Initial zones and records views.
