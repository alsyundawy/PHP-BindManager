<?php

declare(strict_types=1);

/** @var array<int, array<string, mixed>> $zones */
/** @var int $selectedId */
/** @var string $csrfToken */
$templateVars = get_defined_vars();
$zones        = (array) ($templateVars['zones'] ?? []);
$selectedId   = (int) ($templateVars['selectedId'] ?? 0);
$csrfToken    = (string) ($templateVars['csrfToken'] ?? '');

$recordTypes = [
    'A', 'AAAA', 'CNAME', 'MX', 'TXT', 'NS', 'SOA', 'SRV',
    'CAA', 'PTR', 'NAPTR', 'TLSA', 'SSHFP', 'DS',
];

ob_start();
?>
<section class="pbm-page-heading">
    <div>
        <h1>Add DNS record</h1>
        <div class="pbm-muted">Create a new authoritative resource record in BIND9.</div>
    </div>
    <a class="pbm-btn" href="/records<?= $selectedId > 0 ? '?zone_id=' . $selectedId : '' ?>">
        <i class="fa-solid fa-arrow-left me-1"></i>Back
    </a>
</section>

<article class="pbm-card" style="max-width: 640px;">
    <div class="pbm-card-head">
        <h2 class="pbm-card-title">Record specification</h2>
    </div>
    <form method="post" action="/records" autocomplete="off" style="display: grid; gap: 16px; margin-top: 8px;">
        <input
            type="hidden"
            name="_csrf_token"
            value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
        >

        <div>
            <label for="zone-select" style="display: block; font-weight: 600; margin-bottom: 6px;">Zone</label>
            <select
                id="zone-select"
                name="zone_id"
                required
                class="form-select"
                style="width: 100%; padding: 8px 12px; border-radius: 8px;
                       background: var(--pbm-surface-2); color: var(--pbm-text); border: 1px solid var(--pbm-border);"
            >
                <option value="">-- Select zone --</option>
                <?php foreach ($zones as $z) : ?>
                    <?php $zid = (int) ($z['id'] ?? 0); ?>
                    <option value="<?= $zid ?>" <?= $zid === $selectedId ? 'selected' : '' ?>>
                        <?= htmlspecialchars((string) ($z['name'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 12px;">
            <div>
                <label for="record-name" style="display: block; font-weight: 600; margin-bottom: 6px;">
                    Name / Owner <span class="pbm-muted">(@ for apex)</span>
                </label>
                <input
                    id="record-name"
                    type="text"
                    name="name"
                    required
                    value="@"
                    class="form-control"
                    style="width: 100%; padding: 8px 12px; border-radius: 8px;
                           background: var(--pbm-surface-2); color: var(--pbm-text);
                           border: 1px solid var(--pbm-border); font-family: monospace;"
                >
            </div>
            <div>
                <label for="record-type" style="display: block; font-weight: 600; margin-bottom: 6px;">Type</label>
                <select
                    id="record-type"
                    name="record_type"
                    class="form-select"
                    style="width: 100%; padding: 8px 12px; border-radius: 8px;
                           background: var(--pbm-surface-2); color: var(--pbm-text);
                           border: 1px solid var(--pbm-border);"
                >
                    <?php foreach ($recordTypes as $type) : ?>
                        <option value="<?= $type ?>"><?= $type ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
            <div>
                <label
                    for="record-ttl"
                    style="display: block; font-weight: 600; margin-bottom: 6px;"
                >TTL (seconds)</label>
                <input
                    id="record-ttl"
                    type="number"
                    name="ttl"
                    required
                    min="1"
                    max="2147483647"
                    value="3600"
                    class="form-control"
                    style="width: 100%; padding: 8px 12px; border-radius: 8px;
                           background: var(--pbm-surface-2); color: var(--pbm-text);
                           border: 1px solid var(--pbm-border);"
                >
            </div>
            <div>
                <label for="record-priority" style="display: block; font-weight: 600; margin-bottom: 6px;">
                    Priority <span class="pbm-muted">(MX/SRV)</span>
                </label>
                <input
                    id="record-priority"
                    type="number"
                    name="priority"
                    min="0"
                    max="65535"
                    placeholder="e.g. 10"
                    class="form-control"
                    style="width: 100%; padding: 8px 12px; border-radius: 8px;
                           background: var(--pbm-surface-2); color: var(--pbm-text);
                           border: 1px solid var(--pbm-border);"
                >
            </div>
        </div>

        <div>
            <label for="record-content" style="display: block; font-weight: 600; margin-bottom: 6px;">
                Record content / Target value
            </label>
            <input
                id="record-content"
                type="text"
                name="content"
                required
                placeholder="e.g. 192.0.2.1, mail.example.com., or v=spf1..."
                class="form-control"
                style="width: 100%; padding: 8px 12px; border-radius: 8px;
                       background: var(--pbm-surface-2); color: var(--pbm-text);
                       border: 1px solid var(--pbm-border); font-family: monospace;"
            >
            <div class="pbm-muted" style="font-size: .8rem; margin-top: 6px;">
                <strong>Format hints:</strong>
                CAA: <code>0 issue "letsencrypt.org"</code> &bull;
                NAPTR: <code>100 10 "u" "sip+E2U" "!^.*$!sip:info@example.com!" .</code> &bull;
                TLSA: <code>3 1 1 d2ab3453...</code> &bull;
                SSHFP: <code>1 1 123456...</code> &bull;
                DS: <code>2371 13 2 123456...</code>
            </div>
        </div>

        <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 8px;">
            <a class="pbm-btn" href="/records<?= $selectedId > 0 ? '?zone_id=' . $selectedId : '' ?>">Cancel</a>
            <button class="pbm-btn pbm-btn-primary" type="submit">
                <i class="fa-solid fa-plus me-1"></i>Create record
            </button>
        </div>
    </form>
</article>
<?php
$content = (string) ob_get_clean();
$title   = 'Create Record - PHP-BindManager';
require_once __DIR__ . '/../layouts/app.php';
