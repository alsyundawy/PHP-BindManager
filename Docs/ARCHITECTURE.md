# Architecture Documentation

## Overview

PHP-BindManager follows a layered **MVC + Repository + Service** architecture, built on top of a lightweight custom PHP framework (no full-stack framework dependency).

---

## Request Lifecycle

```
HTTP Request
    │
    ▼
[Nginx]
    │  FastCGI
    ▼
[PHP-FPM]
    │
    ▼
[public/index.php]  ← Web root entry point
    │
    ▼
[App\Bootstrap]     ← Load config, env, services
    │
    ▼
[App\Router]        ← Match URI to route definition
    │
    ▼
[Middleware Stack]   ← Auth, CSRF, Rate-limit, Session, Headers
    │
    ▼
[Controller]        ← Handle request, call services
    │
    ▼
[Service Layer]     ← Business logic, orchestration
    │
    ▼
[Repository Layer]  ← Data access, PDO queries
    │
    ▼
[SQLite3 (WAL)]     ← Persistent storage
    │
    ▼
[Controller]        ← Prepare view data
    │
    ▼
[View / Template]   ← Render HTML response
    │
    ▼
HTTP Response
```

---

## Layer Responsibilities

### Controller
- Receives HTTP request (PSR-7 ServerRequestInterface)
- Validates input (delegates to Validator)
- Calls Service layer
- Returns Response (PSR-7 ResponseInterface)
- No business logic — thin controllers

### Service Layer
- Contains all business logic
- Orchestrates multiple repositories
- Handles BIND9 integration
- Emits Events
- Called by Controllers and CLI

### Repository Layer
- All database access via PDO prepared statements
- Returns typed DTOs or domain objects
- No business logic — pure data access
- Implements Repository Interface

### Model
- Represents domain entities (Zone, Record, User, etc.)
- Contains no database logic
- May contain simple domain validation

### DTO (Data Transfer Object)
- Immutable data containers
- Used between Controller → Service → Repository
- Strictly typed (PHP 8.4 readonly properties)

### Middleware
- AuthMiddleware — validates session/token
- CsrfMiddleware — validates CSRF token
- RateLimitMiddleware — per-IP, per-user limiting
- SecurityHeadersMiddleware — injects HTTP security headers
- AuditMiddleware — logs all state-changing requests

---

## Database Schema (ERD Overview)

```
users
  id, username, email, password_hash, role_id, is_active,
  last_login_at, failed_attempts, locked_until, created_at, updated_at

roles
  id, name, description, permissions (JSON), created_at

zones
  id, name, type (MASTER/SLAVE/FORWARD/HINT), view_id, status,
  serial, refresh, retry, expire, minimum, ttl,
  created_by, updated_by, created_at, updated_at

dns_records
  id, zone_id, name, type, ttl, priority, content,
  is_disabled, created_by, updated_by, created_at, updated_at

acls
  id, name, entries (JSON), created_by, created_at, updated_at

views
  id, name, match_clients (JSON), acl_id, created_at, updated_at

api_tokens
  id, user_id, name, token_hash, scopes (JSON),
  last_used_at, expires_at, is_revoked, created_at

audit_logs
  id, user_id, action, entity_type, entity_id,
  old_value (JSON), new_value (JSON), ip_address, user_agent, created_at

activity_logs
  id, user_id, type, message, context (JSON), ip_address, created_at

notifications
  id, user_id, type, title, message, is_read, created_at

settings
  id, key, value, group, description, updated_by, updated_at

backups
  id, filename, size, zone_id (nullable), type,
  created_by, created_at

rate_limits
  id, identifier, action, attempts, reset_at
```

---

## Security Architecture

```
[Client]
   │  HTTPS (TLS 1.2/1.3)
   ▼
[Nginx]  ← Security headers, rate limit (nginx level)
   │
   ▼
[PHP App]
   ├── SecurityHeadersMiddleware  ← CSP, HSTS, X-Frame, etc.
   ├── SessionMiddleware           ← Secure session init
   ├── AuthMiddleware             ← Validate session/token
   ├── CsrfMiddleware             ← Token validation
   ├── RateLimitMiddleware        ← Brute force protection
   └── AuditMiddleware            ← Log all mutations
```

---

## BIND9 Integration

```
[ZoneService]
    │
    ├── Writes zone file to /etc/bind/zones/ (validated path)
    ├── Updates named.conf.local via NamedConfService
    ├── Runs: rndc reload <zone>  (via safe exec wrapper)
    └── Validates zone file via: named-checkzone <zone> <file>
```

All BIND9 commands run through a **SafeExec** wrapper that:
- Whitelist-validates all arguments
- Prevents shell injection
- Logs every execution with output
- Limits allowed commands to a strict set

---

## DNSSEC Flow

```
[User requests DNSSEC sign]
    │
    ▼
[DnssecService]
    ├── Generates KSK/ZSK via dnssec-keygen
    ├── Signs zone via dnssec-signzone
    ├── Updates zone serial
    ├── Stores DS records
    └── Triggers rndc reload
```

---

## Theme System

```
[Page Load]
    │
    ├── Check localStorage('theme')
    ├── Fallback: Check cookie('theme')
    ├── Fallback: prefers-color-scheme media query
    └── Apply: data-theme="light|dark" on <html>

[Toggle]
    ├── Update localStorage
    ├── Update cookie
    └── Toggle data-theme attribute
```
