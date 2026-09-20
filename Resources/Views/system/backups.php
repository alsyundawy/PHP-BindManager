<?php

declare(strict_types=1);

/**
 * @var array<int, array<string, mixed>> $backups
 * @var string                           $csrfToken
 * @var string|null                      $flashSuccess
 * @var string|null                      $flashError
 * @var string                           $dbPath
 */
$backups      = $backups ?? [];
$csrfToken    = $csrfToken ?? '';
$flashSuccess = $flashSuccess ?? null;
$flashError   = $flashError ?? null;
$dbPath       = $dbPath ?? '';

$title = 'Backups & Restore — PHP-BindManager';

$fmtSize = static function (int $bytes): string {
    if ($bytes >= 1_048_576) {
        return number_format($bytes / 1_048_576, 1) . ' MB';
    }
    if ($bytes >= 1024) {
        return number_format($bytes / 1024, 1) . ' KB';
    }

    return $bytes . ' B';
};
?>
<?php ob_start(); ?>

<div class="pbm-page-heading">
    <div>
        <h1><i class="fa-solid fa-box-archive me-2"></i>Backups &amp; Restore</h1>
        <p class="pbm-muted">Create and manage SQLite database snapshots.</p>
    </div>
    <form method="post" action="/system/backups">
        <input type="hidden" name="_csrf_token"
               value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
        <input type="hidden" name="_action" value="create">
        <button type="submit" class="pbm-btn">
            <i class="fa-solid fa-plus me-1"></i>New Backup
        </button>
    </form>
</div>

<?php if (isset($flashSuccess) && $flashSuccess !== '') : ?>
    <div class="pbm-alert pbm-alert-success" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i>
        <?= htmlspecialchars($flashSuccess, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
    </div>
<?php endif; ?>
<?php if (isset($flashError) && $flashError !== '') : ?>
    <div class="pbm-alert pbm-alert-error" role="alert">
        <i class="fa-solid fa-circle-xmark me-2"></i>
        <?= htmlspecialchars($flashError, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
    </div>
<?php endif; ?>

<div class="pbm-card" style="margin-bottom:24px;">
    <div class="pbm-card-header">
        <i class="fa-solid fa-database me-2"></i>Database Information
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-top:12px;">
        <div>
            <div class="pbm-muted" style="font-size:.78rem;margin-bottom:4px;">Database Path</div>
            <code style="font-size:.85rem;word-break:break-all;">
                <?= htmlspecialchars($dbPath, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
            </code>
        </div>
        <div>
            <div class="pbm-muted" style="font-size:.78rem;margin-bottom:4px;">Total Backups</div>
            <strong><?= count($backups) ?></strong>
        </div>
    </div>
</div>

<div class="pbm-card">
    <div class="pbm-card-header">
        <i class="fa-solid fa-clock-rotate-left me-2"></i>Backup History
    </div>
    <?php if (empty($backups)) : ?>
        <div class="pbm-empty">
            <i class="fa-solid fa-box-archive"></i>
            <p>No backups yet. Click <strong>New Backup</strong> to create your first snapshot.</p>
        </div>
    <?php else : ?>
        <div class="pbm-table-wrap">
            <table class="pbm-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Type</th>
                        <th>Source</th>
                        <th>Size</th>
                        <th>SHA-256</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($backups as $bk) : ?>
                        <?php
                        $bkType = (string) ($bk['backup_type'] ?? '');
                        $bkSrc  = (string) ($bk['source_name'] ?? '');
                        $bkSize = $fmtSize((int) ($bk['size_bytes'] ?? 0));
                        $bkSha  = substr((string) ($bk['sha256'] ?? ''), 0, 12);
                        $bkTime = (string) ($bk['created_at'] ?? '');
                        $bkId   = (int) ($bk['id'] ?? 0);
                        ?>
                        <tr>
                            <td><?= $bkId ?></td>
                            <td>
                                <span class="pbm-badge pbm-badge-info">
                                    <?= htmlspecialchars($bkType, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($bkSrc, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                            <td><?= $bkSize ?></td>
                            <td>
                                <code style="font-size:.75rem;">
                                    <?= htmlspecialchars($bkSha, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>…
                                </code>
                            </td>
                            <td><?= htmlspecialchars($bkTime, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                            <td>
                                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                    <form method="post" action="/system/backups"
                                          onsubmit="return confirm('Restore live database? Proceed?');">
                                        <input type="hidden" name="_csrf_token"
                                               value="<?= htmlspecialchars(
                                                   $csrfToken,
                                                   ENT_QUOTES | ENT_SUBSTITUTE,
                                                   'UTF-8'
                                               ) ?>">
                                        <input type="hidden" name="_action" value="restore">
                                        <input type="hidden" name="id" value="<?= $bkId ?>">
                                        <button type="submit" class="pbm-btn pbm-btn-sm pbm-btn-warning"
                                                title="Restore this backup">
                                            <i class="fa-solid fa-rotate-left"></i>
                                        </button>
                                    </form>
                                    <form method="post" action="/system/backups"
                                          onsubmit="return confirm('Delete backup permanently?');">
                                        <input type="hidden" name="_csrf_token"
                                               value="<?= htmlspecialchars(
                                                   $csrfToken,
                                                   ENT_QUOTES | ENT_SUBSTITUTE,
                                                   'UTF-8'
                                               ) ?>">
                                        <input type="hidden" name="_action" value="delete">
                                        <input type="hidden" name="id" value="<?= $bkId ?>">
                                        <button type="submit" class="pbm-btn pbm-btn-sm pbm-btn-danger"
                                                title="Delete this backup">
                                            <i class="fa-solid fa-trash"></i>
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
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/app.php';
