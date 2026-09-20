# QA, Testing, & Final Architecture Audit

This document defines the automated quality gates, security invariants, and test verification standards for PHP-BindManager.

## Quality Gates & Static Analysis

1. **PHPUnit**: Functional and unit test suite verifying router paths, rate limiters, permissions, and zone optimizers.
2. **PHPStan**: Static type analysis running at Level 8 with `phpstan-strict-rules`.
3. **Psalm**: Static analysis enforcing type safety and catching missing return types.
4. **PHP_CodeSniffer**: Code style enforcement following the PSR-12 standard.
5. **PHP-CS-Fixer**: Automated formatting and code hygiene rules.
6. **Trunk**: Multi-linter validation running Prettier, Markdownlint, Yamllint, and Checkov.

## Audit Scope & Verification

- **Syntax & Parse Integrity**: Strict typing, typed properties, zero deprecated function calls.
- **Security Boundaries**: Prepared statements on all SQL queries, CSRF token validation on mutating actions, rate limiting, and secure HTTP-only cookies.
- **DNS Server Role**: Authoritative Primary/Secondary BIND9 server focus with simple isolated recursive fallback and zero RPZ bloat.
- **Asset Integrity**: Complete offline assets (Bootstrap, jQuery, Font Awesome) without third-party CDN reliance.
- **Responsive Viewport**: Fully responsive across mobile viewports (Xiaomi, Redmi, Poco, iPhone, Samsung) respecting safe-area insets and dynamic viewport heights.
