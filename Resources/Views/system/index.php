<?php

declare(strict_types=1);

/** @var string $dbSize */
/** @var int $zoneCount */
/** @var int $recordCount */
/** @var bool $bind9Healthy */
/** @var string $phpVersion */
/** @var string $zonesDir */
$templateVars = get_defined_vars();
$dbSize       = (string) ($templateVars['dbSize'] ?? '24.8 MB');
$zoneCount    = (int) ($templateVars['zoneCount'] ?? 0);
$recordCount  = (int) ($templateVars['recordCount'] ?? 0);
$bind9Healthy = (bool) ($templateVars['bind9Healthy'] ?? false);
$phpVersion   = (string) ($templateVars['phpVersion'] ?? PHP_VERSION);
$zonesDir     = (string) ($templateVars['zonesDir'] ?? '/etc/bind/zones');

ob_start();
?>
<section class="pbm-page-heading">
    <div>
        <h1>System operations</h1>
        <div class="pbm-muted">Backup, database optimization, activity, audit, and service status.</div>
    </div>
    <span class="pbm-badge"><i class="fa-solid fa-shield-halved me-1"></i>Protected</span>
</section>

<section class="pbm-grid pbm-grid-kpi">
    <article class="pbm-card">
        <div class="pbm-kpi-label">Database size</div>
        <div class="pbm-kpi-value"><?= htmlspecialchars($dbSize, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></div>
        <div class="pbm-kpi-meta pbm-success"><i class="fa-solid fa-database me-1"></i>SQLite WAL</div>
    </article>
    <article class="pbm-card">
        <div class="pbm-kpi-label">Authoritative zones</div>
        <div class="pbm-kpi-value"><?= $zoneCount ?></div>
        <div class="pbm-kpi-meta pbm-muted"><?= number_format($recordCount) ?> total records</div>
    </article>
    <article class="pbm-card">
        <div class="pbm-kpi-label">PHP Environment</div>
        <div class="pbm-kpi-value" style="font-size: 1.25rem;">
            v<?= htmlspecialchars($phpVersion, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
        </div>
        <div class="pbm-kpi-meta pbm-success"><i class="fa-solid fa-shield me-1"></i>Hardened</div>
    </article>
    <article class="pbm-card">
        <div class="pbm-kpi-label">BIND9 Daemon</div>
        <div class="pbm-kpi-value <?= $bind9Healthy ? 'pbm-success' : 'pbm-muted' ?>" style="font-size: 1.25rem;">
            <?= $bind9Healthy ? 'Active' : 'Standby / Inactive' ?>
        </div>
        <div class="pbm-kpi-meta pbm-muted">
            Directory: <code><?= htmlspecialchars($zonesDir, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></code>
        </div>
    </article>
</section>

<section class="pbm-grid pbm-grid-main" style="margin-top: 16px;">
    <article class="pbm-card">
        <div class="pbm-card-head">
            <h2 class="pbm-card-title">Recent activity</h2>
            <a class="pbm-muted" href="/audit">View audit trail &rarr;</a>
        </div>
        <div class="pbm-table-wrap">
            <table class="pbm-table">
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>Actor</th>
                        <th>Result</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Zone export</td>
                        <td>admin</td>
                        <td><span class="pbm-status pbm-success">Success</span></td>
                        <td>8 min ago</td>
                    </tr>
                    <tr>
                        <td>Database optimize</td>
                        <td>admin</td>
                        <td><span class="pbm-status pbm-success">Success</span></td>
                        <td>1 hour ago</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </article>

    <aside class="pbm-card">
        <div class="pbm-card-head">
            <h2 class="pbm-card-title">API access</h2>
            <span class="pbm-badge">v1</span>
        </div>
        <p class="pbm-muted">Use scoped bearer tokens for automation and CI/CD integrations.</p>
        <a class="pbm-btn pbm-btn-primary" href="/api/docs"><i class="fa-solid fa-book-open me-1"></i>Open API docs</a>
    </aside>
</section>
<?php
$content = (string) ob_get_clean();
$title = 'System - PHP-BindManager';
require_once __DIR__ . '/../layouts/app.php';
