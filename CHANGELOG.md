# Changelog

All notable changes to PHP-BindManager are documented in this file.

Format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/)  
Versioning follows [Semantic Versioning](https://semver.org/spec/v2.0.0.html)

---

## [Unreleased]

### Added
- Initial project scaffold and directory structure
- Full documentation suite (README, INSTALL, ARCHITECTURE, SECURITY, CONFIGURATION, API, ROADMAP, CONTRIBUTING)
- `composer.json` with PSR-4 autoloading, PHPStan, Psalm, PHP-CS-Fixer, PHPCS
- EditorConfig, `.gitignore`, `.env.example`
- PHPUnit configuration and bootstrap
- MIT License
- Core application bootstrap with PSR-7 request handling
- Lightweight dependency injection container
- Router, route matching, kernel, and HTTP middleware stack
- Secure session, CSRF service, auth service, and rate limiter service
- SQLite initial schema migration and database seeder
- Login and welcome views
- Config files for app, database, session, security, BIND9, logging, API, and RBAC

### Changed
- N/A

### Deprecated
- N/A

### Removed
- N/A

### Fixed
- N/A

### Security
- Added HTTP security headers, secure session defaults, CSRF validation, and brute-force mitigation

---

## [1.0.0] — Planned

### Added
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
