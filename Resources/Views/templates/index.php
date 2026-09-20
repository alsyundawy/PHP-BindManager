<?php

declare(strict_types=1);

/**
 * @var array<int, array<string, mixed>> $templates
 * @var array<int, array<string, mixed>> $zones
 * @var string                           $csrfToken
 * @var string|null                      $flashSuccess
 * @var string|null                      $flashError
 */
$templateVars = get_defined_vars();
$templates    = (array) ($templateVars['templates'] ?? []);
$zones        = (array) ($templateVars['zones'] ?? []);
$csrfToken    = (string) ($templateVars['csrfToken'] ?? '');
$flashSuccess = isset($templateVars['flashSuccess']) ? (string) $templateVars['flashSuccess'] : null;
$flashError   = isset($templateVars['flashError']) ? (string) $templateVars['flashError'] : null;
$csrfVal      = htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

$title = 'Zone Templates Library — PHP-BindManager';
?>
<?php ob_start(); ?>

<div class="pbm-page-heading">
    <div>
        <h1><i class="fa-solid fa-copy me-2"></i>Zone Templates Library</h1>
        <p class="pbm-muted">Pre-configured DNS record profiles for instant zone provisioning.</p>
    </div>
    <button type="button" class="pbm-btn"
            onclick="document.getElementById('tpl-form').style.display='block';this.style.display='none';">
        <i class="fa-solid fa-plus me-1"></i>New Template
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

<div id="tpl-form" class="pbm-card" style="margin-bottom:24px;display:none;">
    <div class="pbm-card-header"><i class="fa-solid fa-plus me-2"></i>Create DNS Zone Template</div>
    <form method="post" action="/templates" style="margin-top:16px;">
        <input type="hidden" name="_csrf_token" value="<?= $csrfVal ?>">
        <input type="hidden" name="_action" value="create">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;">
            <div class="pbm-form-group">
                <label class="pbm-label" for="tpl-name">Template Name <span class="pbm-required">*</span></label>
                <input id="tpl-name" type="text" name="name" class="pbm-input"
                       placeholder="e.g. Standard Web & Mail" required maxlength="100">
            </div>
            <div class="pbm-form-group">
                <label class="pbm-label" for="tpl-desc">Description</label>
                <input id="tpl-desc" type="text" name="description" class="pbm-input"
                       placeholder="e.g. Preconfigures MX, SPF, A, and CNAME records" maxlength="255">
            </div>
        </div>
        <div class="pbm-form-group">
            <label class="pbm-label" for="tpl-records">
                Template Records (JSON) <span class="pbm-required">*</span>
            </label>
            <textarea id="tpl-records" name="records_json" class="pbm-input" rows="6" required
                      style="font-family:monospace;font-size:0.85rem;"
>[
  {"name": "@", "type": "A", "ttl": 3600, "content": "192.0.2.1"},
  {"name": "www", "type": "CNAME", "ttl": 3600, "content": "@"},
  {"name": "mail", "type": "A", "ttl": 3600, "content": "192.0.2.2"},
  {"name": "@", "type": "MX", "ttl": 3600, "priority": 10, "content": "mail"}
]</textarea>
        </div>
        <div style="display:flex;gap:12px;">
            <button type="submit" class="pbm-btn">
                <i class="fa-solid fa-floppy-disk me-1"></i>Save Template
            </button>
            <button type="button" class="pbm-btn pbm-btn-secondary"
                    onclick="document.getElementById('tpl-form').style.display='none';">
                Cancel
            </button>
        </div>
    </form>
</div>

<div class="pbm-card">
    <div class="pbm-card-header"><i class="fa-solid fa-list me-2"></i>Available Templates</div>
    <?php if (empty($templates)) : ?>
        <div class="pbm-empty">
            <i class="fa-solid fa-copy"></i>
            <p>No templates created yet. Add a template or seed defaults.</p>
        </div>
    <?php else : ?>
        <div class="pbm-table-wrap">
            <table class="pbm-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Template Name</th>
                        <th>Description</th>
                        <th>Record Profiles</th>
                        <th>Apply to Zone</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($templates as $tpl) : ?>
                        <?php
                        $tplId    = (int) ($tpl['id'] ?? 0);
                        $tplName  = (string) ($tpl['name'] ?? '');
                        $tplDesc  = (string) ($tpl['description'] ?? '—');
                        $rawJson  = (string) ($tpl['records_json'] ?? '[]');
                        $decoded  = json_decode($rawJson, true);
                        $recCount = is_array($decoded) ? count($decoded) : 0;
                        ?>
                        <tr>
                            <td><?= $tplId ?></td>
                            <td>
                                <strong><?= htmlspecialchars($tplName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong>
                            </td>
                            <td><?= htmlspecialchars($tplDesc, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                            <td>
                                <span class="pbm-badge pbm-badge-primary"><?= $recCount ?> records</span>
                            </td>
                            <td>
                                <form method="post" action="/templates/apply" style="display:flex;gap:6px;">
                                    <input type="hidden" name="_csrf_token" value="<?= $csrfVal ?>">
                                    <input type="hidden" name="template_id" value="<?= $tplId ?>">
                                    <select name="zone_id" class="pbm-input"
                                            style="padding:4px 8px;font-size:.82rem;" required>
                                        <option value="">Select Target Zone</option>
                                        <?php foreach ($zones as $z) : ?>
                                            <option value="<?= (int) ($z['id'] ?? 0) ?>">
                                                <?= htmlspecialchars(
                                                    (string) ($z['name'] ?? ''),
                                                    ENT_QUOTES | ENT_SUBSTITUTE,
                                                    'UTF-8'
                                                ) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="pbm-btn pbm-btn-sm"
                                            onclick="return confirm('Apply template records to selected zone?');">
                                        Apply
                                    </button>
                                </form>
                            </td>
                            <td>
                                <form method="post" action="/templates"
                                      onsubmit="return confirm('Delete template?');">
                                    <input type="hidden" name="_csrf_token" value="<?= $csrfVal ?>">
                                    <input type="hidden" name="_action" value="delete">
                                    <input type="hidden" name="id" value="<?= $tplId ?>">
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
