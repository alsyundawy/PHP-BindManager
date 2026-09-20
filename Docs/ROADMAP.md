# Roadmap

## v1.0.0 & v1.0.1 — Core Engine & Advanced Features (Completed)

- [x] Project structure and documentation
- [x] Core framework bootstrap (Router, DI Container, Middleware)
- [x] Database migrations and schema
- [x] Authentication (login, logout, session, Argon2id)
- [x] RBAC (Admin, Editor, Viewer)
- [x] Dashboard with basic stats
- [x] Zone Management (CRUD, forward, reverse)
- [x] DNS Record Management (A, AAAA, CNAME, MX, NS, TXT, PTR, SRV, CAA, NAPTR, TLSA, SOA)
- [x] ACL Management
- [x] Views Management (Split-Horizon)
- [x] Zone Import / Export
- [x] Zone Validation (named-checkzone)
- [x] Backup and Restore (SQLite WAL snapshots)
- [x] Activity Log
- [x] Audit Trail with JSON diffs
- [x] System Health
- [x] Settings
- [x] User Management
- [x] Profile
- [x] REST API v1
- [x] API Token Management (Granular scopes & SHA-256)
- [x] Light / Dark / Auto Theme (Visual Subnet Calculator style)
- [x] Notification System
- [x] Full documentation
- [x] PHPUnit tests (>= 80% coverage)
- [x] DNSSEC key generation (KSK/ZSK/CSK)
- [x] Zone signing state machine
- [x] DS record management & key tags
- [x] Key rollover & retirement workflows
- [x] DNSSEC status dashboard

## v1.2.0 — Advanced Features (Completed)

- [x] Two-factor authentication (TOTP)
- [x] LDAP / SSO integration
- [x] Zone templates library
- [x] Bulk DNS record operations
- [x] DNS record diff viewer
- [x] Zone history and rollback
- [x] Webhook notifications

## v2.0.0 — Enterprise

- [ ] Multi-server BIND9 management
- [ ] DNS monitoring and alerting
- [ ] SLA reporting
- [ ] Custom roles and permissions
- [ ] White-label support
