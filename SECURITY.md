# Security Policy

## Supported Versions

| Version | Supported |
|---------|---------- |
| 1.x     | ✅ Yes    |

## Reporting a Vulnerability

Do **NOT** open a public GitHub issue for security vulnerabilities.

Please report security issues privately via:
- Email: security@alsyundawy.com
- GitHub Security Advisories: [Report a vulnerability](https://github.com/alsyundawy/PHP-BindManager/security/advisories/new)

You will receive a response within **72 hours**.

## Security Measures Implemented

### Authentication & Session
- Argon2id password hashing (PHP `PASSWORD_ARGON2ID`)
- Secure session configuration (`session.use_strict_mode`, `session.cookie_httponly`, `session.cookie_secure`, `session.cookie_samesite=Strict`)
- Session regeneration on privilege escalation and login
- Brute-force protection with exponential backoff
- Rate limiting per IP and per user

### Input & Output
- All user input validated via strict validators
- All output escaped via `htmlspecialchars()` with `ENT_QUOTES | ENT_SUBSTITUTE`
- Prepared statements (PDO) for all database queries — no raw string interpolation
- Command injection protection — no `exec()` / `shell_exec()` with unvalidated input
- Directory traversal protection — strict path validation

### HTTP Security Headers
```
Content-Security-Policy: default-src 'self'; script-src 'self' 'nonce-{RANDOM}'; ...
X-Frame-Options: DENY
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: geolocation=(), camera=(), microphone=()
Strict-Transport-Security: max-age=31536000; includeSubDomains; preload
```

### CSRF
- Synchronizer Token Pattern — all state-changing forms include `_csrf_token`
- Double Submit Cookie Pattern for AJAX/API requests
- Tokens are cryptographically random (32 bytes, `random_bytes()`)
- Tokens are single-use and time-limited (1 hour)

### API Security
- Bearer token authentication (SHA-256 hashed in DB)
- Token expiry and revocation
- Rate limiting per token
- Scope-based permissions

### Database
- SQLite3 WAL mode
- Foreign key constraints enforced
- All queries use PDO prepared statements
- No dynamic SQL string construction from user input

### Cookie Security
- `HttpOnly` — always
- `Secure` — always (HTTPS)
- `SameSite=Strict` — always
- `Path=/` — always
- Short expiry for session cookies

### File Operations
- MIME type validation
- File extension whitelist
- No user-controlled filenames stored directly
- Upload directory outside web root

### Infrastructure
- Nginx with security headers
- PHP `open_basedir` restriction
- PHP `disable_functions` for dangerous functions
- Least privilege file permissions

## Security Disclosure Process

1. Report received → Acknowledged within 72h
2. Triage and reproduce
3. Patch developed in private branch
4. CVE requested if applicable
5. Release and public disclosure (coordinated)
6. Reporter credited in CHANGELOG and SECURITY.md
