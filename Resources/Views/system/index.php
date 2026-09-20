<?php

declare(strict_types=1);

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
        <div class="pbm-kpi-value">24.8 MB</div>
        <div class="pbm-kpi-meta pbm-success"><i class="fa-solid fa-database me-1"></i>WAL enabled</div>
    </article>
    <article class="pbm-card">
        <div class="pbm-kpi-label">Backups</div>
        <div class="pbm-kpi-value">42</div>
        <div class="pbm-kpi-meta pbm-muted">Last: 8 min ago</div>
    </article>
    <article class="pbm-card">
        <div class="pbm-kpi-label">Audit events</div>
        <div class="pbm-kpi-value">1,284</div>
        <div class="pbm-kpi-meta pbm-muted">Retention active</div>
    </article>
    <article class="pbm-card">
        <div class="pbm-kpi-label">Notifications</div>
        <div class="pbm-kpi-value">00</div>
        <div class="pbm-kpi-meta pbm-success">All clear</div>
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
require __DIR__ . '/../layouts/app.php';