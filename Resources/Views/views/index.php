<?php

declare(strict_types=1);

/**
 * @var array<int, array<string, mixed>> $views
 * @var string                           $csrfToken
 * @var string|null                      $flashSuccess
 * @var string|null                      $flashError
 */
$templateVars = get_defined_vars();
$views        = (array) ($templateVars['views'] ?? []);
$csrfToken    = (string) ($templateVars['csrfToken'] ?? '');
$flashSuccess = isset($templateVars['flashSuccess']) ? (string) $templateVars['flashSuccess'] : null;
$flashError   = isset($templateVars['flashError']) ? (string) $templateVars['flashError'] : null;
$csrfVal      = htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

$title = 'Split-Horizon Views — PHP-BindManager';
?>
<?php ob_start(); ?>

<div class="pbm-page-heading">
    <div>
        <h1><i class="fa-solid fa-diagram-project me-2"></i>Split-Horizon DNS Views</h1>
        <p class="pbm-muted">
            Define named BIND9 <code>view</code> blocks to serve different data to different clients.
        </p>
    </div>
    <button type="button" class="pbm-btn"
            onclick="document.getElementById('view-form').style.display='block';this.style.display='none';">
        <i class="fa-solid fa-plus me-1"></i>New View
    </button>
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

<div id="view-form" class="pbm-card" style="margin-bottom:24px;display:none;">
    <div class="pbm-card-header"><i class="fa-solid fa-plus me-2"></i>New DNS View</div>
    <form method="post" action="/views" style="margin-top:16px;">
        <input type="hidden" name="_csrf_token" value="<?= $csrfVal ?>">
        <input type="hidden" name="_action" value="create">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;">
            <div class="pbm-form-group">
                <label class="pbm-label" for="v-name">View Name <span class="pbm-required">*</span></label>
                <input id="v-name" type="text" name="name" class="pbm-input"
                       placeholder="e.g. internal" required maxlength="80"
                       pattern="[a-zA-Z0-9_\-]+" title="Alphanumeric, hyphens and underscores only">
            </div>
            <div class="pbm-form-group">
                <label class="pbm-label" for="v-desc">Description</label>
                <input id="v-desc" type="text" name="description" class="pbm-input"
                       placeholder="Optional description" maxlength="255">
            </div>
        </div>
        <div class="pbm-form-group">
            <label class="pbm-label" for="v-clients">
                match-clients <span class="pbm-required">*</span>
                <span class="pbm-muted" style="font-weight:400;">
                    — comma- or newline-separated ACLs, IPs, or CIDRs
                </span>
            </label>
            <textarea id="v-clients" name="match_clients" class="pbm-input" rows="3" required
                      placeholder="trusted-resolvers; 192.168.0.0/16; !10.0.0.0/8;"></textarea>
        </div>
        <div style="display:flex;gap:12px;">
            <button type="submit" class="pbm-btn">
                <i class="fa-solid fa-floppy-disk me-1"></i>Save View
            </button>
            <button type="button" class="pbm-btn pbm-btn-secondary"
                    onclick="document.getElementById('view-form').style.display='none';">
                Cancel
            </button>
        </div>
    </form>
</div>

<div class="pbm-card">
    <div class="pbm-card-header"><i class="fa-solid fa-list me-2"></i>Configured Views</div>
    <?php if (empty($views)) : ?>
        <div class="pbm-empty">
            <i class="fa-solid fa-diagram-project"></i>
            <p>No views configured. Add a view to enable split-horizon DNS.</p>
        </div>
    <?php else : ?>
        <div class="pbm-table-wrap">
            <table class="pbm-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>View Name</th>
                        <th>Description</th>
                        <th>match-clients</th>
                        <th>Updated</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($views as $v) : ?>
                        <?php
                        $vName    = (string) ($v['name'] ?? '');
                        $vDesc    = (string) ($v['description'] ?? '—');
                        $vClients = (string) ($v['match_clients'] ?? '');
                        $vUpdated = (string) ($v['updated_at'] ?? '');
                        ?>
                        <tr>
                            <td><?= (int) ($v['id'] ?? 0) ?></td>
                            <td>
                                <strong><?= htmlspecialchars($vName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong>
                            </td>
                            <td><?= htmlspecialchars($vDesc, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                            <td style="max-width:200px;">
                                <code style="font-size:.78rem;white-space:pre-wrap;word-break:break-all;">
                                    <?= htmlspecialchars($vClients, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                </code>
                            </td>
                            <td style="font-size:.82rem;">
                                <?= htmlspecialchars($vUpdated, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                            </td>
                            <td>
                                <form method="post" action="/views"
                                      onsubmit="return confirm('Delete view? Zones will lose their association.');">
                                    <input type="hidden" name="_csrf_token" value="<?= $csrfVal ?>">
                                    <input type="hidden" name="_action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int) ($v['id'] ?? 0) ?>">
                                    <button type="submit" class="pbm-btn pbm-btn-sm pbm-btn-danger">
                                        <i class="fa-solid fa-trash"></i>
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
