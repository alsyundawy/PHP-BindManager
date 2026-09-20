<?php

declare(strict_types=1);

/**
 * @var array<int, array<string, mixed>> $tokens
 * @var string                           $csrfToken
 * @var string|null                      $newToken
 * @var string|null                      $flashSuccess
 * @var string|null                      $flashError
 */
if (! isset($tokens) || ! is_array($tokens)) {
    $tokens = [];
}
if (! isset($csrfToken) || ! is_string($csrfToken)) {
    $csrfToken = '';
}
if (! isset($newToken)) {
    $newToken = null;
}
if (! isset($flashSuccess)) {
    $flashSuccess = null;
}
if (! isset($flashError)) {
    $flashError = null;
}
$csrfVal = htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

$title       = 'API Tokens — PHP-BindManager';
$allScopes   = ['zones:read', 'zones:write', 'records:read', 'records:write', 'system:read'];
?>
<?php ob_start(); ?>

<div class="pbm-page-heading">
    <div>
        <h1><i class="fa-solid fa-key me-2"></i>API Token Management</h1>
        <p class="pbm-muted">Generate and revoke personal API access tokens.</p>
    </div>
</div>

<?php if (isset($newToken) && $newToken !== null && $newToken !== '') : ?>
    <div class="pbm-alert pbm-alert-success" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i>
        <strong>Token created!</strong> Copy it now — it will not be shown again.
        <div style="margin-top:10px;">
            <code id="new-token-val"
                  style="display:block;padding:10px 14px;background:var(--pbm-bg);
                         border:1px solid var(--pbm-border);border-radius:8px;
                         word-break:break-all;font-size:.85rem;user-select:all;">
                <?= htmlspecialchars($newToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
            </code>
            <button type="button" class="pbm-btn pbm-btn-sm"
                    style="margin-top:8px;"
                    onclick="navigator.clipboard.writeText(document.getElementById('new-token-val').textContent.trim())
                             .then(()=>this.textContent='Copied!')">
                <i class="fa-solid fa-copy me-1"></i>Copy
            </button>
        </div>
    </div>
<?php endif; ?>

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
        <i class="fa-solid fa-plus me-2"></i>Generate New Token
    </div>
    <form method="post" action="/system/tokens" style="margin-top:16px;">
        <input type="hidden" name="_csrf_token" value="<?= $csrfVal ?>">
        <input type="hidden" name="_action" value="create">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;">
            <div class="pbm-form-group">
                <label class="pbm-label" for="tok-name">Token Name <span class="pbm-required">*</span></label>
                <input id="tok-name" type="text" name="name" class="pbm-input"
                       placeholder="e.g. CI/CD Pipeline" required maxlength="80">
            </div>
            <div class="pbm-form-group">
                <label class="pbm-label" for="tok-expires">Expires At (optional)</label>
                <input id="tok-expires" type="date" name="expires_at" class="pbm-input"
                       min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
            </div>
        </div>
        <div class="pbm-form-group">
            <div id="scopes-heading" class="pbm-label">Scopes</div>
            <div role="group" aria-labelledby="scopes-heading"
                 style="display:flex;flex-wrap:wrap;gap:12px;margin-top:6px;">
                <?php foreach ($allScopes as $scope) : ?>
                    <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                        <input type="checkbox" name="scopes[]"
                               value="<?= htmlspecialchars($scope, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                        <code style="font-size:.82rem;">
                            <?= htmlspecialchars($scope, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                        </code>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <button type="submit" class="pbm-btn" style="margin-top:4px;">
            <i class="fa-solid fa-key me-1"></i>Generate Token
        </button>
    </form>
</div>

<div class="pbm-card">
    <div class="pbm-card-header">
        <i class="fa-solid fa-list me-2"></i>Your Tokens
    </div>
    <?php if (empty($tokens)) : ?>
        <div class="pbm-empty">
            <i class="fa-solid fa-key"></i>
            <p>No API tokens yet. Generate one above.</p>
        </div>
    <?php else : ?>
        <div class="pbm-table-wrap">
            <table class="pbm-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Scopes</th>
                        <th>Last Used</th>
                        <th>Expires</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tokens as $tok) : ?>
                        <?php
                        $isRevoked  = ($tok['revoked_at'] ?? null) !== null;
                        $isExpired  = ($tok['expires_at'] ?? null) !== null
                                      && strtotime((string) $tok['expires_at']) < time();
                        $scopesJson = (string) ($tok['scopes'] ?? '[]');
                        $scopes     = json_decode($scopesJson, true);
                        $scopes     = is_array($scopes) ? $scopes : [];
                        $tokName    = (string) ($tok['name'] ?? '');
                        $lastUsed   = (string) ($tok['last_used_at'] ?? '—');
                        $expires    = (string) ($tok['expires_at'] ?? '∞');
                        $created    = (string) ($tok['created_at'] ?? '');
                        $tokId      = (int) ($tok['id'] ?? 0);
                        ?>
                        <tr style="<?= $isRevoked || $isExpired ? 'opacity:.55;' : '' ?>">
                            <td><?= $tokId ?></td>
                            <td>
                                <strong><?= htmlspecialchars($tokName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong>
                            </td>
                            <td style="max-width:220px;">
                                <?php foreach ($scopes as $s) : ?>
                                    <span class="pbm-badge pbm-badge-secondary"
                                          style="margin:1px 2px;font-size:.7rem;">
                                        <?= htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                    </span>
                                <?php endforeach; ?>
                            </td>
                            <td style="font-size:.82rem;">
                                <?= htmlspecialchars($lastUsed, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                            </td>
                            <td style="font-size:.82rem;">
                                <?= htmlspecialchars($expires, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                            </td>
                            <td>
                                <?php if ($isRevoked) : ?>
                                    <span class="pbm-badge pbm-badge-danger">Revoked</span>
                                <?php elseif ($isExpired) : ?>
                                    <span class="pbm-badge pbm-badge-warning">Expired</span>
                                <?php else : ?>
                                    <span class="pbm-badge pbm-badge-success">Active</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:.82rem;">
                                <?= htmlspecialchars($created, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                            </td>
                            <td>
                                <?php if (! $isRevoked) : ?>
                                    <form method="post" action="/system/tokens"
                                          onsubmit="return confirm('Revoke this token permanently?');">
                                        <input type="hidden" name="_csrf_token" value="<?= $csrfVal ?>">
                                        <input type="hidden" name="_action" value="revoke">
                                        <input type="hidden" name="id" value="<?= $tokId ?>">
                                        <button type="submit" class="pbm-btn pbm-btn-sm pbm-btn-danger"
                                                title="Revoke token">
                                            <i class="fa-solid fa-ban"></i>
                                        </button>
                                    </form>
                                <?php else : ?>
                                    <form method="post" action="/system/tokens"
                                          onsubmit="return confirm('Delete this token record?');">
                                        <input type="hidden" name="_csrf_token" value="<?= $csrfVal ?>">
                                        <input type="hidden" name="_action" value="delete">
                                        <input type="hidden" name="id" value="<?= $tokId ?>">
                                        <button type="submit" class="pbm-btn pbm-btn-sm pbm-btn-secondary"
                                                title="Delete revoked token">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
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
