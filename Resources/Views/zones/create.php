<?php

declare(strict_types=1);

/** @var string $csrfToken */
$templateVars = get_defined_vars();
$csrfToken    = (string) ($templateVars['csrfToken'] ?? '');
$flashError   = (string) ($templateVars['flashError'] ?? '');

ob_start();
?>
<section class="pbm-page-heading">
    <div>
        <h1>Create zone</h1>
        <div class="pbm-muted">Add a new authoritative DNS zone. A zone file will be provisioned on deploy.</div>
    </div>
    <a class="pbm-btn" href="/zones"><i class="fa-solid fa-arrow-left me-1"></i>Back to zones</a>
</section>

<article class="pbm-card" style="max-width: 640px;">
    <div class="pbm-card-head">
        <h2 class="pbm-card-title">Zone details</h2>
    </div>
<?php if ($flashError !== '') : ?>
    <div class="pbm-alert pbm-alert-danger" role="alert" aria-live="assertive">
        <i class="fa-solid fa-circle-exclamation me-2"></i>
        <?= htmlspecialchars($flashError, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
    </div>
<?php endif; ?>
    <form method="post" action="/zones" autocomplete="off" style="display: grid; gap: 16px; margin-top: 8px;">
        <input
            type="hidden"
            name="_csrf_token"
            value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
        >
        <div>
            <label for="zone-name" style="display: block; font-weight: 600; margin-bottom: 6px;">
                Zone name <span class="pbm-muted">(FQDN)</span>
            </label>
            <input
                id="zone-name"
                type="text"
                name="name"
                required
                maxlength="253"
                placeholder="example.com."
                class="form-control"
                style="width: 100%; padding: 8px 12px; border-radius: 8px;
                       border: 1px solid var(--pbm-border); background: var(--pbm-surface-2);
                       color: var(--pbm-text); font-family: monospace;"
            >
        </div>
        <div>
            <label for="zone-type" style="display: block; font-weight: 600; margin-bottom: 6px;">Zone type</label>
            <select
                id="zone-type"
                name="zone_type"
                class="form-select"
                style="width: 100%; padding: 8px 12px; border-radius: 8px;
                       border: 1px solid var(--pbm-border); background: var(--pbm-surface-2);
                       color: var(--pbm-text);"
            >
                <option value="master">Master (Primary)</option>
                <option value="slave">Slave (Secondary)</option>
                <option value="forward">Forward</option>
                <option value="hint">Hint</option>
            </select>
        </div>
        <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 8px;">
            <a class="pbm-btn" href="/zones">Cancel</a>
            <button class="pbm-btn pbm-btn-primary" type="submit">
                <i class="fa-solid fa-plus me-1"></i>Create zone
            </button>
        </div>
    </form>
</article>
<?php
$content = (string) ob_get_clean();
$title   = 'Create Zone - PHP-BindManager';
require_once __DIR__ . '/../layouts/app.php';
