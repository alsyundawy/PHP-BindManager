# PHP-BindManager — Enterprise Authoritative DNS Control Plane

[![Release](https://img.shields.io/badge/Release-v1.0.1-007ACC?style=for-the-badge&logo=github)](https://github.com/alsyundawy/PHP-BindManager/releases)
[![PHP Version](https://img.shields.io/badge/PHP-8.4%20%7C%208.5-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![BIND 9](https://img.shields.io/badge/BIND%209-Authoritative%20Only-D9381E?style=for-the-badge&logo=internetcomputer&logoColor=white)](https://www.isc.org/bind/)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg?style=for-the-badge)](LICENSE)
[![Quality Gates](https://img.shields.io/badge/Quality%20Gates-PHPStan%20L8%20%7C%20Psalm%20L4%20%7C%20Trunk-brightgreen?style=for-the-badge&logo=checkmarx)](https://trunk.io)
[![Zero CDN](https://img.shields.io/badge/Assets-100%25%20Offline%20Local-blue?style=for-the-badge&logo=bootstrap)](https://getbootstrap.com/)
[![Database](https://img.shields.io/badge/Database-SQLite%203%20WAL-003B57?style=for-the-badge&logo=sqlite&logoColor=white)](https://www.sqlite.org/)
[![Donate PayPal](https://img.shields.io/badge/Donate-PayPal-00457C?style=for-the-badge&logo=paypal&logoColor=white)](https://www.paypal.me/alsyundawy)

> **Enterprise-grade, security-hardened Web GUI and automation engine for BIND9 Authoritative DNS servers.
> Engineered with modern PHP 8.4+, strict PSR standards, zero external CDN dependencies, dual dark/light theming
> inspired by Visual Subnet Calculator, SQLite3 WAL persistence, and full REST API automation.**
>
> Designed, engineered, and maintained by
> **[`HARRY DERTIN SUTISNA ALSYUNDAWY (@alsyundawy)`](https://github.com/alsyundawy)** —
> Built for mission-critical DNS operations.
>
> 📦 **[`GitHub Releases`](https://github.com/alsyundawy/PHP-BindManager/releases)** &nbsp;|&nbsp;
> 📖 **[`Installation Guide`](INSTALL.md)** &nbsp;|&nbsp;
> 🛠️ **[`Production Deployment Tutorial`](TUTORIAL.md)** &nbsp;|&nbsp;
> 🏛️ **[`Architecture & Notes`](DOCNOTE.md)** &nbsp;|&nbsp;
> 📜 **[`Full Changelog`](CHANGELOG.md)** &nbsp;|&nbsp;
> 💖 **[`Support via PayPal`](https://www.paypal.me/alsyundawy)** &nbsp;|&nbsp;
> 🇮🇩 **[`QRIS Donation`](#-support--donation)**

---

## 🧭 Navigation

- [Overview](#-overview)
- [Why This Modernized Edition?](#-why-this-modernized-edition)
- [Key Features](#-key-features)
- [Architecture & Request Pipeline](#️-architecture--request-pipeline)
- [DNS Record Types & Authoritative Engine](#-dns-record-types--authoritative-engine)
- [Visual Subnet Calculator Design & Mobile Responsive System](#-visual-subnet-calculator-design--mobile-responsive-system)
- [Cross-OS Production Deployment & Migration](#-cross-os-production-deployment--migration)
- [Installation & Setup Guide](#-installation--setup-guide)
- [Configuration Reference](#️-configuration-reference)
- [REST API & Automation Layer](#-rest-api--automation-layer)
- [Quality Assurance & Verification Gates](#-quality-assurance--verification-gates)
- [Engineering Standards & Invariants](#-engineering-standards--invariants)
- [Security & Content Safety](#-security--content-safety)
- [Project Directory Structure](#-project-directory-structure)
- [Contributing](#-contributing)
- [Maintainer & Contact](#-maintainer--contact)
- [Support & Donation](#-support--donation)
- [License](#-license)

---

## 🌟 Overview

**PHP-BindManager** is a high-performance, web-based authoritative DNS management suite tailored for system
administrators, network engineers, hosting providers, and DevOps teams.

Managing BIND 9 zone files manually through terminal text editors is error-prone, risks syntax errors, and creates
bottlenecks during incident response. **PHP-BindManager** bridges this gap by providing an intuitive, accessible
Web GUI and automation API while ensuring full compliance with RFC standards and zero downtime.

Whether deployed on Debian, Ubuntu, Rocky Linux, or CentOS, PHP-BindManager delivers sub-millisecond local
configuration rendering, atomic database operations via SQLite WAL mode, and complete decoupling from
internet-dependent CDNs.

---

## 🚀 Why This Modernized Edition?

This edition (**v1.0.1**) represents a clean-slate architectural, security, accessibility, and visual overhaul of
modern DNS administration:

### 🛡️ 1. Zero-CDN Offline Architecture & Content Security

- **100% Local Distribution**: Ships with production bundles of **Bootstrap 3.5.8**, **jQuery 3.7.1**, and
  **Font Awesome 6.7.2** located in `Public/assets/vendor/`.
- **Air-Gapped & Offline Ready**: Runs reliably in isolated server networks, air-gapped enclaves, and private
  intranets without third-party CDN latency, outages, or telemetry tracking.
- **Strict Content Security Policy (CSP)**: HTTP headers enforce `default-src 'self'` and
  `style-src 'self' 'unsafe-inline'` with zero external origins permitted.

### ⚡ 2. Strict Authoritative DNS Invariant (No Cache-Poisoning Vectors)

- **Dedicated Primary/Secondary Authority**: Explicitly configured for authoritative forward and reverse zones.
- **Elimination of Recursive Bloat**: Recursive resolution and Response Policy Zones (RPZ) are deliberately omitted
  from authoritative nodes. This eliminates DNS cache poisoning, recursive query amplification, and memory bloat.

### 🎨 3. Visual Subnet Calculator Theming & Mobile Notch Optimization

- **Ergonomic Palette**: Inspired by the dark/light design system of
  [Visual Subnet Calculator](https://alsyundawy.github.io/visualsubnetcalc).
- **Dual Synchronization**: Instant reactivity syncing both `data-theme` and `data-bs-theme` attributes across
  `dark`, `light`, and `auto` system preferences.
- **Notch & Cutout Safe**: Implements `viewport-fit=cover`, CSS `env(safe-area-inset-*)`, and modern `100dvh`
  viewport units to prevent cutoffs on smartphones (including Xiaomi, Redmi, Poco, iPhone, and Android tablets).

### 🔒 4. Enterprise Security & Defense-in-Depth

- **Brute-Force Rate Limiting**: IP-based rate limiting on authentication and API endpoints with automatic cooldowns.
- **Secure Session Management**: Strict `HttpOnly`, `SameSite=Strict`, and `Secure` cookie attributes verified by
  static security analyzers (SonarLint S3330 compliant).
- **Cryptographic CSRF Tokens**: Double-submitted CSRF validation on all state-changing mutating requests
  (`POST`, `PUT`, `DELETE`).
- **Input Sanitization & Output Escaping**: Automated contextual escaping helper `e()` protects all view templates
  against Cross-Site Scripting (XSS).

### 🗄️ 5. Resilient Local Database (SQLite WAL Mode)

- **Atomic Transactions**: Leverages SQLite 3 in **Write-Ahead Logging (WAL)** mode for concurrent readers and
  sequential zero-lock writers.
- **Single-File Portability**: Eliminates MySQL/PostgreSQL network roundtrips and service dependencies. Database
  backup requires simply copying `Storage/Database/bindmanager.sqlite`.

---

## 🎯 Key Features

| Capability Area           | Highlights & Implementations                                                                                                                           |
|:--------------------------|:-------------------------------------------------------------------------------------------------------------------------------------------------------|
| **Zone Management**       | Forward zones, Reverse IPv4 (`in-addr.arpa`), Reverse IPv6 (`ip6.arpa`), zone imports, export to standard RFC master files, SOA serial auto-increment. |
| **Record Types**          | Native validation and form schemas for `A`, `AAAA`, `CNAME`, `MX`, `NS`, `TXT`, `SRV`, `PTR`, `CAA`, `SSHFP`, `TLSA`, and `SOA`.                       |
| **Access Control (RBAC)** | Role-Based Access Control distinguishing `admin` (full access), `editor` (zone/record management), and `viewer` (read-only audit).                     |
| **REST API Engine**       | Versioned REST API (`/api/v1`) secured via scoped Bearer tokens for Terraform, Ansible, and CI/CD automated zone provisioning.                         |
| **System Health & BIND9** | Service status monitoring for `named` / `bind9`, memory consumption, load averages, zone validation using `named-checkzone`, and `named-checkconf`.    |
| **Audit Log & Trail**     | Tamper-evident activity logging recording user ID, IP address, exact action, target zone, and timestamp.                                               |
| **Responsive UI**         | Seamless layout transitions across monitors from 320px mobile displays up to 4K / 2K desktop workstations.                                             |

---

## 🏗️ Architecture & Request Pipeline

PHP-BindManager follows a clean, decoupled MVC and Service-Repository design pattern:

```mermaid
flowchart TB
    subgraph Client["Web Browser & Automation Clients"]
        User["Sysadmin / Web Browser"]
        APIClient["Ansible / Terraform / CI/CD"]
    end

    subgraph WebServer["Web Server (Nginx / Apache)"]
        Nginx["TLS Termination / Reverse Proxy<br/>(HSTS, CSP, Security Headers)"]
        Static["Local Static Assets<br/>(Bootstrap, jQuery, FontAwesome, App CSS)"]
    end

    subgraph AppKernel["PHP-BindManager Runtime (PHP 8.4+)"]
        FrontController["Public/index.php"]
        Router["HTTP Router & Middleware Stack<br/>(Auth, CSRF, Security Headers, Rate Limiter)"]
        Controllers["Application Controllers<br/>(Dashboard, Zone, Record, System, API)"]
        Services["Domain Service Layer<br/>(ZoneService, BINDCommand, AuthService, TokenService)"]
        Repositories["Repository Layer<br/>(ZoneRepo, RecordRepo, UserRepo, LogRepo)"]
    end

    subgraph StorageEngine["Persistence & DNS Daemon"]
        SQLite[("SQLite 3 Database<br/>(WAL Mode, Foreign Keys, Indexes)")]
        Rndc["BIND 9 Daemon (named)<br/>(rndc reload, named-checkzone)"]
        ZoneFiles[("RFC Zone Files<br/>/var/named or /etc/bind/zones")]
    end

    User -->|"HTTPS"| Nginx
    APIClient -->|"HTTPS Bearer API"| Nginx
    Nginx -->|"Static Files"| Static
    Nginx -->|"FastCGI (PHP-FPM)"| FrontController
    FrontController --> Router
    Router --> Controllers
    Controllers --> Services
    Services --> Repositories
    Repositories --> SQLite
    Services -->|"IPC / Sudo CLI"| Rndc
    Rndc --> ZoneFiles
```

---

## 📊 DNS Record Types & Authoritative Engine

PHP-BindManager validates and formats all standard DNS Resource Records:

| Record Type | Description                           | RFC Standard       | Syntax Validation                                              |
|:------------|:--------------------------------------|:-------------------|:---------------------------------------------------------------|
| **`A`**     | IPv4 Host Address                     | RFC 1035           | Dotted-decimal `0.0.0.0` – `255.255.255.255`                   |
| **`AAAA`**  | IPv6 Host Address                     | RFC 3596           | Standard compressed or uncompressed RFC 4291 IPv6              |
| **`CNAME`** | Canonical Name (Alias)                | RFC 1035           | Fully Qualified Domain Name (FQDN)                             |
| **`MX`**    | Mail Exchange Server                  | RFC 1035, RFC 7505 | Priority integer (`0–65535`) + mail exchanger FQDN             |
| **`NS`**    | Authoritative Name Server             | RFC 1035           | Authoritative nameserver FQDN                                  |
| **`TXT`**   | Text Annotations (SPF, DKIM, DMARC)   | RFC 1464, RFC 7208 | Character-string (supports multi-string chunks)                |
| **`PTR`**   | Pointer Record (Reverse DNS)          | RFC 1035           | Target host FQDN                                               |
| **`SRV`**   | Service Location Record               | RFC 2782           | Priority, weight, port (`1–65535`), target hostname            |
| **`CAA`**   | Certification Authority Authorization | RFC 6844, RFC 8659 | Flag byte, tag (`issue`, `issuewild`, `iodef`), value          |
| **`SSHFP`** | SSH Public Key Fingerprint            | RFC 4255, RFC 6594 | Algorithm, fingerprint type, hex string                        |
| **`TLSA`**  | DANE Transport Layer Security Auth    | RFC 6698, RFC 7671 | Certificate usage, selector, matching type, cert hex           |
| **`SOA`**   | Start of Authority                    | RFC 1035, RFC 2181 | Primary NS, contact email, serial, refresh, retry, expire, TTL |

---

## 🎨 Visual Subnet Calculator Design & Mobile Responsive System

The interface has been meticulously designed following the acclaimed aesthetic of
[Visual Subnet Calculator](https://alsyundawy.github.io/visualsubnetcalc):

- **Curated Dark/Light Palette**: Deep obsidian dark background (`#0b0f19` / `#111827`), subtle borders
  (`#1f2937` / `#334155`), and vibrant primary accents (`#3b82f6` with `#60a5fa` hover glow).
- **Glassmorphism Navigation Header**: Semi-transparent sticky navigation header with `backdrop-filter: blur(12px)`.
- **Notch, Cutout & Safe Area Insets**: Integrated with `viewport-fit=cover` and CSS safe-area padding
  (`padding-top: env(safe-area-inset-top, 0px); padding-bottom: env(safe-area-inset-bottom, 0px);`).
- **Dynamic Viewport Height**: Replaces rigid `100vh` with adaptive `100dvh` to prevent content from being clipped
  beneath mobile browser address bars.
- **Touch-Friendly Overflow Scrolling**: Horizontal table wrappers utilize `-webkit-overflow-scrolling: touch` with
  rounded boundary containers.

---

## 🌐 Cross-OS Production Deployment & Migration

PHP-BindManager is verified across enterprise Linux operating systems. When migrating between distributions,
the primary variation lies in service naming and file locations:

### Distribution Paths & Configuration Mapping

| Component / Setting      | Ubuntu 22.04 / 24.04 & Debian 11 / 12  | Rocky Linux 8 / 9 & CentOS 7 / Stream     |
|:-------------------------|:---------------------------------------|:------------------------------------------|
| **Package Name**         | `bind9`, `bind9-utils`, `bind9-doc`    | `bind`, `bind-utils`                      |
| **Systemd Service**      | `bind9.service` or `named.service`     | `named.service` or `named-chroot.service` |
| **Main Config File**     | `/etc/bind/named.conf`                 | `/etc/named.conf`                         |
| **Local Options File**   | `/etc/bind/named.conf.options`         | Included inside `/etc/named.conf`         |
| **Zone File Directory**  | `/etc/bind/zones/` or `/var/lib/bind/` | `/var/named/` or `/var/named/zones/`      |
| **Service User / Group** | `bind:bind`                            | `named:named`                             |
| **Firewall System**      | `ufw` (Uncomplicated Firewall)         | `firewalld` or `nftables` / `iptables`    |

### Hardened Authoritative BIND Configuration (`named.conf.options`)

```named
options {
    directory "/var/cache/bind";

    // Strictly Authoritative: disable recursion and caching
    recursion no;
    allow-query-cache { none; };
    allow-recursion { none; };

    // Listen on standard DNS ports
    listen-on port 53 { any; };
    listen-on-v6 port 53 { any; };

    // Query access control
    allow-query { any; };

    // Hide version and identity from reconnaissance probes
    version "Not Disclosed";
    hostname none;
    server-id none;

    // Rate Limiting (DNS Amplification Defense)
    rate-limit {
        responses-per-second 15;
        window 5;
    };
};
```

### Production Firewall Configuration

#### Ubuntu / Debian (UFW)

```bash
# Allow standard SSH and Web traffic
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Allow Authoritative DNS traffic
sudo ufw allow 53/tcp
sudo ufw allow 53/udp

# Enable firewall
sudo ufw enable
```

#### Rocky Linux / CentOS (Firewalld / Iptables)

```bash
# Using firewalld
sudo firewall-cmd --permanent --add-service=http
sudo firewall-cmd --permanent --add-service=https
sudo firewall-cmd --permanent --add-service=dns
sudo firewall-cmd --reload

# Or using raw iptables
sudo iptables -A INPUT -p udp --dport 53 -j ACCEPT
sudo iptables -A INPUT -p tcp --dport 53 -j ACCEPT
sudo iptables -A INPUT -p tcp --dport 443 -j ACCEPT
sudo iptables -A INPUT -p tcp --dport 80 -j ACCEPT
```

---

## 📦 Installation & Setup Guide

### 1. Prerequisites

Ensure your system meets the minimum requirements:

- **PHP**: 8.4 or 8.5 with `pdo_sqlite`, `sqlite3`, `mbstring`, `json`, `openssl`, `curl`, `intl`.
- **Web Server**: Nginx (recommended) or Apache with PHP-FPM.
- **DNS Server**: BIND 9.18+.
- **Composer**: 2.x+.

### 2. Clone & Install Dependencies

```bash
# 1. Clone repository
git clone https://github.com/alsyundawy/PHP-BindManager.git /var/www/php-bindmanager
cd /var/www/php-bindmanager

# 2. Copy production environment file
cp .env.example .env

# 3. Install composer dependencies (optimized autoloader)
composer install --no-dev --optimize-autoloader
```

### 3. Initialize Database & Seed Administrator

```bash
# Run database migrations (creates SQLite WAL tables)
php bin/migrate.php

# Seed initial roles and default administrator account
php bin/seed.php
```

> **Default Admin Credentials**:
>
> - **Username**: `admin`
> - **Password**: `ChangeMe@2026!`
> - *(Important: You will be prompted to change this immediately upon first login).*

### 4. File Permissions

```bash
# Ensure web server user can read/write the Storage directory
sudo chown -R www-data:www-data /var/www/php-bindmanager/Storage
sudo chmod -R 775 /var/www/php-bindmanager/Storage
```

### 5. Nginx Production Configuration

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name dns.example.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name dns.example.com;

    ssl_certificate /etc/ssl/certs/dns.example.com.crt;
    ssl_certificate_key /etc/ssl/private/dns.example.com.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    root /var/www/php-bindmanager/Public;
    index index.php;

    # Security Headers
    add_header X-Frame-Options "DENY" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Permissions-Policy "camera=(), microphone=(), geolocation=()" always;
    add_header Content-Security-Policy "default-src 'self'; style-src 'self' 'unsafe-inline'; script-src 'self'; font-src 'self'; img-src 'self' data:;" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## ⚙️ Configuration Reference

Key variables available in your `.env` configuration:

| Setting Key                 | Default Value                           | Description                                                   |
|:----------------------------|:----------------------------------------|:--------------------------------------------------------------|
| `APP_NAME`                  | `"PHP-BindManager"`                     | Application title displayed across headers and metadata.      |
| `APP_ENV`                   | `"production"`                          | Environment profile (`production`, `local`, `testing`).       |
| `APP_DEBUG`                 | `false`                                 | Enable detailed stack traces (Must be `false` in production). |
| `APP_URL`                   | `"https://dns.example.com"`             | Canonical URL of the control plane.                           |
| `DB_CONNECTION`             | `"sqlite"`                              | Database engine (`sqlite`).                                   |
| `DB_DATABASE`               | `"Storage/Database/bindmanager.sqlite"` | Relative or absolute path to SQLite file.                     |
| `SESSION_SECURE`            | `true`                                  | Enforces HTTPS-only cookies (SonarLint S3330 compliant).      |
| `SESSION_LIFETIME`          | `7200`                                  | Session idle expiration in seconds (2 hours).                 |
| `SESSION_SAMESITE`          | `"Strict"`                              | Cross-site cookie isolation policy (`Strict`, `Lax`).         |
| `SECURITY_RATE_LIMIT_LOGIN` | `5`                                     | Maximum failed login attempts before temporary IP lock.       |
| `BIND_CONFIG_PATH`          | `"/etc/bind/named.conf"`                | Path to primary BIND configuration file.                      |
| `BIND_ZONES_PATH`           | `"/etc/bind/zones"`                     | Directory where master zone files are written.                |
| `BIND_RNDC_PATH`            | `"/usr/sbin/rndc"`                      | Absolute path to `rndc` control utility.                      |

---

## 🌐 REST API & Automation Layer

PHP-BindManager features a RESTful API for automated zone generation, record provisioning, and CI/CD integration:

### Authentication

All API requests require a scoped Bearer token in the HTTP Authorization header:

```http
Authorization: Bearer pbm_your_generated_api_token_here
```

### Core Endpoints

| Method | Endpoint                     | Required Scope  | Description                                    |
|:-------|:-----------------------------|:----------------|:-----------------------------------------------|
| `GET`  | `/api/v1/zones`              | `zones:read`    | List all configured authoritative DNS zones.   |
| `POST` | `/api/v1/zones`              | `zones:write`   | Create a new forward or reverse DNS zone.      |
| `GET`  | `/api/v1/zones/{id}/records` | `records:read`  | Fetch all records associated with a zone.      |
| `POST` | `/api/v1/zones/{id}/records` | `records:write` | Add a new resource record to the zone.         |
| `GET`  | `/api/v1/system/health`      | `system:read`   | Inspect server load, memory, and named status. |

### Example cURL Request

```bash
curl -X POST https://dns.example.com/api/v1/zones/1/records \
  -H "Authorization: Bearer pbm_sec_8f92b41c0e" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "api",
    "type": "A",
    "content": "192.0.2.53",
    "ttl": 3600
  }'
```

---

## 📊 Quality Assurance & Verification Gates

Every commit of PHP-BindManager passes rigorous automated quality gates:

| Quality Gate             | Verification Engine                                               | Target / Standard                   | Pass Criteria               |       Status        |
|:-------------------------|:------------------------------------------------------------------|:------------------------------------|:----------------------------|:-------------------:|
| **Unit & Service Tests** | [`PHPUnit 11.5`](https://phpunit.de)                              | Core models, services, repositories | 100% assertions pass        |   **✔ 9/9 PASS**    |
| **Static Analysis**      | [`PHPStan`](https://phpstan.org)                                  | Strict Level 8 analysis             | 0 errors                    | **✔ LEVEL 8 CLEAN** |
| **Type Inference**       | [`Psalm`](https://psalm.dev)                                      | Level 4 strict type safety          | 0 errors, 96.27% inference  |     **✔ CLEAN**     |
| **Coding Standards**     | [`PHP_CodeSniffer`](https://github.com/squizlabs/PHP_CodeSniffer) | PSR-12 strict compliance            | 0 errors, 0 warnings        |  **✔ PSR-12 PASS**  |
| **Code Formatting**      | [`PHP-CS-Fixer`](https://cs.symfony.com)                          | Strict rule set                     | 0 fixable files remaining   |  **✔ 81/81 CLEAN**  |
| **Multi-Linter Engine**  | [`Trunk Check`](https://trunk.io)                                 | MarkdownLint, Prettier, TruffleHog  | 0 security or syntax issues |   **✔ 0 ISSUES**    |

---

## 📋 Engineering Standards & Invariants

To guarantee long-term maintainability, reliability, and security, the following invariants are enforced:

- **Strict Typing Mandatory**: Every PHP source file and view template declares `declare(strict_types=1);` at line 3.
- **Strict Line Length Bound**: All controllers, services, repositories, HTML/PHP view templates, and unit tests
  strictly adhere to $\le 120$ characters per line.
- **Zero Third-Party CDN Dependency**: No runtime asset requests may query external hosts. All vendor CSS, JS, and
  fonts must reside in `Public/assets/vendor/`.
- **Prepared Statements Exclusive**: Raw SQL query concatenations are strictly forbidden. All database operations
  must utilize PDO prepared statements with explicit parameter binding.
- **Fail-Safe Session Cookies**: Session cookies must always have `secure: true`, `httponly: true`, and
  `SameSite: Strict` configured.

---

## 🔒 Security & Content Safety

- **OWASP Top 10 Hardened**: Validated against SQL Injection, Cross-Site Scripting (XSS), Cross-Site Request Forgery
  (CSRF), Insecure Direct Object References (IDOR), and Broken Access Control.
- **Argon2id Password Hashes**: Passwords are saved with `password_hash($password, PASSWORD_ARGON2ID)` using secure
  memory and time cost factors.
- **Subresource Integrity (SRI)**: All local vendor assets are checksum-verified against vendor distributions.
- **Audit Trails**: Security actions (login attempts, zone modifications, privilege elevations) are persisted in the
  `activity_logs` table with IP addresses and user agents.

---

## 📂 Project Directory Structure

```text
PHP-BindManager/
├── App/                        # Application Source Code
│   ├── Application.php         # Application Bootstrap & Container Accessor
│   ├── Controllers/            # HTTP & API Endpoint Controllers
│   │   ├── Api/                # REST API Controllers (Zones, Health)
│   │   ├── Auth/               # Authentication & Session Controllers
│   │   ├── Dashboard/          # Dashboard Overview Controller
│   │   ├── Dns/                # Zone & Record Controllers
│   │   └── System/             # System Operations & API Docs Controller
│   ├── Enums/                  # PHP 8.4 Enums (RecordType, UserRole)
│   ├── Exceptions/             # Domain & HTTP Exceptions
│   ├── Middlewares/            # HTTP Middleware Stack (Auth, CSRF, Headers)
│   ├── Models/                 # Domain Entity Models
│   ├── Repositories/           # PDO Database Repositories
│   ├── Services/               # Domain Business Logic Layer
│   └── Support/                # Config, Env, Request, Response Helpers
├── Config/                     # Modular Application Configurations
│   ├── api.php                 # API & Rate Limit Config
│   ├── app.php                 # App Core Config
│   ├── bind9.php               # BIND9 Daemon & Path Settings
│   ├── database.php            # SQLite Database Connection Config
│   ├── logging.php             # System & Audit Logging Config
│   ├── rbac.php                # Role-Based Access Control Matrix
│   ├── security.php            # Security Headers, CSP & Rate Limits
│   └── session.php             # Session & Cookie Security Config
├── Database/                   # Database Migrations & Seeds
│   ├── Migrations/             # Schema Migrations (WAL, Indexes)
│   └── Seeds/                  # Default Admin & Roles Seeder
├── Docs/                       # Comprehensive Architecture Guides
├── Public/                     # Web Root (Publicly Accessible)
│   ├── index.php               # Front Controller
│   └── assets/                 # Local Assets (Zero CDN)
│       ├── css/app.min.css     # Visual Subnet Calculator Theme CSS
│       ├── js/app.min.js       # Theme & UI Controller JS
│       └── vendor/             # Local Vendor Distributions
│           ├── bootstrap/      # Bootstrap 3.5.8 (CSS & JS Bundle)
│           ├── fontawesome/    # Font Awesome 6.7.2 (Webfonts & CSS)
│           └── jquery/         # jQuery 3.7.1 Minified
├── Resources/                  # Server-Side View Templates
│   └── Views/                  # PHP HTML Views (Auth, Dashboard, Zones)
│       ├── auth/               # Login & Profile Views
│       ├── dashboard/          # Dashboard Overview
│       ├── errors/             # Error Pages (404, 500, CSRF)
│       ├── layouts/            # Base App Layout Template
│       ├── partials/           # Navbar, Sidebar, Modals
│       ├── records/            # DNS Records Index & Modal Forms
│       ├── system/             # System Status & API Docs
│       └── zones/              # Zones Index & Creation Forms
├── Routes/                     # Route Definitions (web.php, dns.php, api.php)
├── Storage/                    # Runtime Storage (Ignored by Git)
│   ├── Database/               # SQLite Database File Location
│   └── Logs/                   # Application & Audit Logs
├── Tests/                      # Automated Unit & Integration Tests
├── bin/                        # CLI Commands (migrate.php, seed.php)
├── CHANGELOG.md                # Full Semantic Versioning Changelog
├── DOCNOTE.md                  # Engineering Architecture Notes
├── INSTALL.md                  # Comprehensive Multi-OS Installation Guide
├── LICENSE                     # MIT Open Source License
├── phpstan.neon                # PHPStan Level 8 Configuration
├── psalm.xml                   # Psalm Strict Configuration
├── phpcs.xml                   # PHP_CodeSniffer PSR-12 Configuration
└── TUTORIAL.md                 # Complete BIND9 Master/Slave Deployment Tutorial
```

---

## 🤝 Contributing

Contributions are welcome! Please follow these guidelines:

1. Fork the repository and create your feature branch: `git checkout -b feature/amazing-feature`.
2. Ensure all changes adhere strictly to PSR-12 and max 120-character line lengths.
3. Verify that all quality gates pass: `vendor/bin/phpunit`, `phpstan analyse`, `psalm`, `phpcs`,
   `php-cs-fixer`, and `trunk check --no-fix`.
4. Commit your changes with conventional commit messages: `git commit -m 'feat: add DNSSEC rollover support'`.
5. Push to your branch and open a Pull Request.

---

## 📬 Maintainer & Contact

For technical inquiries, enterprise deployments, security consultations, or collaboration:

- **Lead Maintainer & Engineering**: **HARRY DERTIN SUTISNA ALSYUNDAWY** — [`ALSYUNDAWY IT SOLUTION`](https://alsyundawy.com)
- **Official Website**: [`https://alsyundawy.com`](https://alsyundawy.com)
- **GitHub Profile**: [`@alsyundawy`](https://github.com/alsyundawy)
- **Email**: [`alsyundawy@gmail.com`](mailto:alsyundawy@gmail.com)
- **Phone / WhatsApp / Telegram**: [`+62 856-8515-212`](tel:+628568515212)
- **Repository**: [`https://github.com/alsyundawy/PHP-BindManager`](https://github.com/alsyundawy/PHP-BindManager)

---

## 💖 Support & Donation

If **PHP-BindManager** has saved you time, enhanced your DNS operations, or provided value in your enterprise
infrastructure, consider supporting its continuous maintenance, security audits, and open-source development:

### 💳 International Support: PayPal

[![Donate with PayPal](https://img.shields.io/badge/Donate-PayPal-00457C?style=for-the-badge&logo=paypal&logoColor=white)](https://www.paypal.me/alsyundawy)

- **PayPal Link**: [`https://www.paypal.me/alsyundawy`](https://www.paypal.me/alsyundawy)

### 🇮🇩 Indonesian & Regional Support: QRIS (Quick Response Code Indonesian Standard)

Scan the QRIS barcode below using any Indonesian mobile banking app (BCA, Mandiri, BRI, BNI, BSI, CIMB Niaga, Permata)
or e-wallet (GoPay, OVO, DANA, LinkAja, ShopeePay):

![QRIS Donation Barcode - ALSYUNDAWY](https://github.com/user-attachments/assets/a0126f28-6dde-43da-ba14-d7c9a27de0df)

- **Merchant / Account Name**: **ALSYUNDAWY IT SOLUTION**
- **NMID**: **`ID1020021153676`**
- **Direct Barcode Asset Link**: [`https://github.com/user-attachments/assets/a0126f28-6dde-43da-ba14-d7c9a27de0df`](https://github.com/user-attachments/assets/a0126f28-6dde-43da-ba14-d7c9a27de0df)
- **WhatsApp Confirmation**: [`+62 856-8515-212`](https://wa.me/628568515212)

Your support directly powers open-source DNS infrastructure tooling, security enhancements, and continuous community improvements.

---

## 📄 License

PHP-BindManager is open-source software licensed under the [`MIT License`](LICENSE) © 2024–2026 Harry DS Alsyundawy.

Feel free to use, modify, and distribute it for personal, commercial, and enterprise infrastructure deployments.
