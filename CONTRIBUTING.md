# Contributing to PHP-BindManager

Thank you for your interest in contributing to PHP-BindManager!

---

## Code of Conduct

All contributors are expected to behave professionally and respectfully.  
Harassment of any kind will not be tolerated.

---

## How to Contribute

### Reporting Bugs

1. Search existing issues before creating a new one
2. Use the Bug Report template
3. Include: PHP version, OS, BIND9 version, steps to reproduce, expected vs actual behavior
4. For security vulnerabilities — see [SECURITY.md](SECURITY.md), do NOT open a public issue

### Suggesting Features

1. Search existing issues and discussions
2. Open a GitHub Discussion first for large features
3. Use the Feature Request template for smaller additions

### Pull Requests

1. Fork the repository
2. Create a branch: `git checkout -b feat/your-feature` or `fix/your-fix`
3. Write code following the standards below
4. Write or update tests (minimum 80% coverage)
5. Run all QA checks before submitting:
   ```bash
   composer run phpcs
   composer run phpstan
   composer run psalm
   composer run test
   ```
6. Commit using Conventional Commits:
   ```
   feat: add CAA record support
   fix: resolve zone import encoding issue
   docs: update API.md with new endpoints
   refactor: simplify ZoneService::validate()
   test: add unit tests for DnsRecordValidator
   security: patch CSRF token regeneration
   ```
7. Open a Pull Request against the `main` branch
8. Fill in the PR template completely

---

## Coding Standards

- PHP 8.4+ with `declare(strict_types=1)` in every file
- PSR-1, PSR-4, PSR-12 compliance
- SOLID, DRY, KISS, YAGNI principles
- Full PHPDoc on all classes, methods, properties
- Type declarations on all parameters, return types, properties
- No deprecated functions, no `var_dump`, no `print_r` in production code
- All queries via PDO prepared statements — no raw string interpolation
- All output escaped — no raw `echo $userInput`

---

## Development Setup

```bash
git clone https://github.com/alsyundawy/PHP-BindManager.git
cd PHP-BindManager
cp .env.example .env
composer install
php bin/migrate.php
php bin/seed.php
```

---

## Branch Naming

| Type | Pattern |
|---|---|
| Feature | `feat/description` |
| Bug Fix | `fix/description` |
| Documentation | `docs/description` |
| Refactor | `refactor/description` |
| Security | `security/description` |
| Release | `release/v1.0.0` |

---

## Commit Convention

Use [Conventional Commits](https://www.conventionalcommits.org/):

```
type(scope): short description

Longer description if needed.

Refs #issue-number
```

Types: `feat`, `fix`, `docs`, `style`, `refactor`, `test`, `chore`, `security`, `perf`
