# Production BIND 9 Authoritative DNS Deployment & Administration Guide

## 1. Overview and Architecture

PHP-BindManager is designed strictly as an **Authoritative Primary (Master) and Secondary (Slave) DNS Management
Platform**. In accordance with modern Internet security and RFC standards (RFC 7706, RFC 8482), recursive resolution
should never be commingled on public authoritative nameservers. This eliminates DNS amplification, cache poisoning
(Kaminsky attacks), and unnecessary RPZ (Response Policy Zone) overhead.

```text
       ┌─────────────────────────────────────────────────────────────┐
       │                   Authoritative Master                      │
       │                   (PHP-BindManager GUI)                     │
       │           named.conf.local + SQLite WAL Storage             │
       └──────────────────────────────┬──────────────────────────────┘
                                      │ TSIG (HMAC-SHA256)
                                      │ AXFR / IXFR + NOTIFY
                                      ▼
       ┌─────────────────────────────────────────────────────────────┐
       │                   Authoritative Slave(s)                    │
       │                   (Read-only edge DNS)                      │
       │                 named.conf.local (slaves)                   │
       └──────────────────────────────┬──────────────────────────────┘
                                      │ DNS Queries (UDP/TCP 53)
                                      ▼
                             Public Resolvers / Clients
```

---

## 2. Multi-Distribution Installation & Prerequisites

### 2.1 Ubuntu 22.04 / 24.04 LTS & Debian 11 / 12

```bash
# Update repository index
sudo apt update && sudo apt upgrade -y

# Install BIND9, utilities, and PHP 8.4 runtime dependencies
sudo apt install -y \
    bind9 \
    bind9utils \
    bind9-doc \
    dnsutils \
    nginx \
    php8.4-fpm \
    php8.4-cli \
    php8.4-sqlite3 \
    php8.4-mbstring \
    php8.4-curl \
    php8.4-xml \
    sqlite3 \
    ufw

# Verify named service status
sudo systemctl enable --now named
sudo systemctl status named
```

### 2.2 Rocky Linux 8 / 9 & CentOS 7 / Stream

```bash
# Enable EPEL and REMI repositories (for modern PHP)
sudo dnf install -y epel-release
sudo dnf install -y https://rpms.remirepo.net/enterprise/remi-release-$(rpm -E %rhel).rpm
sudo dnf module reset php -y
sudo dnf module enable php:remi-8.4 -y

# Install BIND9 and runtime stack
sudo dnf install -y \
    bind \
    bind-utils \
    nginx \
    php-fpm \
    php-cli \
    php-pdo \
    php-mbstring \
    sqlite \
    firewalld

# Enable named service
sudo systemctl enable --now named
sudo systemctl status named
```

---

## 3. Authoritative Master / Slave Configuration with TSIG

### 3.1 Generating the TSIG Transfer Key

Generate a dedicated transfer key using `tsig-keygen`:

```bash
tsig-keygen -a hmac-sha256 transfer-key > /etc/bind/transfer-key.key
# (On Rocky/CentOS, save to /etc/named/transfer-key.key)

sudo chown root:bind /etc/bind/transfer-key.key
sudo chmod 0640 /etc/bind/transfer-key.key
```

### 3.2 Master Server Configuration (`/etc/bind/named.conf.options`)

Ensure recursion is strictly disabled:

```text
options {
    directory "/var/cache/bind";

    // Strictly Authoritative Only
    recursion no;
    allow-recursion { none; };
    allow-query-cache { none; };

    // Default query policy
    allow-query { any; };

    // Response Rate Limiting (RRL) to mitigate DNS Amplification attacks
    rate-limit {
        responses-per-second 10;
        window 5;
    };

    listen-on-v6 { any; };
    listen-on { any; };

    dnssec-validation auto;
    auth-nxdomain no;
};
```

### 3.3 Zone Definition with TSIG (`/etc/bind/named.conf.local`)

On the Master server:

```text
include "/etc/bind/transfer-key.key";

zone "example.com" {
    type master;
    file "/var/lib/bind/zones/db.example.com";
    allow-transfer { key "transfer-key"; };
    also-notify { 198.51.100.2; }; // IP address of Slave DNS
    notify yes;
};
```

On the Slave server (`/etc/bind/named.conf.local`):

```text
include "/etc/bind/transfer-key.key";

server 198.51.100.1 { // IP of Master DNS
    keys { "transfer-key"; };
};

zone "example.com" {
    type slave;
    file "/var/lib/bind/zones/slaves/db.example.com";
    masters { 198.51.100.1 key "transfer-key"; };
    allow-transfer { none; };
};
```

---

## 4. Mail DNS Infrastructure & Security Records

Proper authoritative DNS configuration is mandatory to guarantee email deliverability and thwart domain spoofing.

### 4.1 Zone File Template with Full Mail Protections

```text
$ORIGIN example.com.
$TTL 3600

@   IN  SOA ns1.example.com. hostmaster.example.com. (
            2026092001 ; Serial YYYYMMDDNN
            7200       ; Refresh (2 hours)
            3600       ; Retry (1 hour)
            1209600    ; Expire (2 weeks)
            3600 )     ; Minimum TTL (1 hour)

; Nameservers
@       IN  NS      ns1.example.com.
@       IN  NS      ns2.example.com.

; A Records
@       IN  A       192.0.2.10
ns1     IN  A       198.51.100.1
ns2     IN  A       198.51.100.2
mail    IN  A       192.0.2.25

; Mail Exchange (MX)
@       IN  MX  10  mail.example.com.

; SPF (Sender Policy Framework - Hard Fail)
@       IN  TXT     "v=spf1 mx ip4:192.0.2.25 -all"

; DKIM (DomainKeys Identified Mail - 2048-bit RSA)
mail._domainkey IN TXT "v=DKIM1; k=rsa; p=MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA3V4h..."

; DMARC (Domain-based Message Authentication - Strict Reject)
_dmarc  IN  TXT     "v=DMARC1; p=reject; sp=reject; pct=100; rua=mailto:dmarc-rua@example.com; ruf=mailto:dmarc-ruf@example.com; adkim=s; aspf=s"

; Certificate Authority Authorization (CAA)
@       IN  CAA 0 issue "letsencrypt.org"
@       IN  CAA 0 issuewild ";"
@       IN  CAA 0 iodef "mailto:security@example.com"

; TLSA (DANE for Port 25 SMTP)
25._tcp.mail IN TLSA 3 1 1 e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855
```

### 4.2 Reverse DNS (PTR)

Create the corresponding reverse in-addr zone for the mail server IP (`192.0.2.25` -> `25.2.0.192.in-addr.arpa.`):

```text
$ORIGIN 2.0.192.in-addr.arpa.
$TTL 3600
@   IN  SOA ns1.example.com. hostmaster.example.com. (
            2026092001 7200 3600 1209600 3600 )
@   IN  NS  ns1.example.com.
25  IN  PTR mail.example.com.
```

---

## 5. Firewall & Network Rate Limiting

### 5.1 UFW (Ubuntu / Debian)

```bash
# Allow inbound DNS queries
sudo ufw allow 53/tcp comment "BIND9 TCP"
sudo ufw allow 53/udp comment "BIND9 UDP"

# Allow Web UI
sudo ufw allow 80/tcp comment "HTTP Web"
sudo ufw allow 443/tcp comment "HTTPS Web"

# Enable firewall
sudo ufw enable
sudo ufw status verbose
```

### 5.2 Firewalld (Rocky Linux / CentOS)

```bash
sudo firewall-cmd --permanent --add-service=dns
sudo firewall-cmd --permanent --add-service=http
sudo firewall-cmd --permanent --add-service=https
sudo firewall-cmd --reload
sudo firewall-cmd --list-all
```

### 5.3 Advanced iptables Rate Limiting against UDP Floods

Mitigate automated scanning and amplification at the kernel level:

```bash
# Rate limit inbound UDP DNS requests per source IP
sudo iptables -A INPUT -p udp --dport 53 -m hashlimit \
    --hashlimit-name dns_rate \
    --hashlimit-above 25/sec \
    --hashlimit-burst 50 \
    --hashlimit-mode srcip \
    --hashlimit-htable-expire 30000 \
    -j DROP
```

---

## 6. Backup, Restoration, and Zimbra Mail Patterns

### 6.1 PHP-BindManager SQLite Atomic Backup

SQLite's WAL mode enables online atomic snapshotting using `VACUUM INTO`:

```bash
# Run automated database snapshot via CLI
php bin/console backup:create

# Restore snapshot securely
php bin/console backup:restore Storage/Backups/bindmanager-20260920-000000-xxxxxx.sqlite
```

### 6.2 Zimbra Mail Backup / Restore Integration Patterns

When managing DNS for Zimbra Collaboration Suites (Open Source, Zextras, or Network Edition):

1. **Cold & Online Snapshots**:
    - `zmbackup` (Network Edition): Full LDAP, mailstore, and configuration state backup.
    - Zextras Suite Real-Time Engine: Continuous item-level backup engine.
2. **DNS Disaster Recovery Protocol**:
    - In cross-datacenter failover, lower the zone TTL from `86400` to `300` at least 24 hours prior to scheduled migration.
    - Verify secondary slave synchronization immediately using `dig AXFR`:

        ```bash
        dig @ns2.example.com example.com AXFR
        ```

    - Update `mail.example.com` A and MX records to point to standby Zimbra infrastructure.

---

## 7. Diagnostics and Quality Verification

Verify zone syntax and service health before reloading:

```bash
# 1. Validate configuration files
named-checkconf /etc/bind/named.conf

# 2. Validate zone file syntax
named-checkzone example.com /var/lib/bind/zones/db.example.com

# 3. Reload BIND9 daemon without downtime
rndc reload

# 4. Verify authoritative response
dig @127.0.0.1 example.com SOA +multiline +norec
```
