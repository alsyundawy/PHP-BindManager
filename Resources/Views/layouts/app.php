<?php

declare(strict_types=1);

/** @var string $title */
/** @var string $content */
$templateVars = get_defined_vars();
$title = (string) ($templateVars['title'] ?? 'PHP-BindManager');
$content = (string) ($templateVars['content'] ?? '');
?>
<!doctype html>
<html lang="en" data-theme="dark" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#2563eb">
    <meta name="description" content="PHP-BindManager BIND9 DNS operations dashboard">
    <link rel="manifest" href="/assets/manifest.json">
    <!-- Local Offline Vendor CSS (Zero CDN) -->
    <link rel="stylesheet" href="/assets/vendor/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/vendor/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/app.min.css">
    <script>
        try {
            const saved = localStorage.getItem("pbm-theme") || "auto";
            const theme = saved === "auto" ? (matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light") : saved;
            document.documentElement.dataset.theme = theme;
            document.documentElement.setAttribute("data-bs-theme", theme);
        } catch (e) {}
    </script>
    <title><?= e($title ?? 'PHP-BindManager') ?></title>
</head>
<body>
    <div class="pbm-app">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>
        <?php include __DIR__ . '/../partials/navbar.php'; ?>
        <main class="pbm-main">
            <div class="pbm-container">
                <?= $content ?? '' ?>
            </div>
        </main>
    </div>
    <!-- Local Offline Vendor JS (Zero CDN) -->
    <script src="/assets/vendor/jquery/jquery.min.js"></script>
    <script src="/assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/app.min.js" defer></script>
</body>
</html>