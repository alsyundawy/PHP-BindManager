# Installation Guide

## Requirements

| Component | Version | Notes |
|---|---|---|
| PHP | 8.4+ | Required |
| PHP Extensions | pdo_sqlite, sqlite3, mbstring, json, openssl, curl, intl, zip | All required |
| Nginx | 1.24+ | Recommended web server |
| PHP-FPM | 8.4+ | Required |
| BIND9 | 9.16+ | Must be installed and running |
| Composer | 2.x | Required |
| OS | Debian 12 / Ubuntu 22.04+ | Tested platforms |
| Disk | 500MB+ | For logs, cache, backups |

---

## Step 1 — Install System Dependencies

```bash
# Debian / Ubuntu
apt update && apt upgrade -y
apt install -y nginx php8.4 php8.4-fpm php8.4-sqlite3 php8.4-mbstring \
  php8.4-json php8.4-openssl php8.4-curl php8.4-intl php8.4-zip \
  php8.4-pdo php8.4-xml bind9 bind9utils bind9-doc sqlite3 git unzip curl
```

---

## Step 2 — Install Composer

```bash
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer
```

---

## Step 3 — Clone and Configure

```bash
cd /var/www
git clone https://github.com/alsyundawy/PHP-BindManager.git
cd PHP-BindManager
cp .env.example .env
nano .env  # Edit your configuration
```

---

## Step 4 — Install PHP Dependencies

```bash
composer install --no-dev --optimize-autoloader
```

---

## Step 5 — Directory Permissions

```bash
chown -R www-data:www-data /var/www/PHP-BindManager
chmod -R 755 /var/www/PHP-BindManager
chmod -R 775 /var/www/PHP-BindManager/Storage
chmod -R 775 /var/www/PHP-BindManager/Database
```

---

## Step 6 — Database Initialization

```bash
php bin/migrate.php
php bin/seed.php  # Creates default admin user
```

Default credentials (change immediately):
```
Username: admin
Password: ChangeMe@2026!
```

---

## Step 7 — Nginx Configuration

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/PHP-BindManager/Public;
    index index.php;

    # Redirect HTTP to HTTPS
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name your-domain.com;
    root /var/www/PHP-BindManager/Public;
    index index.php;

    ssl_certificate /etc/ssl/certs/your-cert.pem;
    ssl_certificate_key /etc/ssl/private/your-key.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;

    # Security Headers
    add_header X-Frame-Options "DENY" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Permissions-Policy "geolocation=(), camera=(), microphone=()" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    # Deny access to sensitive files
    location ~ /\.(env|git|htaccess|DS_Store) {
        deny all;
        return 404;
    }

    location ~ \.(sql|sqlite|db|log|ini|conf|json)$ {
        deny all;
        return 404;
    }

    # Cache static assets
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    # Gzip
    gzip on;
    gzip_vary on;
    gzip_min_length 1000;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml;
}
```

---

## Step 8 — BIND9 Permissions

```bash
# Allow www-data to manage BIND9 zone files
usermod -aG bind www-data
chmod 775 /etc/bind
chmod 664 /etc/bind/named.conf.local
```

---

## Step 9 — Enable and Start Services

```bash
systemctl enable nginx php8.4-fpm bind9
systemctl restart nginx php8.4-fpm bind9
```

---

## Step 10 — First Login

Navigate to `https://your-domain.com` and login with the default credentials.  
**Change the default password immediately.**

---

## Updating

```bash
cd /var/www/PHP-BindManager
git pull origin main
composer install --no-dev --optimize-autoloader
php bin/migrate.php
```

---

## Troubleshooting

| Issue | Solution |
|---|---|
| 502 Bad Gateway | Check `systemctl status php8.4-fpm` |
| Permission denied | Check `Storage/` and `Database/` ownership |
| BIND9 not updating | Check `www-data` is in `bind` group |
| SQLite locked | Verify WAL mode is enabled |
| CSRF errors | Ensure session is writable |
