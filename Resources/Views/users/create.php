<?php

declare(strict_types=1);

/** @var array<int, array<string, mixed>> $roles */
/** @var string $csrfToken */
$templateVars = get_defined_vars();
$roles        = (array) ($templateVars['roles'] ?? []);
$csrfToken    = (string) ($templateVars['csrfToken'] ?? '');

ob_start();
?>
<section class="pbm-page-heading">
    <div>
        <h1>Create User</h1>
        <div class="pbm-muted">Add a new operator account with specific RBAC permissions.</div>
    </div>
    <a class="pbm-btn" href="/users"><i class="fa-solid fa-arrow-left me-1"></i>Back to users</a>
</section>

<article class="pbm-card" style="max-width: 580px;">
    <div class="pbm-card-head">
        <h2 class="pbm-card-title">User Account Details</h2>
    </div>
    <form method="post" action="/users" autocomplete="off" style="display: grid; gap: 16px; margin-top: 12px;">
        <input
            type="hidden"
            name="_csrf_token"
            value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>"
        >

        <div>
            <label for="username" style="display: block; font-weight: 600; margin-bottom: 6px;">Username</label>
            <input
                id="username"
                type="text"
                name="username"
                required
                class="form-control"
                style="width: 100%; padding: 8px 12px; border-radius: 8px;
                       background: var(--pbm-surface-2); color: var(--pbm-text);
                       border: 1px solid var(--pbm-border);"
            >
        </div>

        <div>
            <label for="email" style="display: block; font-weight: 600; margin-bottom: 6px;">Email address</label>
            <input
                id="email"
                type="email"
                name="email"
                required
                class="form-control"
                style="width: 100%; padding: 8px 12px; border-radius: 8px;
                       background: var(--pbm-surface-2); color: var(--pbm-text);
                       border: 1px solid var(--pbm-border);"
            >
        </div>

        <div>
            <label for="password" style="display: block; font-weight: 600; margin-bottom: 6px;">
                Password (min. 8 characters)
            </label>
            <input
                id="password"
                type="password"
                name="password"
                required
                minlength="8"
                class="form-control"
                style="width: 100%; padding: 8px 12px; border-radius: 8px;
                       background: var(--pbm-surface-2); color: var(--pbm-text);
                       border: 1px solid var(--pbm-border);"
            >
        </div>

        <div>
            <label for="role_id" style="display: block; font-weight: 600; margin-bottom: 6px;">Role</label>
            <select
                id="role_id"
                name="role_id"
                required
                class="form-select"
                style="width: 100%; padding: 8px 12px; border-radius: 8px;
                       background: var(--pbm-surface-2); color: var(--pbm-text);
                       border: 1px solid var(--pbm-border);"
            >
                <?php foreach ($roles as $r) : ?>
                    <?php $rid = (int) ($r['id'] ?? 0); ?>
                    <option value="<?= $rid ?>" <?= (string) ($r['name'] ?? '') === 'editor' ? 'selected' : '' ?>>
                        <?= htmlspecialchars(ucfirst((string) ($r['name'] ?? '')), ENT_QUOTES, 'UTF-8') ?>
                        - <?= htmlspecialchars((string) ($r['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label style="display: flex; align-items: center; gap: 8px; font-weight: 600; cursor: pointer;">
                <input type="checkbox" name="is_active" value="1" checked>
                <span>Account active and enabled</span>
            </label>
        </div>

        <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 8px;">
            <a class="pbm-btn" href="/users">Cancel</a>
            <button class="pbm-btn pbm-btn-primary" type="submit">
                <i class="fa-solid fa-plus me-1"></i>Create account
            </button>
        </div>
    </form>
</article>
<?php
$content = (string) ob_get_clean();
$title   = 'Create User - PHP-BindManager';
require_once __DIR__ . '/../layouts/app.php';
