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
[App\Application]   ← Load env, config, services
    │
    ▼
[App\Http\Kernel]   ← Build middleware pipeline
    │
    ▼
[App\Http\Router]   ← Match URI to route definition
    │
    ▼
[Middleware Stack]   ← Session, Rate-limit, CSRF, Auth
    │
    ▼
[Controller/Closure Handler]
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
[View / Template]   ← Render HTML response
    │
    ▼
HTTP Response
```

---

## Layer Responsibilities

### Application
- Boots the environment and configuration
- Registers dependencies in the container
- Creates PSR-7 requests from globals
- Delegates all handling to Kernel

### Kernel
- Resolves route match
- Builds middleware execution order
- Converts exceptions into HTML error responses

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

### Middleware
- SessionMiddleware — secure PHP session initialization
- RateLimitMiddleware — per-IP throttling
- CsrfMiddleware — validates CSRF token on mutating requests
- AuthMiddleware — protects authenticated routes

---

## Database Schema (Phase 2)

```
roles
  id, name, description, permissions, created_at

users
  id, role_id, username, email, password_hash,
  last_login_at, last_login_ip, failed_attempts,
  locked_until, is_active, created_at, updated_at

user_sessions
  session_id, user_id, ip_address, user_agent,
  last_activity_at, created_at

rate_limits
  identifier, action, attempts, reset_at

audit_logs
  id, user_id, action, entity_type, entity_id,
  old_value, new_value, ip_address, user_agent, created_at
```

---

## Authentication Flow

```
[GET /login]
   │
   ├── Start secure session
   ├── Generate / reuse CSRF token
   └── Render login form

[POST /login]
   │
   ├── SessionMiddleware
   ├── RateLimitMiddleware
   ├── CsrfMiddleware
   ├── Verify username/password
   ├── Check account lock state
   ├── session_regenerate_id(true)
   ├── Persist session metadata
   └── Redirect to dashboard
```

---

## Security Architecture

```
[Client]
   │  HTTPS (TLS 1.2/1.3)
   ▼
[Nginx]  ← TLS, security headers, static asset cache
   │
   ▼
[Public/index.php]
   ├── X-Frame-Options: DENY
   ├── X-Content-Type-Options: nosniff
   ├── Referrer-Policy
   ├── Permissions-Policy
   ├── Content-Security-Policy
   └── HSTS when secure

[Middleware Stack]
   ├── SessionMiddleware
   ├── RateLimitMiddleware
   ├── CsrfMiddleware
   └── AuthMiddleware
```
