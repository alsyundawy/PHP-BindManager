<?php

declare(strict_types=1);

/**
 * @var array<int, array<string, mixed>> $acls
 * @var string                           $csrfToken
 * @var string|null                      $flashSuccess
 * @var string|null                      $flashError
 */
$templateVars = get_defined_vars();
$acls         = (array) ($templateVars['acls'] ?? []);
$csrfToken    = (string) ($templateVars['csrfToken'] ?? '');
$flashSuccess = isset($templateVars['flashSuccess']) ? (string) $templateVars['flashSuccess'] : null;
$flashError   = isset($templateVars['flashError']) ? (string) $templateVars['flashError'] : null;
$csrfVal      = htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

$title = 'Access Control Lists — PHP-BindManager';
?>
<?php ob_start(); ?>

<div class="pbm-page-heading">
    <div>
        <h1><i class="fa-solid fa-shield-halved me-2"></i>Access Control Lists</h1>
        <p class="pbm-muted">Manage named ACLs used in BIND9 <code>named.conf</code> directives.</p>
    </div>
    <button type="button" class="pbm-btn"
            onclick="document.getElementById('acl-form').style.display='block';this.style.display='none';">
        <i class="fa-solid fa-plus me-1"></i>New ACL
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

<div id="acl-form" class="pbm-card" style="margin-bottom:24px;display:none;">
    <div class="pbm-card-header"><i class="fa-solid fa-plus me-2"></i>New ACL</div>
    <form method="post" action="/acls" style="margin-top:16px;">
        <input type="hidden" name="_csrf_token" value="<?= $csrfVal ?>">
        <input type="hidden" name="_action" value="create">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;">
            <div class="pbm-form-group">
                <label class="pbm-label" for="acl-name">ACL Name <span class="pbm-required">*</span></label>
                <input id="acl-name" type="text" name="name" class="pbm-input"
                       placeholder="e.g. trusted-resolvers" required maxlength="80"
                       pattern="[a-zA-Z0-9_\-]+" title="Alphanumeric, hyphens and underscores only">
            </div>
            <div class="pbm-form-group">
                <label class="pbm-label" for="acl-desc">Description</label>
                <input id="acl-desc" type="text" name="description" class="pbm-input"
                       placeholder="Optional description" maxlength="255">
            </div>
        </div>
        <div class="pbm-form-group">
            <label class="pbm-label" for="acl-entries">
                Entries <span class="pbm-required">*</span>
                <span class="pbm-muted" style="font-weight:400;"> — one per line (IP, CIDR, or key name)</span>
            </label>
            <textarea id="acl-entries" name="entries" class="pbm-input" rows="5" required
                      placeholder="192.168.1.0/24&#10;10.0.0.0/8&#10;!172.16.0.0/12"></textarea>
        </div>
        <div style="display:flex;gap:12px;">
            <button type="submit" class="pbm-btn">
                <i class="fa-solid fa-floppy-disk me-1"></i>Save ACL
            </button>
            <button type="button" class="pbm-btn pbm-btn-secondary"
                    onclick="document.getElementById('acl-form').style.display='none';">
                Cancel
            </button>
        </div>
    </form>
</div>

<div class="pbm-card">
    <div class="pbm-card-header"><i class="fa-solid fa-list me-2"></i>Defined ACLs</div>
    <?php if (empty($acls)) : ?>
        <div class="pbm-empty">
            <i class="fa-solid fa-shield-halved"></i>
            <p>No ACLs defined. Create one to control DNS query access.</p>
        </div>
    <?php else : ?>
        <div class="pbm-table-wrap">
            <table class="pbm-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Entries Preview</th>
                        <th>Updated</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($acls as $acl) : ?>
                        <?php
                        $aclName = (string) ($acl['name'] ?? '');
                        $aclDesc = (string) ($acl['description'] ?? '—');
                        $rawEnt  = (string) ($acl['entries'] ?? '');
                        $lines   = explode("\n", $rawEnt);
                        $preview = implode(', ', array_slice($lines, 0, 3));
                        if (count($lines) > 3) {
                            $preview .= '…';
                        }
                        $updated = (string) ($acl['updated_at'] ?? '');
                        ?>
                        <tr>
                            <td><?= (int) ($acl['id'] ?? 0) ?></td>
                            <td>
                                <strong><?= htmlspecialchars($aclName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong>
                            </td>
                            <td><?= htmlspecialchars($aclDesc, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                            <td style="max-width:220px;">
                                <code style="font-size:.78rem;white-space:pre-wrap;word-break:break-all;">
                                    <?= htmlspecialchars($preview, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                </code>
                            </td>
                            <td style="font-size:.82rem;">
                                <?= htmlspecialchars($updated, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                            </td>
                            <td>
                                <form method="post" action="/acls"
                                      onsubmit="return confirm('Delete this ACL?');">
                                    <input type="hidden" name="_csrf_token" value="<?= $csrfVal ?>">
                                    <input type="hidden" name="_action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int) ($acl['id'] ?? 0) ?>">
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
