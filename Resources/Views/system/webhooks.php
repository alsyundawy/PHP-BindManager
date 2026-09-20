<?php

declare(strict_types=1);

/**
 * @var array<int, array<string, mixed>> $webhooks
 * @var string                           $csrfToken
 * @var string|null                      $flashSuccess
 * @var string|null                      $flashError
 */
$templateVars = get_defined_vars();
$webhooks     = (array) ($templateVars['webhooks'] ?? []);
$csrfToken    = (string) ($templateVars['csrfToken'] ?? '');
$flashSuccess = isset($templateVars['flashSuccess']) ? (string) $templateVars['flashSuccess'] : null;
$flashError   = isset($templateVars['flashError']) ? (string) $templateVars['flashError'] : null;
$csrfVal      = htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

$title = 'Webhooks & Notifications — PHP-BindManager';
?>
<?php ob_start(); ?>

<div class="pbm-page-heading">
    <div>
        <h1><i class="fa-solid fa-satellite-dish me-2"></i>Webhooks</h1>
        <p class="pbm-muted">Dispatch HTTP POST notifications on zone and record lifecycle events.</p>
    </div>
    <button type="button" class="pbm-btn"
            onclick="document.getElementById('wh-form').style.display='block';this.style.display='none';">
        <i class="fa-solid fa-plus me-1"></i>New Webhook
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

<div id="wh-form" class="pbm-card" style="margin-bottom:24px;display:none;">
    <div class="pbm-card-header"><i class="fa-solid fa-plus me-2"></i>Register Webhook Endpoint</div>
    <form method="post" action="/system/webhooks" style="margin-top:16px;">
        <input type="hidden" name="_csrf_token" value="<?= $csrfVal ?>">
        <input type="hidden" name="_action" value="create">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;">
            <div class="pbm-form-group">
                <label class="pbm-label" for="wh-name">Endpoint Name <span class="pbm-required">*</span></label>
                <input id="wh-name" type="text" name="name" class="pbm-input"
                       placeholder="e.g. Discord Alerts or CI Dispatcher" required maxlength="100">
            </div>
            <div class="pbm-form-group">
                <label class="pbm-label" for="wh-url">Webhook URL <span class="pbm-required">*</span></label>
                <input id="wh-url" type="url" name="url" class="pbm-input"
                       placeholder="https://example.com/webhook/dns" required maxlength="255">
            </div>
            <div class="pbm-form-group">
                <label class="pbm-label" for="wh-secret">Secret Token (for HMAC Signature)</label>
                <input id="wh-secret" type="text" name="secret" class="pbm-input"
                       placeholder="Optional shared secret key" maxlength="128">
            </div>
            <div class="pbm-form-group">
                <label class="pbm-label" for="wh-events">Subscribed Events</label>
                <input id="wh-events" type="text" name="events" class="pbm-input"
                       value="zone.updated,record.created,record.deleted" required maxlength="255">
            </div>
        </div>
        <div style="display:flex;gap:12px;margin-top:8px;">
            <button type="submit" class="pbm-btn">
                <i class="fa-solid fa-floppy-disk me-1"></i>Save Webhook
            </button>
            <button type="button" class="pbm-btn pbm-btn-secondary"
                    onclick="document.getElementById('wh-form').style.display='none';">
                Cancel
            </button>
        </div>
    </form>
</div>

<div class="pbm-card">
    <div class="pbm-card-header"><i class="fa-solid fa-list me-2"></i>Active Webhooks</div>
    <?php if (empty($webhooks)) : ?>
        <div class="pbm-empty">
            <i class="fa-solid fa-satellite-dish"></i>
            <p>No webhooks configured yet. Register a webhook to receive real-time notifications.</p>
        </div>
    <?php else : ?>
        <div class="pbm-table-wrap">
            <table class="pbm-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>URL</th>
                        <th>Events</th>
                        <th>Last Status</th>
                        <th>Last Triggered</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($webhooks as $wh) : ?>
                        <?php
                        $whId     = (int) ($wh['id'] ?? 0);
                        $whName   = (string) ($wh['name'] ?? '');
                        $whUrl    = (string) ($wh['url'] ?? '');
                        $whEvents = (string) ($wh['events'] ?? '');
                        $whStatus = $wh['last_status'] !== null ? (int) $wh['last_status'] : null;
                        $whTrig   = (string) ($wh['last_triggered_at'] ?? 'Never');
                        ?>
                        <tr>
                            <td><?= $whId ?></td>
                            <td>
                                <strong><?= htmlspecialchars($whName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong>
                            </td>
                            <td>
                                <code style="font-size:.8rem;">
                                    <?= htmlspecialchars($whUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                </code>
                            </td>
                            <td style="font-size:.82rem;">
                                <?= htmlspecialchars($whEvents, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                            </td>
                            <td>
                                <?php if ($whStatus === null) : ?>
                                    <span class="pbm-badge pbm-badge-secondary">Pending</span>
                                <?php elseif ($whStatus >= 200 && $whStatus < 300) : ?>
                                    <span class="pbm-badge pbm-badge-success"><?= $whStatus ?> OK</span>
                                <?php else : ?>
                                    <span class="pbm-badge pbm-badge-danger"><?= $whStatus ?> Err</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:.82rem;">
                                <?= htmlspecialchars($whTrig, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                            </td>
                            <td>
                                <form method="post" action="/system/webhooks"
                                      onsubmit="return confirm('Delete webhook?');">
                                    <input type="hidden" name="_csrf_token" value="<?= $csrfVal ?>">
                                    <input type="hidden" name="_action" value="delete">
                                    <input type="hidden" name="id" value="<?= $whId ?>">
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
