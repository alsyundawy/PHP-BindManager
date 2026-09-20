<?php

declare(strict_types=1);

/**
 * @var array<int, array<string, mixed>> $zones
 * @var array<int, array<string, mixed>> $keys
 * @var string                           $csrfToken
 * @var string|null                      $flashSuccess
 * @var string|null                      $flashError
 */
$templateVars = get_defined_vars();
$zones        = (array) ($templateVars['zones'] ?? []);
$keys         = (array) ($templateVars['keys'] ?? []);
$csrfToken    = (string) ($templateVars['csrfToken'] ?? '');
$flashSuccess = isset($templateVars['flashSuccess']) ? (string) $templateVars['flashSuccess'] : null;
$flashError   = isset($templateVars['flashError']) ? (string) $templateVars['flashError'] : null;
$csrfVal      = htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

$title = 'DNSSEC Manager — PHP-BindManager';

$keyRoleLabels = ['ksk' => 'KSK', 'zsk' => 'ZSK', 'csk' => 'CSK'];
$algoLabels    = [
    5  => 'RSASHA1',
    7  => 'NSEC3RSASHA1',
    8  => 'RSASHA256',
    10 => 'RSASHA512',
    13 => 'ECDSAP256SHA256',
    14 => 'ECDSAP384SHA384',
    15 => 'ED25519',
    16 => 'ED448',
];
?>
<?php ob_start(); ?>

<div class="pbm-page-heading">
    <div>
        <h1><i class="fa-solid fa-key me-2"></i>DNSSEC Manager</h1>
        <p class="pbm-muted">Generate key pairs, sign zones, and manage rollovers.</p>
    </div>
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

<div class="pbm-card" style="margin-bottom:24px;">
    <div class="pbm-card-header">
        <i class="fa-solid fa-gears me-2"></i>Generate Key Pair
    </div>
    <form method="post" action="/dnssec" style="margin-top:16px;">
        <input type="hidden" name="_csrf_token" value="<?= $csrfVal ?>">
        <input type="hidden" name="_action" value="generate">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;">
            <div class="pbm-form-group">
                <label class="pbm-label" for="dnssec-zone">Zone <span class="pbm-required">*</span></label>
                <select id="dnssec-zone" name="zone_id" class="pbm-input" required>
                    <option value="">— Select zone —</option>
                    <?php foreach ($zones as $z) : ?>
                        <option value="<?= (int) ($z['id'] ?? 0) ?>">
                            <?= htmlspecialchars((string) ($z['name'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="pbm-form-group">
                <label class="pbm-label" for="dnssec-role">Key Role <span class="pbm-required">*</span></label>
                <select id="dnssec-role" name="key_role" class="pbm-input" required>
                    <option value="ksk">KSK (Key-Signing Key)</option>
                    <option value="zsk" selected>ZSK (Zone-Signing Key)</option>
                    <option value="csk">CSK (Combined-Signing Key)</option>
                </select>
            </div>
            <div class="pbm-form-group">
                <label class="pbm-label" for="dnssec-algo">Algorithm <span class="pbm-required">*</span></label>
                <select id="dnssec-algo" name="algorithm" class="pbm-input" required>
                    <?php foreach ($algoLabels as $num => $name) : ?>
                        <option value="<?= $num ?>" <?= $num === 13 ? 'selected' : '' ?>>
                            <?= $num ?> — <?= htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <button type="submit" class="pbm-btn" style="margin-top:4px;">
            <i class="fa-solid fa-key me-1"></i>Generate Keys
        </button>
    </form>
</div>

<div class="pbm-card">
    <div class="pbm-card-header"><i class="fa-solid fa-list me-2"></i>Active Keys</div>
    <?php if (empty($keys)) : ?>
        <div class="pbm-empty">
            <i class="fa-solid fa-key"></i>
            <p>No DNSSEC keys generated yet. Use the form above to create a key pair.</p>
        </div>
    <?php else : ?>
        <div class="pbm-table-wrap">
            <table class="pbm-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Zone</th>
                        <th>Role</th>
                        <th>Key Tag</th>
                        <th>Algorithm</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($keys as $k) :
                        $zName       = (string) ($k['zone_name'] ?? '');
                        $role        = (string) ($k['key_role'] ?? 'zsk');
                        $roleLabel   = $keyRoleLabels[$role] ?? strtoupper($role);
                        $roleBadge   = $role === 'ksk' ? 'pbm-badge-danger' : 'pbm-badge-info';
                        $algoNum     = (int) ($k['algorithm'] ?? 0);
                        $algoText    = $algoNum . ' — ' . ($algoLabels[$algoNum] ?? 'Unknown');
                        $status      = (string) ($k['status'] ?? 'active');
                        $statusBadge = match ($status) {
                            'active'  => 'pbm-badge-success',
                            'retired' => 'pbm-badge-warning',
                            'revoked' => 'pbm-badge-danger',
                            default   => 'pbm-badge-secondary',
                        };
                        $createdAt   = (string) ($k['created_at'] ?? '');
                        ?>
                        <tr>
                            <td><?= (int) ($k['id'] ?? 0) ?></td>
                            <td>
                                <strong><?= htmlspecialchars($zName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong>
                            </td>
                            <td>
                                <span class="pbm-badge <?= $roleBadge ?>">
                                    <?= htmlspecialchars($roleLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                </span>
                            </td>
                            <td><code><?= (int) ($k['key_tag'] ?? 0) ?></code></td>
                            <td>
                                <?= htmlspecialchars($algoText, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                            </td>
                            <td>
                                <span class="pbm-badge <?= $statusBadge ?>">
                                    <?= htmlspecialchars(ucfirst($status), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                </span>
                            </td>
                            <td style="font-size:.82rem;">
                                <?= htmlspecialchars($createdAt, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                            </td>
                            <td>
                                <?php if ($status === 'active') : ?>
                                    <form method="post" action="/dnssec"
                                          onsubmit="return confirm('Retire key? It will no longer sign.');">
                                        <input type="hidden" name="_csrf_token" value="<?= $csrfVal ?>">
                                        <input type="hidden" name="_action" value="retire">
                                        <input type="hidden" name="id" value="<?= (int) ($k['id'] ?? 0) ?>">
                                        <button type="submit" class="pbm-btn pbm-btn-sm pbm-btn-warning">
                                            <i class="fa-solid fa-rotate-right me-1"></i>Retire
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
