<?php

declare(strict_types=1);

$currentPath = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
if ($currentPath === false) {
    $currentPath = '/';
}
$csrfToken = (string) ($_SESSION['_csrf']['value'] ?? '');
?>
<aside class="pbm-sidebar" id="primary-sidebar" aria-label="Primary navigation">
    <div class="pbm-brand">
        <span class="pbm-brand-mark" aria-hidden="true"><i class="fa-solid fa-server"></i></span>
        <span>PHP-BindManager</span>
    </div>
    <div class="pbm-nav-title">Authoritative DNS</div>
    <nav aria-label="Authoritative DNS navigation">
        <a class="pbm-nav-link<?= pbmNavActive('/dashboard', $currentPath) ?>" href="/dashboard">
            <i class="fa-solid fa-gauge-high"></i> <span>Dashboard</span>
        </a>
        <a class="pbm-nav-link<?= pbmNavActive('/zones', $currentPath) ?>" href="/zones">
            <i class="fa-solid fa-layer-group"></i> <span>Zones</span>
        </a>
        <a class="pbm-nav-link<?= pbmNavActive('/records', $currentPath) ?>" href="/records">
            <i class="fa-solid fa-list-check"></i> <span>DNS Records</span>
        </a>
    </nav>
    <div class="pbm-nav-title">Administration</div>
    <nav aria-label="Administration navigation">
        <a class="pbm-nav-link<?= pbmNavActive('/system', $currentPath) ?>" href="/system">
            <i class="fa-solid fa-sliders"></i> <span>Operations</span>
        </a>
        <a class="pbm-nav-link<?= pbmNavActive('/api/docs', $currentPath) ?>" href="/api/docs">
            <i class="fa-solid fa-book-open"></i> <span>API Docs</span>
        </a>
    </nav>
    <div style="margin-top: auto; padding-top: 16px;">
        <form method="post" action="/logout" style="margin: 0;">
            <input
                type="hidden"
                name="_csrf_token"
                value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
            >
            <button
                type="submit"
                class="pbm-nav-link"
                style="width: 100%; border: none; background: none; cursor: pointer; text-align: left;"
            >
                <i class="fa-solid fa-arrow-right-from-bracket"></i> <span>Sign out</span>
            </button>
        </form>
    </div>
</aside>
