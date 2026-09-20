<?php

declare(strict_types=1);

/** @var array<string, mixed> $user */
/** @var array<int, array<string, mixed>> $roles */
/** @var string $csrfToken */
$templateVars = get_defined_vars();
$user         = (array) ($templateVars['user'] ?? []);
$roles        = (array) ($templateVars['roles'] ?? []);
$csrfToken    = (string) ($templateVars['csrfToken'] ?? '');

$uid      = (int) ($user['id'] ?? 0);
$userRole = (int) ($user['role_id'] ?? 1);
$isActive = (int) ($user['is_active'] ?? 1) === 1;

ob_start();
?>
<section class="pbm-page-heading">
    <div>
        <h1>Edit User</h1>
        <div class="pbm-muted">Update credentials or role for
            <?= htmlspecialchars((string) ($user['username'] ?? ''), ENT_QUOTES, 'UTF-8') ?>.
        </div>
    </div>
    <a class="pbm-btn" href="/users"><i class="fa-solid fa-arrow-left me-1"></i>Back to users</a>
</section>

<article class="pbm-card" style="max-width: 580px;">
    <div class="pbm-card-head">
        <h2 class="pbm-card-title">Modify Account</h2>
    </div>
    <form
        method="post"
        action="/users/<?= $uid ?>"
        autocomplete="off"
        style="display: grid; gap: 16px; margin-top: 12px;"
    >
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
                value="<?= htmlspecialchars((string) ($user['username'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
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
                value="<?= htmlspecialchars((string) ($user['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                class="form-control"
                style="width: 100%; padding: 8px 12px; border-radius: 8px;
                       background: var(--pbm-surface-2); color: var(--pbm-text);
                       border: 1px solid var(--pbm-border);"
            >
        </div>

        <div>
            <label for="password" style="display: block; font-weight: 600; margin-bottom: 6px;">
                Reset Password <span class="pbm-muted">(leave blank to keep current)</span>
            </label>
            <input
                id="password"
                type="password"
                name="password"
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
                    <option value="<?= $rid ?>" <?= $rid === $userRole ? 'selected' : '' ?>>
                        <?= htmlspecialchars(ucfirst((string) ($r['name'] ?? '')), ENT_QUOTES, 'UTF-8') ?>
                        - <?= htmlspecialchars((string) ($r['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label style="display: flex; align-items: center; gap: 8px; font-weight: 600; cursor: pointer;">
                <input type="checkbox" name="is_active" value="1" <?= $isActive ? 'checked' : '' ?>>
                <span>Account active and enabled</span>
            </label>
        </div>

        <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 8px;">
            <a class="pbm-btn" href="/users">Cancel</a>
            <button class="pbm-btn pbm-btn-primary" type="submit">
                <i class="fa-solid fa-floppy-disk me-1"></i>Save changes
            </button>
        </div>
    </form>
</article>
<?php
$content = (string) ob_get_clean();
$title   = 'Edit User - PHP-BindManager';
require_once __DIR__ . '/../layouts/app.php';
