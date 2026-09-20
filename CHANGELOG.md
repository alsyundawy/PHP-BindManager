# Changelog

All notable changes to PHP-BindManager are documented in this file.

Format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/)  
Versioning follows [Semantic Versioning](https://semver.org/spec/v2.0.0.html)

---

## [1.1.0] - 2026-09-21

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
- Optimized `Public/assets/css/app.min.css` with cross-browser `text-size-adjust` fallbacks
  (`-webkit-text-size-adjust`, `-moz-text-size-adjust`, `text-size-adjust`) and thin scrollbars.
- Hardened Xiaomi/Redmi/POCO responsive viewports with dynamic `100svh`/`100dvh` units and
  safe-area-inset bounds to prevent clipping under MIUI/HyperOS navigation bars.

### Fixed

- Fixed column name mismatch in `ApiTokenRepository` (`scopes_json` -> `scopes`).
- Fixed `PDOStatement|false` checks in `ApiTokenRepository::all()` and `ActivityLogRepository::count()`.
- Fixed short ternary operators, redundant casts, and non-boolean `if` conditions in `Routes/system.php`
  to achieve 100% PHPStan Level 8 strict compliance.
- Re-formatted all system views (`backups.php`, `activity.php`, `audit-logs.php`, `tokens.php`,
  `acls/index.php`, `views/index.php`, `dnssec/index.php`) to strictly conform to PSR-12, line length <= 120 chars,
  and `require_once` semantics.
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
