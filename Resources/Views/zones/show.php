<?php

declare(strict_types=1);

/** @var array<string, mixed> $zone */
/** @var array<int, array<string, mixed>> $records */
/** @var string $exportText */
/** @var string $csrfToken */
$templateVars = get_defined_vars();
$zone         = (array) ($templateVars['zone'] ?? []);
$records      = (array) ($templateVars['records'] ?? []);
$exportText   = (string) ($templateVars['exportText'] ?? '');
$csrfToken    = (string) ($templateVars['csrfToken'] ?? '');

$zid   = (int) ($zone['id'] ?? 0);
$zname = (string) ($zone['name'] ?? '');
$ztype = (string) ($zone['zone_type'] ?? 'master');
$zstat = (string) ($zone['status'] ?? 'draft');
$zpath = (string) ($zone['file_path'] ?? '');

$typeColors = [
    'A'     => '#3b82f6',
    'AAAA'  => '#8b5cf6',
    'CNAME' => '#06b6d4',
    'MX'    => '#f59e0b',
    'TXT'   => '#6b7280',
    'NS'    => '#10b981',
    'SOA'   => '#ec4899',
    'SRV'   => '#f97316',
    'PTR'   => '#14b8a6',
    'CAA'   => '#84cc16',
];

ob_start();
?>
<section class="pbm-page-heading">
    <div>
        <div style="display: flex; align-items: center; gap: 8px;">
            <h1 style="margin: 0;"><code><?= htmlspecialchars($zname, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></code></h1>
            <span class="pbm-badge"><?= htmlspecialchars($ztype, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
            <span class="pbm-status <?= $zstat === 'active' ? 'pbm-success' : 'pbm-muted' ?>">
                <?= htmlspecialchars(ucfirst($zstat), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
            </span>
        </div>
        <div class="pbm-muted" style="margin-top: 4px;">File: <code><?= htmlspecialchars($zpath, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></code></div>
    </div>
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <a class="pbm-btn" href="/zones"><i class="fa-solid fa-arrow-left me-1"></i>Back</a>
        <a class="pbm-btn pbm-btn-primary" href="/records/create?zone_id=<?= $zid ?>">
            <i class="fa-solid fa-plus me-1"></i>Add record
        </a>
        <form method="post" action="/zones/<?= $zid ?>/deploy" style="margin: 0; display: inline;">
            <input
                type="hidden"
                name="_csrf_token"
                value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
            >
            <button class="pbm-btn" style="color: var(--pbm-success);" type="submit">
                <i class="fa-solid fa-cloud-arrow-up me-1"></i>Deploy
            </button>
        </form>
        <form
            method="post"
            action="/zones/<?= $zid ?>/delete"
            style="margin: 0; display: inline;"
            onsubmit="return confirm('Permanently delete this zone and its records?');"
        >
            <input
                type="hidden"
                name="_csrf_token"
                value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
            >
            <button class="pbm-btn" style="color: var(--pbm-danger, #ef4444);" type="submit">
                <i class="fa-solid fa-trash-can me-1"></i>Delete
            </button>
        </form>
    </div>
</section>

<article class="pbm-card" style="margin-bottom: 20px;">
    <div class="pbm-card-head">
        <h2 class="pbm-card-title">DNS records</h2>
        <span class="pbm-badge"><?= count($records) ?> records</span>
    </div>
    <?php if ($records === []) : ?>
    <div style="padding: 32px 0; text-align: center;" class="pbm-muted">
        <i class="fa-solid fa-list-check" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
        No records yet. <a href="/records/create?zone_id=<?= $zid ?>">Add the first record &rarr;</a>
    </div>
    <?php else : ?>
    <div class="pbm-table-wrap">
        <table class="pbm-table">
            <thead>
                <tr>
                    <th>Owner</th>
                    <th>Type</th>
                    <th>TTL</th>
                    <th>Content</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($records as $record) : ?>
                <?php
                $rid     = (int) ($record['id'] ?? 0);
                $rname   = (string) ($record['name'] ?? '');
                $rtype   = strtoupper((string) ($record['record_type'] ?? 'A'));
                $color   = $typeColors[$rtype] ?? '#6b7280';
                $rttl    = (int) ($record['ttl'] ?? 3600);
                $rval    = (string) ($record['content'] ?? '');
                $priority = $record['priority'] ?? null;
                ?>
                <tr>
                    <td><code><?= htmlspecialchars($rname, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></code></td>
                    <td>
                        <span
                            class="pbm-badge"
                            style="background: <?= htmlspecialchars($color, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>22;
                                   color: <?= htmlspecialchars($color, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>;
                                   border-color: <?= htmlspecialchars($color, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>44;"
                        ><?= htmlspecialchars($rtype, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                    </td>
                    <td><?= $rttl ?>s</td>
                    <td>
                        <code style="font-size: .8rem; word-break: break-all;">
                            <?= $priority !== null ? (int) $priority . ' ' : '' ?>
                            <?= htmlspecialchars($rval, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                        </code>
                    </td>
                    <td>
                        <form
                            method="post"
                            action="/records/<?= $rid ?>/delete"
                            style="margin: 0;"
                            onsubmit="return confirm('Delete this record?');"
                        >
                            <input
                                type="hidden"
                                name="_csrf_token"
                                value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                            >
                            <button
                                class="pbm-btn"
                                style="padding: 2px 7px; font-size: .75rem; color: var(--pbm-danger, #ef4444);"
                                type="submit"
                                title="Delete record"
                            >
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</article>

<article class="pbm-card">
    <div class="pbm-card-head">
        <h2 class="pbm-card-title">BIND9 zone file preview</h2>
        <span class="pbm-badge">RFC 1035 format</span>
    </div>
    <pre style="overflow-x: auto; background: var(--pbm-surface-2); padding: 16px; border-radius: 8px; color: var(--pbm-text); font-family: monospace; font-size: .85rem;"><?= htmlspecialchars($exportText, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></pre>
</article>
<?php
$content = (string) ob_get_clean();
$title   = "Zone {$zname} - PHP-BindManager";
require_once __DIR__ . '/../layouts/app.php';
