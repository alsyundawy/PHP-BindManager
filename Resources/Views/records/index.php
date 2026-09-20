<?php

declare(strict_types=1);

/** @var array<int, array<string, mixed>> $records */
/** @var array<int, array<string, mixed>> $zones */
/** @var int $zoneId */
/** @var string $csrfToken */
$templateVars = get_defined_vars();
$records      = (array) ($templateVars['records'] ?? []);
$zones        = (array) ($templateVars['zones'] ?? []);
$zoneId       = (int) ($templateVars['zoneId'] ?? 0);
$csrfToken    = (string) ($templateVars['csrfToken'] ?? '');

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

$selectedZoneName = '';
foreach ($zones as $z) {
    if ((int) ($z['id'] ?? 0) === $zoneId) {
        $selectedZoneName = (string) ($z['name'] ?? '');
        break;
    }
}

ob_start();
?>
<section class="pbm-page-heading">
    <div>
        <h1>DNS records</h1>
        <div class="pbm-muted">Review and manage records with typed validation before writing to BIND9.</div>
    </div>
    <div style="display: flex; gap: 8px;">
        <?php if ($zoneId > 0) : ?>
            <a class="pbm-btn" href="/zones/<?= $zoneId ?>"><i class="fa-solid fa-eye me-1"></i>View zone</a>
        <?php endif; ?>
        <a class="pbm-btn pbm-btn-primary" href="/records/create<?= $zoneId > 0 ? '?zone_id=' . $zoneId : '' ?>">
            <i class="fa-solid fa-plus me-1"></i>New record
        </a>
    </div>
</section>

<div style="margin-bottom: 16px; display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
    <label for="zone-filter" style="font-weight: 600; font-size: .9rem;">Filter by zone:</label>
    <select
        id="zone-filter"
        class="form-select"
        style="max-width: 320px; padding: 6px 12px; border-radius: 8px;
               background: var(--pbm-surface-2); color: var(--pbm-text); border: 1px solid var(--pbm-border);"
        onchange="location.href = this.value ? '/records?zone_id=' + this.value : '/records';"
    >
        <option value="">All authoritative zones</option>
        <?php foreach ($zones as $z) : ?>
            <?php $zid = (int) ($z['id'] ?? 0); ?>
            <option value="<?= $zid ?>" <?= $zid === $zoneId ? 'selected' : '' ?>>
                <?= htmlspecialchars((string) ($z['name'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<article class="pbm-card">
    <div class="pbm-card-head">
        <h2 class="pbm-card-title">
            <?php if ($selectedZoneName !== '') : ?>
                Records for <?= htmlspecialchars($selectedZoneName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
            <?php else : ?>
                All DNS records
            <?php endif; ?>
        </h2>
        <span class="pbm-badge"><?= count($records) ?> records</span>
    </div>
    <?php if ($records === []) : ?>
    <div style="padding: 40px 0; text-align: center;" class="pbm-muted">
        <i class="fa-solid fa-list-check" style="font-size: 2rem; margin-bottom: 12px; display: block;"></i>
        <?php if ($zoneId > 0) : ?>
            No records in this zone yet. <a href="/records/create?zone_id=<?= $zoneId ?>">Add first record &rarr;</a>
        <?php else : ?>
            No DNS records found. Select or create a zone first.
        <?php endif; ?>
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
                    $rid      = (int) ($record['id'] ?? 0);
                    $type     = strtoupper((string) ($record['record_type'] ?? 'A'));
                    $color    = $typeColors[$type] ?? '#6b7280';
                    $priority = $record['priority'] ?? null;
                    $rzid     = (int) ($record['zone_id'] ?? $zoneId);
                    ?>
                <tr>
                    <td>
                        <code><?=
                            htmlspecialchars((string) ($record['name'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                        ?></code>
                    </td>
                    <td>
                        <span
                            class="pbm-badge"
                            style="background: <?= htmlspecialchars($color, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>22;
                                   color: <?= htmlspecialchars($color, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>;
                                   border-color: <?=
                                       htmlspecialchars($color, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                                    ?>44;"
                        ><?= htmlspecialchars($type, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                    </td>
                    <td><?= (int) ($record['ttl'] ?? 3600) ?>s</td>
                    <td>
                        <code style="font-size: .8rem; word-break: break-all;">
                            <?= $priority !== null ? (int) $priority . ' ' : '' ?>
                            <?=
                            htmlspecialchars(
                                (string) ($record['content'] ?? ''),
                                ENT_QUOTES | ENT_SUBSTITUTE,
                                'UTF-8'
                            )
                            ?>
                        </code>
                    </td>
                    <td>
                        <form
                            method="post"
                            action="/records/<?= $rid ?>/delete<?= $rzid > 0 ? '?zone_id=' . $rzid : '' ?>"
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
<?php
$content = (string) ob_get_clean();
$title   = 'DNS Records - PHP-BindManager';
require_once __DIR__ . '/../layouts/app.php';
