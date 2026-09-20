<?php

declare(strict_types=1);

/** @var array<int, array<string, mixed>> $logs */
/** @var int $total */
$templateVars = get_defined_vars();
$logs         = (array) ($templateVars['logs'] ?? []);
$total        = (int) ($templateVars['total'] ?? 0);

$title = 'Audit Trail — PHP-BindManager';

$actionBadge = [
    'CREATE' => 'pbm-badge-success',
    'UPDATE' => 'pbm-badge-warning',
    'DELETE' => 'pbm-badge-danger',
    'LOGIN'  => 'pbm-badge-info',
    'LOGOUT' => 'pbm-badge-secondary',
];
?>
<?php ob_start(); ?>

<div class="pbm-page-heading">
    <div>
        <h1><i class="fa-solid fa-clipboard-check me-2"></i>Audit Trail</h1>
        <p class="pbm-muted">Immutable record of all data-change events for compliance.</p>
    </div>
    <span class="pbm-badge pbm-badge-secondary"><?= $total ?> events</span>
</div>

<div class="pbm-card">
    <?php if (empty($logs)) : ?>
        <div class="pbm-empty">
            <i class="fa-solid fa-clipboard-check"></i>
            <p>No audit events recorded yet.</p>
        </div>
    <?php else : ?>
        <div class="pbm-table-wrap">
            <table class="pbm-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Time</th>
                        <th>Action</th>
                        <th>Entity</th>
                        <th>Entity ID</th>
                        <th>User</th>
                        <th>IP</th>
                        <th>Changes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log) : ?>
                        <?php
                        $act       = strtoupper((string) ($log['action'] ?? ''));
                        $timeStr   = (string) ($log['created_at'] ?? '');
                        $entity    = (string) ($log['entity_type'] ?? '');
                        $entityId  = (string) ($log['entity_id'] ?? '—');
                        $uname     = (string) ($log['username'] ?? '');
                        $userStr   = $uname !== '' ? $uname : '—';
                        $ipStr     = (string) ($log['ip_address'] ?? '');
                        $oldVal    = (string) ($log['old_value'] ?? '');
                        $newVal    = (string) ($log['new_value'] ?? '');
                        $oldEsc    = htmlspecialchars($oldVal, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                        $newEsc    = htmlspecialchars($newVal, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                        $hasChange = ($oldVal !== '' || $newVal !== '');
                        ?>
                        <tr>
                            <td><?= (int) ($log['id'] ?? 0) ?></td>
                            <td style="white-space:nowrap;font-size:.82rem;">
                                <?= htmlspecialchars($timeStr, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                            </td>
                            <td>
                                <span class="pbm-badge <?= $actionBadge[$act] ?? 'pbm-badge-secondary' ?>">
                                    <?= htmlspecialchars($act, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                </span>
                            </td>
                            <td>
                                <code style="font-size:.82rem;">
                                    <?= htmlspecialchars($entity, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                </code>
                            </td>
                            <td>
                                <?= htmlspecialchars($entityId, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($userStr, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                            </td>
                            <td style="font-size:.82rem;">
                                <?= htmlspecialchars($ipStr, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                            </td>
                            <td>
                                <?php if ($hasChange) : ?>
                                    <details style="cursor:pointer;">
                                        <summary style="font-size:.78rem;color:var(--pbm-muted);">View diff</summary>
                                        <div style="margin-top:6px;font-size:.78rem;">
                                            <?php if ($oldVal !== '') : ?>
                                                <div class="pbm-muted">Before:</div>
                                                <pre class="pbm-diff-code"><?= $oldEsc ?></pre>
                                            <?php endif; ?>
                                            <?php if ($newVal !== '') : ?>
                                                <div class="pbm-muted">After:</div>
                                                <pre class="pbm-diff-code"><?= $newEsc ?></pre>
                                            <?php endif; ?>
                                        </div>
                                    </details>
                                <?php else : ?>
                                    <span class="pbm-muted">—</span>
                                <?php endif; ?>
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
