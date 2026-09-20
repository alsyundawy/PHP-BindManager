<?php

declare(strict_types=1);

$currentPath = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
if ($currentPath === false) {
    $currentPath = '/';
}
$sessionRole = (string) ($_SESSION['role'] ?? 'viewer');
$csrfToken   = (string) ($_SESSION['_csrf']['value'] ?? '');
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
        <a class="pbm-nav-link<?= pbmNavActive('/acls', $currentPath) ?>" href="/acls">
            <i class="fa-solid fa-shield-halved"></i> <span>Access Control (ACL)</span>
        </a>
        <a class="pbm-nav-link<?= pbmNavActive('/views', $currentPath) ?>" href="/views">
            <i class="fa-solid fa-diagram-project"></i> <span>Split-Horizon Views</span>
        </a>
        <a class="pbm-nav-link<?= pbmNavActive('/dnssec', $currentPath) ?>" href="/dnssec">
            <i class="fa-solid fa-key"></i> <span>DNSSEC Manager</span>
        </a>
    </nav>
    <div class="pbm-nav-title">Administration</div>
    <nav aria-label="Administration navigation">
        <a class="pbm-nav-link<?= pbmNavActive('/system', $currentPath) ?>" href="/system">
            <i class="fa-solid fa-sliders"></i> <span>Operations</span>
        </a>
        <a class="pbm-nav-link<?= pbmNavActive('/system/backups', $currentPath) ?>" href="/system/backups">
            <i class="fa-solid fa-box-archive"></i> <span>Backups & Restore</span>
        </a>
        <a class="pbm-nav-link<?= pbmNavActive('/system/activity', $currentPath) ?>" href="/system/activity">
            <i class="fa-solid fa-clock-rotate-left"></i> <span>Activity Log</span>
        </a>
        <a class="pbm-nav-link<?= pbmNavActive('/system/audit-logs', $currentPath) ?>" href="/system/audit-logs">
            <i class="fa-solid fa-clipboard-check"></i> <span>Audit Trail</span>
        </a>
        <a class="pbm-nav-link<?= pbmNavActive('/system/tokens', $currentPath) ?>" href="/system/tokens">
            <i class="fa-solid fa-key"></i> <span>API Tokens</span>
        </a>
        <?php if ($sessionRole === 'admin') : ?>
            <a class="pbm-nav-link<?= pbmNavActive('/users', $currentPath) ?>" href="/users">
                <i class="fa-solid fa-users-gear"></i> <span>User Accounts</span>
            </a>
        <?php endif; ?>
        <a class="pbm-nav-link<?= pbmNavActive('/profile', $currentPath) ?>" href="/profile">
            <i class="fa-solid fa-user"></i> <span>My Profile</span>
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
