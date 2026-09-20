<?php

declare(strict_types=1);

/** @var string $csrfToken */
$templateVars = get_defined_vars();
$csrfToken = (string) ($templateVars['csrfToken'] ?? '');
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
        <input
            id="username"
            name="username"
            type="text"
            required
            maxlength="64"
            placeholder="e.g. admin"
        >
        <label for="password">Password</label>
        <input
            id="password"
            name="password"
            type="password"
            required
            maxlength="255"
            placeholder="••••••••"
        >
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
