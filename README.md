# PHP-BindManager

![PHP 8.4+](https://img.shields.io/badge/PHP-8.4%2B-blue?logo=php)
![Bootstrap 5.3](https://img.shields.io/badge/Bootstrap-5.3.x-purple?logo=bootstrap)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-Latest-teal?logo=tailwindcss)
![SQLite3](https://img.shields.io/badge/SQLite3-WAL-green?logo=sqlite)
![License MIT](https://img.shields.io/badge/License-MIT-yellow)
![Status](https://img.shields.io/badge/Status-Development-orange)
![BIND9](https://img.shields.io/badge/BIND9-DNS%20Manager-red)

> **Enterprise-grade Web GUI for BIND9 DNS Server Management**  
> Built with PHP 8.4+, Bootstrap 5.3, Tailwind CSS, jQuery 3.7, SQLite3, RBAC, DNSSEC, and a full REST API.

---

## ✨ Features

| Category             | Features                                                     |
| -------------------- | ------------------------------------------------------------ |
| **Dashboard**        | Real-time stats, widgets, system health, activity feed       |
| **Zone Management**  | Forward, Reverse, DNSSEC, Templates, Import/Export           |
| **Record Types**     | A, AAAA, CNAME, MX, NS, TXT, PTR, SRV, CAA, NAPTR, TLSA, SOA |
| **Security**         | RBAC, CSRF, XSS, CSP, Brute-force, Rate-limiting, Argon2id   |
| **API**              | REST API with Token auth, full Swagger/OpenAPI docs          |
| **ACL & Views**      | BIND9 ACL and Views management                               |
| **Backup & Restore** | Full zone backup and restore system                          |
| **Audit Trail**      | Full audit log with user, IP, action, timestamp              |
| **Multi-user**       | Role-Based Access Control (Admin, Editor, Viewer)            |
| **Theme**            | Light / Dark / Auto (prefers-color-scheme)                   |

---

## 🚀 Quick Start

See [INSTALL.md](INSTALL.md) for full installation instructions and [TUTORIAL.md](TUTORIAL.md) for a comprehensive multi-distribution production deployment and BIND9 configuration guide. Read [DOCNOTE.md](DOCNOTE.md) for architecture and engineering standards.

```bash
git clone https://github.com/alsyundawy/PHP-BindManager.git
cd PHP-BindManager
cp .env.example .env
composer install --no-dev --optimize-autoloader
php bin/migrate.php
php bin/seed.php
```

---

## 🏗️ Architecture

See [ARCHITECTURE.md](Docs/ARCHITECTURE.md) and [DOCNOTE.md](DOCNOTE.md) for full architectural documentation.

```text
Request → Nginx → PHP-FPM → public/index.php
       → Router → Middleware Stack → Controller
       → Service Layer → Repository → SQLite3
       → View (Twig-compatible PHP templates)
       → Response
```

---

## 🔒 Security

See [SECURITY.md](SECURITY.md) for full security policy.

- CSP, CSRF, XSS, Clickjacking, HSTS, SameSite Cookies
- Argon2id password hashing
- Brute-force & rate limiting
- Input validation, output escaping, prepared statements
- Least privilege, defense in depth

---

## 📋 Requirements

| Component      | Version                                                            |
| -------------- | ------------------------------------------------------------------ |
| PHP            | 8.4+                                                               |
| PHP Extensions | pdo_sqlite, sqlite3, mbstring, json, openssl, curl, intl           |
| Nginx          | 1.24+                                                              |
| PHP-FPM        | 8.4+                                                               |
| BIND9          | 9.16+                                                              |
| Composer       | 2.x                                                                |
| OS             | Ubuntu 22.04/24.04, Debian 11/12, Rocky Linux 8/9, CentOS 7/Stream |

---

## 📂 Project Structure

```text
PHP-BindManager/
├── App/                    # Application core
│   ├── Controllers/
│   ├── Models/
│   ├── Repositories/
│   ├── Services/
│   ├── Libraries/
│   ├── Helpers/
│   ├── Traits/
│   ├── Enums/
│   ├── DTO/
│   ├── Validators/
│   ├── Policies/
│   ├── Middlewares/
│   ├── Events/
│   └── Exceptions/
├── Config/                 # App & route config
├── Routes/                 # Route definitions
├── Database/               # Migrations, seeders, schema
├── Storage/                # Cache, logs, uploads
├── Resources/              # Views, assets source
├── Public/                 # Web root (index.php, assets)
├── Tests/                  # PHPUnit test suites
├── Docs/                   # Full documentation
├── bin/                    # CLI tools
└── vendor/                 # Composer dependencies
```

---

## 🤝 Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md).

---

## 📄 License

MIT License — see [LICENSE](LICENSE).

---

## 👤 Author

**alsyundawy** — [github.com/alsyundawy](https://github.com/alsyundawy)  
ALSYUNDAWY.COM · DKI Jakarta, Indonesia
