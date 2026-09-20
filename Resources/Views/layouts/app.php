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
    <meta name="theme-color" content="#2563eb" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#0b0f19" media="(prefers-color-scheme: dark)">
    <meta name="color-scheme" content="light dark">
    <meta name="robots" content="noindex, nofollow">
    <meta name="format-detection" content="telephone=no, date=no, address=no, email=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="BindManager">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="description" content="PHP-BindManager BIND9 Authoritative DNS operations platform.">
    <meta property="og:title" content="<?= e($title ?? 'PHP-BindManager') ?>">
    <meta property="og:description" content="Enterprise Web GUI for BIND9 Authoritative DNS Infrastructure.">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="PHP-BindManager">
    <link rel="manifest" href="/assets/manifest.json">
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
    <script>
        try {
            const saved = localStorage.getItem("pbm-theme") || "auto";
            const prefersDark = matchMedia("(prefers-color-scheme: dark)").matches;
            const theme = saved === "auto" ? (prefersDark ? "dark" : "light") : saved;
            document.documentElement.dataset.theme = theme;
            document.documentElement.setAttribute("data-bs-theme", theme);
        } catch (e) {}
    </script>
    <title><?= e($title ?? 'PHP-BindManager') ?></title>
</head>
<body>
    <a href="#pbm-main-content" class="pbm-skip-link">Skip to main content</a>
    <div class="pbm-app">
        <?php include_once __DIR__ . '/../partials/sidebar.php'; ?>
        <div class="pbm-sidebar-backdrop" aria-hidden="true"></div>
        <?php include_once __DIR__ . '/../partials/navbar.php'; ?>
        <main id="pbm-main-content" class="pbm-main" tabindex="-1">
            <div class="pbm-container">
                <?php if (isset($_SESSION['flash_success']) && is_string($_SESSION['flash_success'])) : ?>
                    <div class="pbm-alert pbm-alert-success mb-3" role="alert">
                        <i class="fa-solid fa-circle-check me-2"></i>
                        <?= e($_SESSION['flash_success']) ?>
                    </div>
                    <?php unset($_SESSION['flash_success']); ?>
                <?php endif; ?>
                <?php if (isset($_SESSION['flash_error']) && is_string($_SESSION['flash_error'])) : ?>
                    <div class="pbm-alert pbm-alert-danger mb-3" role="alert">
                        <i class="fa-solid fa-circle-exclamation me-2"></i>
                        <?= e($_SESSION['flash_error']) ?>
                    </div>
                    <?php unset($_SESSION['flash_error']); ?>
                <?php endif; ?>
                <?= $content ?? '' ?>
                <footer class="pbm-footer" role="contentinfo">
                    <div>
                        <strong>PHP-BindManager</strong> v1.0.1 &bull; BIND 9 Authoritative DNS
                    </div>
                    <div>
                        <span>Zero-CDN &bull; SQLite WAL &bull; PHP <?= PHP_VERSION ?></span>
                    </div>
                </footer>
            </div>
        </main>
    </div>
    <!-- Local Offline Vendor JS (Zero CDN) -->
    <script src="/assets/vendor/jquery/jquery.min.js"></script>
    <script src="/assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/app.min.js" defer></script>
</body>
</html>
