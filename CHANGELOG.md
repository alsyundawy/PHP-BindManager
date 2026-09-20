# Changelog

All notable changes to PHP-BindManager are documented in this file.

Format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/)  
Versioning follows [Semantic Versioning](https://semver.org/spec/v2.0.0.html)

---

## [Unreleased]

### Added

- Initial project scaffold and directory structure
- Full documentation suite (README, INSTALL, ARCHITECTURE, SECURITY, CONFIGURATION, API, ROADMAP, CONTRIBUTING)
- Zero-CDN local vendor assets (Bootstrap 5.3.3, jQuery 3.7.1, Font Awesome 6.5.2) in `Public/assets/vendor/`
- Production BIND 9 Authoritative DNS deployment tutorial (`TUTORIAL.md`)
- Architectural and engineering documentation notes (`DOCNOTE.md`)
- `composer.json` with PSR-4 autoloading, PHPStan, Psalm, PHP-CS-Fixer, PHPCS
- EditorConfig, `.gitignore`, `.env.example`
- PHPUnit configuration and bootstrap
- MIT License
- Core application bootstrap with PSR-7 request handling
- Lightweight dependency injection container
- Router, route matching, kernel, and HTTP middleware stack
- Secure session, CSRF service, auth service, and rate limiter service
- SQLite initial schema migration and database seeder
- Login, dashboard, zones, records, and system views
- Config files for app, database, session, security, BIND9, logging, API, and RBAC

### Changed

- Refactored routes (`web.php`, `dns.php`, `system.php`) and configs to strict multiline PSR-12 arrays
- Modernized UI theme to match Visual Subnet Calculator aesthetic with dual `data-theme` and `data-bs-theme` synchronization
- Enhanced mobile responsiveness with `viewport-fit=cover`, safe-area insets, `100dvh`, and responsive table wrappers
- Hardened database repositories and services with typed docblocks and PDO query safety checks

### Deprecated

- N/A

### Removed

- Stale worktree and temporary notes

### Fixed

- Missing `/dashboard` and `/logout` paths in `Routes/web.php`
- `ZoneOptimizerTest` normalization assertion mismatch
- Untyped readonly properties and missing getters in `App/Application.php`
- Psalm 8.4 runtime mapping and PHP-CS-Fixer 8.5 runtime allowance

### Security

- Added HTTP security headers, secure session defaults, CSRF validation, and brute-force mitigation
- Localized all assets to eliminate external CDN tracking and supply chain risks

---

## [1.0.0] — Planned

### Planned Additions

- Full application MVP with all core features
- Dashboard with real-time statistics
- Zone management (forward, reverse, DNSSEC)
- Complete DNS record type support (A, AAAA, CNAME, MX, NS, TXT, PTR, SRV, CAA, NAPTR, TLSA, SOA)
- RBAC with Admin, Editor, Viewer roles
- REST API with Bearer token authentication
- Light / Dark / Auto theme
- Full audit trail and activity log
- Backup and restore
- Import / Export zone files
- ACL and Views management
- System health monitoring
- Multi-user management
- Profile and settings pages
- Notification system
