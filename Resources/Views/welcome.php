<!doctype html>
<html lang="en" data-theme="auto">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($appName ?? 'PHP-BindManager') ?></title>
    <style>
        :root { color-scheme: light dark; font-family: Inter, system-ui, sans-serif; }
        body { margin: 0; background: #0b1220; color: #e5e7eb; }
        .wrap { max-width: 960px; margin: 0 auto; padding: 48px 20px; }
        .card { background: #111827; border: 1px solid #1f2937; border-radius: 18px; padding: 32px; box-shadow: 0 24px 48px rgba(0,0,0,.35); }
        h1 { margin: 0 0 16px; font-size: 2.2rem; }
        p { color: #cbd5e1; line-height: 1.7; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit,minmax(220px,1fr)); gap: 16px; margin-top: 28px; }
        .item { padding: 16px; border-radius: 14px; background: #0f172a; border: 1px solid #1e293b; }
        a { color: #93c5fd; text-decoration: none; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <h1><?= e($appName ?? 'PHP-BindManager') ?></h1>
        <p>Enterprise-grade Web GUI for BIND9 DNS management with secure authentication, RBAC, rate limiting, SQLite WAL, and modular MVC architecture.</p>
        <div class="grid">
            <div class="item"><strong>Backend</strong><br>PHP 8.4+, PSR stack, Repository + Service Layer</div>
            <div class="item"><strong>Security</strong><br>CSRF, CSP, secure session, brute-force protection</div>
            <div class="item"><strong>Database</strong><br>SQLite3 WAL, indexes, foreign keys, transactions</div>
            <div class="item"><strong>Next</strong><br><a href="/login">Proceed to login</a></div>
        </div>
    </div>
</div>
</body>
</html>
