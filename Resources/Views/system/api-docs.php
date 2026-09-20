<?php

declare(strict_types=1);

ob_start();
?>
<section class="pbm-page-heading">
    <div>
        <h1>API documentation</h1>
        <div class="pbm-muted">Versioned REST API for zones, records, system health, and automation.</div>
    </div>
    <span class="pbm-badge">OpenAPI-ready</span>
</section>

<article class="pbm-card">
    <h2 class="pbm-card-title">Authentication</h2>
    <pre
        style="overflow-x: auto; background: var(--pbm-surface-2);
               padding: 16px; border-radius: 12px; color: var(--pbm-text);"
    >Authorization: Bearer pbm_your_token_here
GET /api/v1/zones</pre>

    <h2 class="pbm-card-title" style="margin-top: 24px;">Endpoints</h2>
    <div class="pbm-table-wrap">
        <table class="pbm-table">
            <thead>
                <tr>
                    <th>Method</th>
                    <th>Endpoint</th>
                    <th>Scope</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><span class="badge bg-primary">GET</span></td>
                    <td><code>/api/v1/zones</code></td>
                    <td><code>zones:read</code></td>
                </tr>
                <tr>
                    <td><span class="badge bg-success">POST</span></td>
                    <td><code>/api/v1/zones</code></td>
                    <td><code>zones:write</code></td>
                </tr>
                <tr>
                    <td><span class="badge bg-primary">GET</span></td>
                    <td><code>/api/v1/system/health</code></td>
                    <td><code>system:read</code></td>
                </tr>
            </tbody>
        </table>
    </div>
</article>
<?php
$content = (string) ob_get_clean();
$title = 'API Docs - PHP-BindManager';
require_once __DIR__ . '/../layouts/app.php';
