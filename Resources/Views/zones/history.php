<?php

declare(strict_types=1);

/**
 * @var array<string, mixed>             $zone
 * @var array<int, array<string, mixed>> $history
 * @var string                           $csrfToken
 * @var string|null                      $flashSuccess
 * @var string|null                      $flashError
 */
$templateVars = get_defined_vars();
$zone         = (array) ($templateVars['zone'] ?? []);
$history      = (array) ($templateVars['history'] ?? []);
$csrfToken    = (string) ($templateVars['csrfToken'] ?? '');
$flashSuccess = isset($templateVars['flashSuccess']) ? (string) $templateVars['flashSuccess'] : null;
$flashError   = isset($templateVars['flashError']) ? (string) $templateVars['flashError'] : null;
$csrfVal      = htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

$zoneName = (string) ($zone['name'] ?? 'Unknown');
$zoneId   = (int) ($zone['id'] ?? 0);
$title    = "Revision History: {$zoneName} — PHP-BindManager";
?>
<?php ob_start(); ?>

<div class="pbm-page-heading">
    <div>
        <h1>
            <i class="fa-solid fa-clock-rotate-left me-2"></i>
            Revisions: <?= htmlspecialchars($zoneName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
        </h1>
        <p class="pbm-muted">Historical snapshots, configuration diffs, and point-in-time rollback.</p>
    </div>
    <a href="/zones/<?= $zoneId ?>" class="pbm-btn pbm-btn-secondary">
        <i class="fa-solid fa-arrow-left me-1"></i>Back to Zone
    </a>
</div>

<?php if ($flashSuccess !== null && $flashSuccess !== '') : ?>
    <div class="pbm-alert pbm-alert-success" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i>
        <?= htmlspecialchars($flashSuccess, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
    </div>
<?php endif; ?>
<?php if ($flashError !== null && $flashError !== '') : ?>
    <div class="pbm-alert pbm-alert-error" role="alert">
        <i class="fa-solid fa-circle-xmark me-2"></i>
        <?= htmlspecialchars($flashError, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
    </div>
<?php endif; ?>

<div class="pbm-card">
    <div class="pbm-card-header"><i class="fa-solid fa-list-check me-2"></i>Snapshot Revisions</div>
    <?php if (empty($history)) : ?>
        <div class="pbm-empty">
            <i class="fa-solid fa-clock-rotate-left"></i>
            <p>No historical revisions recorded for this zone yet. Changes will create automatic snapshots.</p>
        </div>
    <?php else : ?>
        <div class="pbm-table-wrap">
            <table class="pbm-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Time</th>
                        <th>Serial</th>
                        <th>Author</th>
                        <th>Summary</th>
                        <th>Zone Diff / Content</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $h) : ?>
                        <?php
                        $hid       = (int) ($h['id'] ?? 0);
                        $hSerial   = (int) ($h['serial'] ?? 0);
                        $hTime     = (string) ($h['created_at'] ?? '');
                        $hUser     = (string) ($h['username'] ?? 'System');
                        $hSummary  = (string) ($h['change_summary'] ?? 'Zone update');
                        $hContent  = (string) ($h['zone_content'] ?? '');
                        ?>
                        <tr>
                            <td><?= $hid ?></td>
                            <td style="font-size:.82rem;white-space:nowrap;">
                                <?= htmlspecialchars($hTime, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                            </td>
                            <td><code><?= $hSerial ?></code></td>
                            <td><?= htmlspecialchars($hUser, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($hSummary, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                            <td style="max-width:240px;">
                                <details>
                                    <summary style="cursor:pointer;font-size:.82rem;color:var(--pbm-primary);">
                                        View Snapshot (<?= strlen($hContent) ?> bytes)
                                    </summary>
                                    <pre class="pbm-diff-code" style="max-height:160px;overflow-y:auto;margin-top:6px;"
><?= htmlspecialchars($hContent, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></pre>
                                </details>
                            </td>
                            <td>
                                <form method="post" action="/zones/<?= $zoneId ?>/rollback"
                                      onsubmit="return confirm('Rollback zone to revision #<?= $hid ?>?');">
                                    <input type="hidden" name="_csrf_token" value="<?= $csrfVal ?>">
                                    <input type="hidden" name="history_id" value="<?= $hid ?>">
                                    <button type="submit" class="pbm-btn pbm-btn-sm pbm-btn-warning">
                                        <i class="fa-solid fa-rotate-left me-1"></i>Rollback
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/app.php';
