<?php

declare(strict_types=1);

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
        <div class="pbm-kpi-value">128</div>
        <div class="pbm-kpi-meta pbm-success"><i class="fa-solid fa-arrow-up me-1"></i>12 this month</div>
    </article>
    <article class="pbm-card">
        <div class="pbm-kpi-label">DNS records</div>
        <div class="pbm-kpi-value">4,892</div>
        <div class="pbm-kpi-meta pbm-success"><i class="fa-solid fa-arrow-up me-1"></i>4.8% this month</div>
    </article>
    <article class="pbm-card">
        <div class="pbm-kpi-label">Validation warnings</div>
        <div class="pbm-kpi-value">00</div>
        <div class="pbm-kpi-meta pbm-success"><i class="fa-solid fa-check me-1"></i>All zones valid</div>
    </article>
    <article class="pbm-card">
        <div class="pbm-kpi-label">BIND9 health</div>
        <div class="pbm-kpi-value pbm-success">Healthy</div>
        <div class="pbm-kpi-meta pbm-muted">Last check 2 min ago</div>
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
                        <th>Records</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>example.com</td>
                        <td><span class="pbm-badge">Master</span></td>
                        <td>86</td>
                        <td><span class="pbm-status pbm-success">Synced</span></td>
                    </tr>
                    <tr>
                        <td>10.in-addr.arpa</td>
                        <td><span class="pbm-badge">Reverse</span></td>
                        <td>254</td>
                        <td><span class="pbm-status pbm-success">Synced</span></td>
                    </tr>
                    <tr>
                        <td>mail.example.org</td>
                        <td><span class="pbm-badge">Master</span></td>
                        <td>42</td>
                        <td><span class="pbm-status pbm-success">Synced</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </article>

    <aside class="pbm-card">
        <div class="pbm-card-head">
            <h2 class="pbm-card-title">System health</h2>
            <span class="pbm-badge">Live</span>
        </div>
        <p class="pbm-muted">All authoritative services are responding normally.</p>
        <div class="pbm-card" style="padding: 14px; margin-top: 14px; background: var(--pbm-surface-2);">
            <div class="pbm-status pbm-success"><strong>BIND9 Daemon (named)</strong></div>
            <div class="pbm-muted" style="font-size: .8rem; margin-top: 5px;">Running &middot; 99.99% uptime</div>
        </div>
        <div class="pbm-card" style="padding: 14px; margin-top: 10px; background: var(--pbm-surface-2);">
            <div class="pbm-status pbm-success"><strong>SQLite Database</strong></div>
            <div class="pbm-muted" style="font-size: .8rem; margin-top: 5px;">WAL Mode &middot; Ready</div>
        </div>
    </aside>
</section>
<?php
$content = (string) ob_get_clean();
$title = 'Dashboard - PHP-BindManager';
require_once __DIR__ . '/../layouts/app.php';
