# Changelog

All notable changes to PHP-BindManager are documented in this file.

Format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/)  
Versioning follows [Semantic Versioning](https://semver.org/spec/v2.0.0.html)

---

## [1.0.1] - 2026-09-21

### Added

- **Backup & Restore System**: Web UI and backend service (`/system/backups`) for creating, restoring,
  and deleting SQLite database snapshots with SHA-256 integrity verification.
- **Activity Log Viewer**: Comprehensive audit log interface (`/system/activity`) with category filtering
  (auth, zone, record, user, system, api, backup) and user attribution.
- **Audit Trail Inspector**: Immutable change tracker (`/system/audit-logs`) with expandable before/after
  diff view for compliance.
- **API Token Management**: Token generator (`/system/tokens`) supporting custom expiration dates,
  granular permission scopes, SHA-256 hashing, one-time reveal, and instant revocation.
- **Access Control Lists (ACL)**: Named BIND9 ACL management (`/acls`) for restricting query access.
- **Split-Horizon DNS Views**: Multi-view configuration (`/views`) with `match-clients` rules for internal
  and external split-horizon resolution.
- **DNSSEC Key Management**: Cryptographic key pair generation (`/dnssec`) with KSK, ZSK, and CSK roles,
  algorithm selection (ECDSA, Ed25519, RSA), and key retirement workflows.
- New repositories: `BackupRepository`, `AclRepository`, `DnsViewRepository`, `DnssecKeyRepository`.

### Changed

- Modularized `App\Application::boot()` by extracting `registerCoreServices()`, `registerAuthServices()`,
  `registerDnsServices()`, and `registerSystemServices()` to ensure clean dependency injection.
- Optimized `Public/assets/css/app.min.css`: removed invalid vendor-prefixed properties that triggered CSS linter
  warnings, normalized text-size-adjust, added custom `.pbm-diff-code` word-wrapping, and implemented thin 6px
  styled scrollbars for high-density tables.
- Hardened Xiaomi, Redmi, & POCO responsive viewports (HyperOS & MIUI): integrated `viewport-fit=cover`,
  `100svh`/`100dvh` units, safe-area-inset bounds, and flexible zero-minwidth containers to prevent UI clipping
  and horizontal scrolling under floating navigation gestures.

### Fixed

- **100% Linter & Static Analysis Eradication**: Fixed all Intelephense, PHP-CS-Fixer, PHPCS, Psalm, and PHPStan
  Level 8 issues with zero errors.
- Eliminated all undefined template variable warnings across views (`acls/index.php`, `dnssec/index.php`,
  `views/index.php`, `system/backups.php`, `system/tokens.php`, `system/activity.php`, `system/audit-logs.php`)
  using safe `if (! isset(...))` guards.
- Streamlined form CSRF token embedding via precomputed `$csrfVal` variables, eliminating multiline formatting
  and PHPCS indentation errors.
- Fixed closure return type mismatch and unused parameters in `Tests/Unit/Http/AuthMiddlewareTest.php`.
- Corrected column name mapping in `ApiTokenRepository` (`scopes_json` -> `scopes`).
- Fixed `PDOStatement|false` checks in `ApiTokenRepository::all()` and `ActivityLogRepository::count()`.
- Fixed accessible grouping on API token scope checkboxes (`role="group"` and `aria-labelledby`).

---

## [1.0.0] - 2026-09-20

### Added

- Initial release of PHP-BindManager: Enterprise Web GUI for BIND9 Authoritative DNS.
- Authoritative DNS Zone and Record management (SOA, NS, A, AAAA, CNAME, MX, TXT, SRV, PTR, CAA, SSHFP, TLSA).
- Zero-CDN local vendor assets (Bootstrap 3.5.8, jQuery 3.7.1, Font Awesome 6.7.2) in `Public/assets/vendor/`.
- Modern responsive dashboard inspired by Visual Subnet Calculator with dual `data-theme` / `data-bs-theme` synchronization.
- Production BIND 9 Authoritative DNS deployment tutorial (`TUTORIAL.md`).
- SQLite 3 database engine with WAL mode, transactions, indexed lookups, and migration seeder.
- Security-hardened authentication with rate limiting, brute-force mitigation, CSRF token validation,
  and secure session management.
- REST API layer with scoped Bearer token authentication and OpenAPI-ready documentation.
- Comprehensive PHPUnit test suite, PHPStan Level 8, Psalm Level 4, PHPCS, PHP-CS-Fixer, and Trunk check compliance.
