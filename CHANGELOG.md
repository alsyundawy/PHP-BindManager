# Changelog

All notable changes to PHP-BindManager are documented in this file.

Format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/)  
Versioning follows [Semantic Versioning](https://semver.org/spec/v2.0.0.html)

---

## [1.0.0] - 2026-09-20

### Added

- Initial release of PHP-BindManager: Enterprise Web GUI for BIND9 Authoritative DNS.
- Authoritative DNS Zone and Record management (SOA, NS, A, AAAA, CNAME, MX, TXT, SRV, PTR, CAA, SSHFP, TLSA).
- Initial project scaffold and directory structure.
- Full documentation suite (README, INSTALL, ARCHITECTURE, SECURITY, CONFIGURATION, API, ROADMAP, CONTRIBUTING,
  TUTORIAL, DOCNOTE).
- Zero-CDN local vendor assets (Bootstrap 3.5.8, jQuery 3.7.1, Font Awesome 6.7.2) in `Public/assets/vendor/`.
- Modern responsive dashboard inspired by Visual Subnet Calculator with dual `data-theme` / `data-bs-theme` synchronization.
- Production BIND 9 Authoritative DNS deployment tutorial (`TUTORIAL.md`).
- SQLite 3 database engine with WAL mode, transactions, indexed lookups, and migration seeder.
- Security-hardened authentication with rate limiting, brute-force mitigation, CSRF token validation,
  and secure session management.
- REST API layer with scoped Bearer token authentication and OpenAPI-ready documentation.
- Comprehensive PHPUnit test suite, PHPStan Level 8, Psalm Level 4, PHPCS, PHP-CS-Fixer, and Trunk check compliance.

### Changed

- Refactored routes (`web.php`, `dns.php`, `system.php`) and configs to strict multiline PSR-12 arrays.
- Enhanced mobile responsiveness with `viewport-fit=cover`, safe-area insets, `100dvh`, and responsive table wrappers.
- Hardened database repositories and services with typed docblocks and PDO query safety checks.

### Fixed

- Resolved Intelephense schema type mismatch in `.vscode/settings.json` (`undefinedVariables` set to string `"off"`).
- Suppressed non-standard CSS `text-size-adjust` linter warning in `Public/assets/css/app.min.css`
  in favor of `-webkit-text-size-adjust: 100%`.
- Cleared trailing blank line at EOF in `Public/assets/css/app.min.css` for Trunk check compliance.
- Hardened session cookies in `App/Services/Auth/AuthenticationService.php` with explicit `secure: true`
  and `httponly: true` flags (SonarLint S3330).
- Re-formatted all template views (`errors/generic.php`, `layouts/app.php`, `welcome.php`, `system/api-docs.php`,
  `partials/navbar.php`, `partials/sidebar.php`) to strictly adhere to max 120-character line limit.
- Replaced `include` with `include_once` for view partials and added trailing newline to `Resources/Views/layouts/app.php`.
- Formatted long SQL schema statements in unit test fixtures (`RateLimiterServiceTest.php`,
  `ApiTokenServiceTest.php`) to adhere to line length constraints.
- Fixed missing `/dashboard` and `/logout` paths in `Routes/web.php`.
- Fixed `ZoneOptimizerTest` normalization assertion mismatch.
- Added untyped readonly properties and missing getters in `App/Application.php`.
- Configured Psalm 8.4 runtime mapping and PHP-CS-Fixer 8.5 runtime allowance.

### Security

- Enforced strict HTTP security headers (CSP, HSTS, X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Permissions-Policy).
- Localized all frontend assets to eliminate external CDN tracking, downtime, and supply chain risks.
