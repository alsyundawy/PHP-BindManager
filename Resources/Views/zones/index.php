<?php

declare(strict_types=1);

/** @var array<int, array<string, mixed>> $zones */
/** @var array<int, int> $recordCounts */
/** @var string $csrfToken */
$templateVars = get_defined_vars();
$zones        = (array) ($templateVars['zones'] ?? []);
$recordCounts = (array) ($templateVars['recordCounts'] ?? []);
$csrfToken    = (string) ($templateVars['csrfToken'] ?? '');

ob_start();
?>
<section class="pbm-page-heading">
    <div>
        <h1>Zones</h1>
        <div class="pbm-muted">Manage forward, reverse, master, and slave authoritative zones.</div>
    </div>
    <a class="pbm-btn pbm-btn-primary" href="/zones/create">
        <i class="fa-solid fa-plus me-1"></i>New zone
    </a>
</section>

<article class="pbm-card">
    <div class="pbm-card-head">
        <h2 class="pbm-card-title">Authoritative zones</h2>
        <span class="pbm-badge"><?= count($zones) ?> total</span>
    </div>
    <?php if ($zones === []) : ?>
    <div style="padding: 40px 0; text-align: center;" class="pbm-muted">
        <i class="fa-solid fa-layer-group" style="font-size: 2rem; margin-bottom: 12px; display: block;"></i>
        No zones yet. <a href="/zones/create">Create your first zone &rarr;</a>
    </div>
    <?php else : ?>
    <div class="pbm-table-wrap">
        <table class="pbm-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Records</th>
                    <th>File path</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($zones as $zone) : ?>
                <?php
                $zid   = (int) ($zone['id'] ?? 0);
                $zname = (string) ($zone['name'] ?? '');
                $ztype = (string) ($zone['zone_type'] ?? 'master');
                $zpath = (string) ($zone['file_path'] ?? '');
                $zstat = (string) ($zone['status'] ?? 'draft');
                $rcnt  = (int) ($recordCounts[$zid] ?? 0);
                ?>
                <tr>
                    <td>
                        <a href="/zones/<?= $zid ?>" style="font-weight: 600; text-decoration: none;">
                            <code><?= htmlspecialchars($zname, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></code>
                        </a>
                    </td>
                    <td>
                        <span class="pbm-badge">
                            <?= htmlspecialchars($ztype, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                        </span>
                    </td>
                    <td>
                        <a href="/records?zone_id=<?= $zid ?>" class="pbm-muted">
                            <span class="pbm-badge"><?= $rcnt ?></span>
                        </a>
                    </td>
                    <td>
                        <code style="font-size: .8rem;">
                            <?= htmlspecialchars($zpath, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                        </code>
                    </td>
                    <td>
                        <span class="pbm-status <?= $zstat === 'active' ? 'pbm-success' : 'pbm-muted' ?>">
                            <?= htmlspecialchars(ucfirst($zstat), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                        </span>
                    </td>
                    <td>
                        <div style="display: flex; gap: 6px; align-items: center;">
                            <a
                                class="pbm-btn"
                                style="padding: 3px 8px; font-size: .78rem;"
                                href="/zones/<?= $zid ?>"
                                title="View zone details"
                            >
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <a
                                class="pbm-btn"
                                style="padding: 3px 8px; font-size: .78rem;"
                                href="/records?zone_id=<?= $zid ?>"
                                title="Manage records"
                            >
                                <i class="fa-solid fa-list-check"></i>
                            </a>
                            <form method="post" action="/zones/<?= $zid ?>/deploy" style="margin: 0; display: inline;">
                                <input
                                    type="hidden"
                                    name="_csrf_token"
                                    value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                >
                                <button
                                    class="pbm-btn"
                                    style="padding: 3px 8px; font-size: .78rem; color: var(--pbm-success);"
                                    type="submit"
                                    title="Deploy zone to BIND9"
                                >
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                </button>
                            </form>
                            <form
                                method="post"
                                action="/zones/<?= $zid ?>/delete"
                                style="margin: 0; display: inline;"
                                onsubmit="return confirm('Are you sure you want to delete this zone?');"
                            >
                                <input
                                    type="hidden"
                                    name="_csrf_token"
                                    value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                >
                                <button
                                    class="pbm-btn"
                                    style="padding: 3px 8px; font-size: .78rem; color: var(--pbm-danger, #ef4444);"
                                    type="submit"
                                    title="Delete zone"
                                >
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </form>
                        </div>
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
$title   = 'Zones - PHP-BindManager';
require_once __DIR__ . '/../layouts/app.php';
