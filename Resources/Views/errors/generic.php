<?php

declare(strict_types=1);

/** @var int|string $statusCode */
$statusCode = isset($statusCode) ? (string) $statusCode : '500';

/** @var string $message */
$message = isset($message) ? (string) $message : 'An error occurred';
?>
<!doctype html>
<html lang="en" data-theme="dark" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?= e((string) $statusCode) ?> - Error</title>
    <!-- Local Offline Vendor CSS (Zero CDN) -->
    <link rel="stylesheet" href="/assets/vendor/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/vendor/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/app.min.css">
    <style>
        body { margin: 0; font-family: Inter, system-ui, sans-serif; background: var(--pbm-bg, #0b0f19); color: var(--pbm-text, #f8fafc); }
        .wrap { min-height: 100vh; min-height: 100dvh; display: grid; place-items: center; padding: 24px; }
        .box { width: 100%; max-width: 640px; background: var(--pbm-surface, #111827); border: 1px solid var(--pbm-border, #334155); border-radius: 16px; padding: 28px; box-shadow: var(--pbm-shadow); }
        a { color: var(--pbm-primary, #3b82f6); text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="box">
        <h1 class="h2 fw-bold text-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i><?= e((string) $statusCode) ?></h1>
        <p class="text-secondary"><?= e($message ?? 'An error occurred.') ?></p>
        <p class="mt-4"><a href="/"><i class="fa-solid fa-house me-1"></i>Return to home</a></p>
    </div>
</div>
<!-- Local Offline Vendor JS (Zero CDN) -->
<script src="/assets/vendor/jquery/jquery.min.js"></script>
<script src="/assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>
