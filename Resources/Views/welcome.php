<?php

declare(strict_types=1);

/** @var string $appName */
$templateVars = get_defined_vars();
$appName = (string) ($templateVars['appName'] ?? 'PHP-BindManager');
?>
<!doctype html>
<html lang="en" data-theme="dark" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="robots" content="index, follow">
    <meta name="description" content="PHP-BindManager - Enterprise Web GUI for BIND9 Authoritative DNS.">
    <meta property="og:title" content="<?= e($appName) ?> - Authoritative DNS Manager">
    <meta property="og:description" content="Enterprise Web GUI for BIND9 Authoritative DNS Infrastructure.">
    <meta property="og:type" content="website">
    <title><?= e($appName) ?> - Authoritative DNS Manager</title>
    <!-- Local Offline Vendor CSS (Zero CDN) -->
    <link rel="stylesheet" href="/assets/vendor/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/vendor/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/app.min.css">
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "PHP-BindManager",
        "operatingSystem": "Linux",
        "applicationCategory": "DeveloperApplication",
        "description": "Enterprise Web GUI for BIND9 Authoritative DNS Infrastructure."
    }
    </script>
    <style>
        :root {
            color-scheme: light dark;
            font-family: Inter, system-ui, sans-serif;
        }
        body {
            margin: 0;
            background: var(--pbm-bg, #0b0f19);
            color: var(--pbm-text, #f8fafc);
        }
        .wrap {
            max-width: 960px;
            margin: 0 auto;
            padding: 48px 20px;
        }
        .card {
            background: var(--pbm-surface, #111827);
            border: 1px solid var(--pbm-border, #1f2937);
            border-radius: 18px;
            padding: 32px;
            box-shadow: 0 24px 48px rgba(0, 0, 0, 0.35);
        }
        h1 {
            margin: 0 0 16px;
            font-size: 2.2rem;
        }
        p {
            color: var(--pbm-muted, #94a3b8);
            line-height: 1.7;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-top: 28px;
        }
        .item {
            padding: 16px;
            border-radius: 14px;
            background: var(--pbm-surface-2, #1e293b);
            border: 1px solid var(--pbm-border, #334155);
        }
        a {
            color: var(--pbm-primary, #3b82f6);
            text-decoration: none;
            font-weight: 600;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <h1>
            <i class="fa-solid fa-server text-primary me-2"></i><?= e($appName ?? 'PHP-BindManager') ?>
        </h1>
        <p>
            Enterprise-grade Web GUI for BIND9 DNS management with secure authentication,
            RBAC, rate limiting, SQLite WAL, and modular MVC architecture.
        </p>
        <div class="grid">
            <div class="item">
                <strong><i class="fa-solid fa-code me-2"></i>Backend</strong><br>
                PHP 8.4+, PSR stack, Repository + Service Layer
            </div>
            <div class="item">
                <strong><i class="fa-solid fa-shield-halved me-2"></i>Security</strong><br>
                CSRF, CSP, secure session, brute-force protection
            </div>
            <div class="item">
                <strong><i class="fa-solid fa-database me-2"></i>Database</strong><br>
                SQLite3 WAL, indexes, foreign keys, transactions
            </div>
            <div class="item">
                <strong><i class="fa-solid fa-arrow-right-to-bracket me-2"></i>Next</strong><br>
                <a href="/login">Proceed to login &rarr;</a>
            </div>
        </div>
    </div>
</div>
<!-- Local Offline Vendor JS (Zero CDN) -->
<script src="/assets/vendor/jquery/jquery.min.js"></script>
<script src="/assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>
