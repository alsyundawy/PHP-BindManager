<?php

declare(strict_types=1);

ob_start();
?>
<section class="pbm-page-heading">
    <div>
        <h1>Zones</h1>
        <div class="pbm-muted">Manage forward, reverse, master, and slave authoritative zones.</div>
    </div>
    <a class="pbm-btn pbm-btn-primary" href="/zones/create"><i class="fa-solid fa-plus me-1"></i>New zone</a>
</section>

<article class="pbm-card">
    <div class="pbm-card-head">
        <h2 class="pbm-card-title">Authoritative zones</h2>
        <span class="pbm-badge">128 total</span>
    </div>
    <div class="pbm-table-wrap">
        <table class="pbm-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>View</th>
                    <th>DNSSEC</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>example.com.</code></td>
                    <td><span class="badge bg-primary">Master</span></td>
                    <td>public</td>
                    <td><span class="badge bg-success">Enabled</span></td>
                    <td><span class="pbm-status pbm-success">Active</span></td>
                </tr>
                <tr>
                    <td><code>10.in-addr.arpa.</code></td>
                    <td><span class="badge bg-secondary">Reverse</span></td>
                    <td>internal</td>
                    <td><span class="badge bg-secondary">Disabled</span></td>
                    <td><span class="pbm-status pbm-success">Active</span></td>
                </tr>
            </tbody>
        </table>
    </div>
</article>
<?php
$content = (string) ob_get_clean();
$title = 'Zones - PHP-BindManager';
require __DIR__ . '/../layouts/app.php';