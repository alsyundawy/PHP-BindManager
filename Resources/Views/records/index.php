<?php

declare(strict_types=1);

ob_start();
?>
<section class="pbm-page-heading">
    <div>
        <h1>DNS records</h1>
        <div class="pbm-muted">Review and manage records with typed validation before writing to BIND9.</div>
    </div>
    <a class="pbm-btn pbm-btn-primary" href="/records/create"><i class="fa-solid fa-plus me-1"></i>New record</a>
</section>

<article class="pbm-card">
    <div class="pbm-card-head">
        <h2 class="pbm-card-title">Recent records</h2>
        <span class="pbm-badge">Validated</span>
    </div>
    <div class="pbm-table-wrap">
        <table class="pbm-table">
            <thead>
                <tr>
                    <th>Owner</th>
                    <th>Type</th>
                    <th>TTL</th>
                    <th>Content</th>
                    <th>State</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>www</code></td>
                    <td><span class="badge bg-info text-dark">A</span></td>
                    <td>3600</td>
                    <td><code>192.0.2.10</code></td>
                    <td><span class="pbm-status pbm-success">Valid</span></td>
                </tr>
                <tr>
                    <td><code>@</code></td>
                    <td><span class="badge bg-warning text-dark">MX</span></td>
                    <td>3600</td>
                    <td><code>10 mail.example.com.</code></td>
                    <td><span class="pbm-status pbm-success">Valid</span></td>
                </tr>
                <tr>
                    <td><code>_dmarc</code></td>
                    <td><span class="badge bg-secondary">TXT</span></td>
                    <td>3600</td>
                    <td><code>v=DMARC1; p=reject; rua=mailto:dmarc@example.com</code></td>
                    <td><span class="pbm-status pbm-success">Valid</span></td>
                </tr>
            </tbody>
        </table>
    </div>
</article>
<?php
$content = (string) ob_get_clean();
$title = 'DNS Records - PHP-BindManager';
require_once __DIR__ . '/../layouts/app.php';
