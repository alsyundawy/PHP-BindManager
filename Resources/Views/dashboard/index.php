<?php

declare(strict_types=1);

/** @var int $zoneCount */
/** @var int $recordCount */
/** @var bool $bind9Healthy */
/** @var array<int, array<string, mixed>> $recentZones */
/** @var string $appName */
$templateVars  = get_defined_vars();
$zoneCount     = (int) ($templateVars['zoneCount'] ?? 0);
$recordCount   = (int) ($templateVars['recordCount'] ?? 0);
$bind9Healthy  = (bool) ($templateVars['bind9Healthy'] ?? false);
$recentZones   = (array) ($templateVars['recentZones'] ?? []);
$bind9Status   = $bind9Healthy ? 'Healthy' : 'Unavailable';

ob_start();
?>
<section class="pbm-page-heading">
    <div>
        <h1>Dashboard</h1>
        <div class="pbm-muted">A clear view of zones, record health, and recent DNS activity.</div>
    </div>
    <a class="pbm-btn pbm-btn-primary" href="/zones/create"><i class="fa-solid fa-plus me-1"></i>New zone</a>
</section>

<section class="pbm-grid pbm-grid-kpi" aria-label="DNS overview">
    <article class="pbm-card">
        <div class="pbm-kpi-label">Authoritative zones</div>
        <div class="pbm-kpi-value"><?= $zoneCount ?></div>
        <div class="pbm-kpi-meta">
            <a href="/zones" class="pbm-muted" style="text-decoration: none;">
                <i class="fa-solid fa-arrow-right me-1"></i>Manage zones
            </a>
        </div>
    </article>
    <article class="pbm-card">
        <div class="pbm-kpi-label">DNS records</div>
        <div class="pbm-kpi-value"><?= number_format($recordCount) ?></div>
        <div class="pbm-kpi-meta">
            <a href="/records" class="pbm-muted" style="text-decoration: none;">
                <i class="fa-solid fa-arrow-right me-1"></i>View records
            </a>
        </div>
    </article>
    <article class="pbm-card">
        <div class="pbm-kpi-label">Validation warnings</div>
        <div class="pbm-kpi-value">00</div>
        <div class="pbm-kpi-meta pbm-success">
            <i class="fa-solid fa-check me-1"></i>All zones valid
        </div>
    </article>
    <article class="pbm-card">
        <div class="pbm-kpi-label">BIND9 health</div>
        <div class="pbm-kpi-value <?= $bind9Healthy ? 'pbm-success' : 'pbm-muted' ?>">
            <?= htmlspecialchars($bind9Status, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
        </div>
        <div class="pbm-kpi-meta pbm-muted">
            <i class="fa-solid fa-clock me-1"></i>Last check: now
        </div>
    </article>
</section>

<section class="pbm-grid pbm-grid-main">
    <article class="pbm-card">
        <div class="pbm-card-head">
            <h2 class="pbm-card-title">Recent zones</h2>
            <a class="pbm-muted" href="/zones">View all &rarr;</a>
        </div>
        <div class="pbm-table-wrap">
            <table class="pbm-table">
                <thead>
                    <tr>
                        <th>Zone</th>
                        <th>Type</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($recentZones === []) : ?>
                    <tr>
                        <td colspan="3" style="text-align: center; padding: 24px;" class="pbm-muted">
                            No zones yet. <a href="/zones/create">Create your first zone &rarr;</a>
                        </td>
                    </tr>
                    <?php else : ?>
                        <?php foreach ($recentZones as $rz) : ?>
                            <?php
                            $rzid   = (int) ($rz['id'] ?? 0);
                            $rzname = (string) ($rz['name'] ?? '');
                            $rztype = (string) ($rz['zone_type'] ?? 'master');
                            $rzstat = (string) ($rz['status'] ?? 'draft');
                            ?>
                        <tr>
                            <td>
                                <a href="/zones/<?= $rzid ?>" style="text-decoration: none; font-weight: 600;">
                                    <code><?= htmlspecialchars($rzname, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></code>
                                </a>
                            </td>
                            <td>
                                <span class="pbm-badge">
                                    <?= htmlspecialchars($rztype, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                </span>
                            </td>
                            <td>
                                <span class="pbm-status <?= $rzstat === 'active' ? 'pbm-success' : 'pbm-muted' ?>">
                                    <?= htmlspecialchars(ucfirst($rzstat), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>

    <aside class="pbm-card">
        <div class="pbm-card-head">
            <h2 class="pbm-card-title">System health</h2>
            <span class="pbm-badge">Live</span>
        </div>
        <p class="pbm-muted">Authoritative service status at a glance.</p>
        <div class="pbm-card" style="padding: 14px; margin-top: 14px; background: var(--pbm-surface-2);">
            <div class="pbm-status <?= $bind9Healthy ? 'pbm-success' : 'pbm-muted' ?>">
                <strong>BIND9 Daemon (named)</strong>
            </div>
            <div class="pbm-muted" style="font-size: .8rem; margin-top: 5px;">
                <?= $bind9Healthy ? 'Running &middot; Authoritative' : 'Status unknown &middot; Check systemctl' ?>
            </div>
        </div>
        <div class="pbm-card" style="padding: 14px; margin-top: 10px; background: var(--pbm-surface-2);">
            <div class="pbm-status pbm-success"><strong>SQLite Database</strong></div>
            <div class="pbm-muted" style="font-size: .8rem; margin-top: 5px;">
                WAL Mode &middot; <?= $zoneCount ?> zones / <?= number_format($recordCount) ?> records
            </div>
        </div>
    </aside>
</section>
<?php
$content = (string) ob_get_clean();
$title   = 'Dashboard - PHP-BindManager';
require_once __DIR__ . '/../layouts/app.php';
