<?php

declare(strict_types=1);

/** @var array<string, mixed> $user */
/** @var string $csrfToken */
$templateVars = get_defined_vars();
$user         = (array) ($templateVars['user'] ?? []);
$csrfToken    = (string) ($templateVars['csrfToken'] ?? '');

$roleName = (string) ($user['role_name'] ?? 'Viewer');

ob_start();
?>
<section class="pbm-page-heading">
    <div>
        <h1>My Profile</h1>
        <div class="pbm-muted">Manage your administrator credentials and account security settings.</div>
    </div>
</section>

<div class="pbm-grid" style="grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));">
    <!-- Account Information -->
    <article class="pbm-card">
        <div class="pbm-card-head">
            <h2 class="pbm-card-title"><i class="fa-solid fa-id-badge me-2"></i>Account Overview</h2>
            <span class="pbm-badge"><?= htmlspecialchars(ucfirst($roleName), ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <form method="post" action="/profile" style="display: grid; gap: 16px; margin-top: 12px;">
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
            <div class="pbm-muted" style="font-size: .85rem;">
                Last login: <?= htmlspecialchars((string) ($user['last_login_at'] ?? 'Never'), ENT_QUOTES, 'UTF-8') ?>
                (IP: <?= htmlspecialchars((string) ($user['last_login_ip'] ?? 'N/A'), ENT_QUOTES, 'UTF-8') ?>)
            </div>
            <div style="display: flex; justify-content: flex-end;">
                <button class="pbm-btn pbm-btn-primary" type="submit">
                    <i class="fa-solid fa-floppy-disk me-1"></i>Save profile
                </button>
            </div>
        </form>
    </article>

    <!-- Change Password -->
    <article class="pbm-card">
        <div class="pbm-card-head">
            <h2 class="pbm-card-title"><i class="fa-solid fa-key me-2"></i>Change Password</h2>
        </div>
        <form method="post" action="/profile/password" style="display: grid; gap: 16px; margin-top: 12px;">
            <input
                type="hidden"
                name="_csrf_token"
                value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>"
            >
            <div>
                <label for="current_password" style="display: block; font-weight: 600; margin-bottom: 6px;">
                    Current password
                </label>
                <input
                    id="current_password"
                    type="password"
                    name="current_password"
                    required
                    class="form-control"
                    style="width: 100%; padding: 8px 12px; border-radius: 8px;
                           background: var(--pbm-surface-2); color: var(--pbm-text);
                           border: 1px solid var(--pbm-border);"
                >
            </div>
            <div>
                <label for="new_password" style="display: block; font-weight: 600; margin-bottom: 6px;">
                    New password (min. 8 characters)
                </label>
                <input
                    id="new_password"
                    type="password"
                    name="new_password"
                    required
                    minlength="8"
                    class="form-control"
                    style="width: 100%; padding: 8px 12px; border-radius: 8px;
                           background: var(--pbm-surface-2); color: var(--pbm-text);
                           border: 1px solid var(--pbm-border);"
                >
            </div>
            <div>
                <label for="confirm_password" style="display: block; font-weight: 600; margin-bottom: 6px;">
                    Confirm new password
                </label>
                <input
                    id="confirm_password"
                    type="password"
                    name="confirm_password"
                    required
                    minlength="8"
                    class="form-control"
                    style="width: 100%; padding: 8px 12px; border-radius: 8px;
                           background: var(--pbm-surface-2); color: var(--pbm-text);
                           border: 1px solid var(--pbm-border);"
                >
            </div>
            <div style="display: flex; justify-content: flex-end;">
                <button class="pbm-btn pbm-btn-primary" type="submit">
                    <i class="fa-solid fa-lock me-1"></i>Update password
                </button>
            </div>
        </form>
    </article>

    <!-- Two-Factor Authentication (TOTP) -->
    <article class="pbm-card">
        <div class="pbm-card-head">
            <h2 class="pbm-card-title">
                <i class="fa-solid fa-mobile-screen-button me-2"></i>Two-Factor Authentication (2FA)
            </h2>
        </div>
        <p style="color: var(--pbm-muted); font-size: .875rem; margin-top: 12px; margin-bottom: 16px;">
            Protect your administrator account with time-based one-time password (TOTP) authentication
            using apps like Google Authenticator, Microsoft Authenticator, or Authy.
        </p>
        <div style="display: flex; gap: 12px; align-items: center;">
            <a href="/profile/totp" class="pbm-btn pbm-btn-primary">
                <i class="fa-solid fa-shield-halved me-1"></i>Configure 2FA
            </a>
            <span class="pbm-badge pbm-badge-secondary">RFC 6238 TOTP</span>
        </div>
    </article>
</div>
<?php
$content = (string) ob_get_clean();
$title   = 'My Profile - PHP-BindManager';
require_once __DIR__ . '/../layouts/app.php';
