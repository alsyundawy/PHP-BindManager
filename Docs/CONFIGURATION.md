# Configuration Reference

All configuration is managed via the `.env` file and `Config/` PHP config files.

---

## Environment Variables (.env)

```ini
# ==============================
# APP
# ==============================
APP_NAME="PHP-BindManager"
APP_ENV=production           # production | development | testing
APP_DEBUG=false
APP_URL=https://your-domain.com
APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=en

# ==============================
# DATABASE
# ==============================
DB_PATH=/var/www/PHP-BindManager/Database/bindmanager.sqlite
DB_WAL=true
DB_FOREIGN_KEYS=true

# ==============================
# SESSION
# ==============================
SESSION_NAME=pbm_session
SESSION_LIFETIME=7200        # seconds
SESSION_SECURE=true
SESSION_HTTPONLY=true
SESSION_SAMESITE=Strict
SESSION_DRIVER=file          # file | database

# ==============================
# SECURITY
# ==============================
APP_KEY=                     # 32-byte random key (run: php bin/generate-key.php)
CSRF_TOKEN_LIFETIME=3600
RATE_LIMIT_LOGIN=10          # max attempts per minute
RATE_LIMIT_API=300           # requests per minute per token
BRUTE_FORCE_MAX=5            # max failed login attempts
BRUTE_FORCE_LOCKOUT=900      # lockout duration in seconds
PASSWORD_MIN_LENGTH=12

# ==============================
# BIND9
# ==============================
BIND9_ZONES_DIR=/etc/bind/zones
BIND9_CONF_DIR=/etc/bind
BIND9_NAMED_CONF=/etc/bind/named.conf.local
BIND9_CHECKZONE=/usr/sbin/named-checkzone
BIND9_CHECKCONF=/usr/sbin/named-checkconf
BIND9_RNDC=/usr/sbin/rndc
BIND9_KEYGEN=/usr/sbin/dnssec-keygen
BIND9_SIGNZONE=/usr/sbin/dnssec-signzone
BIND9_USER=bind

# ==============================
# LOGGING
# ==============================
LOG_LEVEL=warning            # debug | info | warning | error | critical
LOG_PATH=/var/www/PHP-BindManager/Storage/Logs
LOG_MAX_FILES=30             # days to retain

# ==============================
# CACHE
# ==============================
CACHE_DRIVER=file            # file | array
CACHE_TTL=3600
CACHE_PATH=/var/www/PHP-BindManager/Storage/Cache

# ==============================
# MAIL (for notifications)
# ==============================
MAIL_DRIVER=smtp             # smtp | null
MAIL_HOST=smtp.your-domain.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="PHP-BindManager"

# ==============================
# API
# ==============================
API_ENABLED=true
API_VERSION=v1
API_RATE_LIMIT=300
API_TOKEN_LIFETIME=31536000  # 1 year in seconds
```

---

## Config Files

| File | Purpose |
|---|---|
| `Config/app.php` | App-level settings |
| `Config/database.php` | Database connection config |
| `Config/session.php` | Session settings |
| `Config/security.php` | Security settings (CSP, headers) |
| `Config/bind9.php` | BIND9 paths and settings |
| `Config/logging.php` | Log channel definitions |
| `Config/cache.php` | Cache driver settings |
| `Config/mail.php` | Mail settings |
| `Config/api.php` | API settings |
| `Config/rbac.php` | Role and permission definitions |
