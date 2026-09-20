<?php

declare(strict_types=1);

/** @var string $csrfToken */
$csrfToken = isset($csrfToken) ? (string) $csrfToken : '';
?>
<!doctype html>
<html lang="en" data-theme="dark" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Login - PHP-BindManager</title>
    <!-- Local Offline Vendor CSS (Zero CDN) -->
    <link rel="stylesheet" href="/assets/vendor/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/vendor/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/app.min.css">
    <style>
        body { margin: 0; font-family: Inter, system-ui, sans-serif; background: var(--pbm-bg, #0b0f19); color: var(--pbm-text, #f8fafc); }
        .login-wrap { min-height: 100vh; min-height: 100dvh; display: grid; place-items: center; padding: 24px; padding-top: max(24px, env(safe-area-inset-top, 24px)); padding-bottom: max(24px, env(safe-area-inset-bottom, 24px)); }
        .login-card { width: 100%; max-width: 420px; background: var(--pbm-surface, #111827); border: 1px solid var(--pbm-border, #334155); border-radius: 18px; padding: 32px; box-shadow: var(--pbm-shadow, 0 18px 40px rgba(0,0,0,.35)); }
        .login-card input { width: 100%; padding: 12px 14px; margin-top: 8px; margin-bottom: 16px; border-radius: 12px; border: 1px solid var(--pbm-border, #334155); background: var(--pbm-surface-2, #1e293b); color: var(--pbm-text, #f8fafc); box-sizing: border-box; }
        .login-card input:focus { outline: 2px solid var(--pbm-primary, #3b82f6); border-color: var(--pbm-primary, #3b82f6); }
        .login-card label { display: block; font-size: .88rem; color: var(--pbm-muted, #94a3b8); font-weight: 600; }
        .login-card p { color: var(--pbm-muted, #94a3b8); font-size: .9rem; }
    </style>
</head>
<body>
<div class="login-wrap">
    <form class="login-card" method="post" action="/login" autocomplete="off">
        <div class="d-flex align-items-center gap-2 mb-3">
            <span class="pbm-brand-mark"><i class="fa-solid fa-server"></i></span>
            <span class="fs-4 fw-bold">PHP-BindManager</span>
        </div>
        <h2 class="h4 fw-bold mb-1">Sign in</h2>
        <p>Use your administrator credentials to access DNS management.</p>
        <input type="hidden" name="_csrf_token" value="<?= e($csrfToken ?? '') ?>">
        <label for="username">Username</label>
        <input id="username" name="username" type="text" required maxlength="64" placeholder="e.g. admin">
        <label for="password">Password</label>
        <input id="password" name="password" type="password" required maxlength="255" placeholder="••••••••">
        <button class="pbm-btn pbm-btn-primary w-100 py-2 fs-6 fw-semibold" type="submit">
            <i class="fa-solid fa-right-to-bracket me-2"></i>Sign In
        </button>
    </form>
</div>
<!-- Local Offline Vendor JS (Zero CDN) -->
<script src="/assets/vendor/jquery/jquery.min.js"></script>
<script src="/assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>
