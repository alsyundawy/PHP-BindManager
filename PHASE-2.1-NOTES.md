# PHP-BindManager Phase 2.1 Patch Notes

This patch prepares the missing stabilization work before pushing the next GitHub commit.

## Included fixes
- Add `POST /login`, `POST /logout`, and authenticated `GET /dashboard` routes.
- Generate CSRF token through `CsrfService::token()` before rendering the login form.
- Fix repeated named PDO placeholders in SQLite UPSERT statements by using unique markers.
- Preserve `SameSite`, `HttpOnly`, and `Secure` flags during session start and cookie deletion.
- Add migration tracking table and transactional migration runner.
- Add basic unit and integration tests for rate limiting, CSRF, permission wildcard matching, and route presence.
- Add a compact dashboard view aligned with admin-panel UI guidance.

## Suggested commit message
`fix: complete phase 2.1 auth flow, migration safety, and baseline tests`
