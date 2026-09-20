<?php

declare(strict_types=1);

/**
 * @var array<int, array<string, mixed>> $logs
 * @var string                           $category
 * @var int                              $total
 */
if (! isset($logs) || ! is_array($logs)) {
    $logs = [];
}
if (! isset($category) || ! is_string($category)) {
    $category = '';
}
if (! isset($total) || ! is_int($total)) {
    $total = 0;
}

$title = 'Activity Log — PHP-BindManager';

$categories = ['auth', 'zone', 'record', 'user', 'system', 'api', 'backup'];
$badgeMap   = [
    'auth'   => 'pbm-badge-info',
    'zone'   => 'pbm-badge-success',
    'record' => 'pbm-badge-primary',
    'user'   => 'pbm-badge-warning',
    'system' => 'pbm-badge-secondary',
    'api'    => 'pbm-badge-info',
    'backup' => 'pbm-badge-warning',
];
?>
<?php ob_start(); ?>

<div class="pbm-page-heading">
    <div>
        <h1><i class="fa-solid fa-clock-rotate-left me-2"></i>Activity Log</h1>
        <p class="pbm-muted">Operational events across all system components.</p>
    </div>
    <span class="pbm-badge pbm-badge-secondary"><?= $total ?> entries</span>
</div>

<div class="pbm-card" style="margin-bottom:20px;">
    <form method="get" action="/system/activity"
          style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
        <div class="pbm-form-group" style="margin:0;flex:1;min-width:160px;">
            <label class="pbm-label" for="cat-filter">Category</label>
            <select id="cat-filter" name="category" class="pbm-input" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat) : ?>
                    <option value="<?= htmlspecialchars($cat, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                        <?= $category === $cat ? 'selected' : '' ?>>
                        <?= htmlspecialchars(ucfirst($cat), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="pbm-btn">
            <i class="fa-solid fa-filter me-1"></i>Filter
        </button>
        <?php if ($category !== '') : ?>
            <a href="/system/activity" class="pbm-btn pbm-btn-secondary">
                <i class="fa-solid fa-xmark me-1"></i>Clear
            </a>
        <?php endif; ?>
    </form>
</div>

<div class="pbm-card">
    <?php if (empty($logs)) : ?>
        <div class="pbm-empty">
            <i class="fa-solid fa-clock-rotate-left"></i>
            <p>No activity recorded yet.</p>
        </div>
    <?php else : ?>
        <div class="pbm-table-wrap">
            <table class="pbm-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Time</th>
                        <th>Category</th>
                        <th>Action</th>
                        <th>User</th>
                        <th>Message</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log) : ?>
                        <?php
                        $actCat    = (string) ($log['category'] ?? 'system');
                        $createdAt = (string) ($log['created_at'] ?? '');
                        $actionStr = (string) ($log['action'] ?? '');
                        $userStr   = (string) ($log['username'] ?? '—');
                        $msgStr    = (string) ($log['message'] ?? '');
                        $ipAddr    = (string) ($log['ip_address'] ?? '');
                        ?>
                        <tr>
                            <td><?= (int) ($log['id'] ?? 0) ?></td>
                            <td style="white-space:nowrap;font-size:.82rem;">
                                <?= htmlspecialchars($createdAt, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                            </td>
                            <td>
                                <span class="pbm-badge <?= $badgeMap[$actCat] ?? 'pbm-badge-secondary' ?>">
                                    <?= htmlspecialchars($actCat, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                </span>
                            </td>
                            <td>
                                <code style="font-size:.82rem;">
                                    <?= htmlspecialchars($actionStr, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                </code>
                            </td>
                            <td>
                                <?= htmlspecialchars($userStr, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                            </td>
                            <td style="max-width:280px;overflow-wrap:break-word;word-break:break-word;">
                                <?= htmlspecialchars($msgStr, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                            </td>
                            <td style="font-size:.82rem;">
                                <?= htmlspecialchars($ipAddr, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
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
