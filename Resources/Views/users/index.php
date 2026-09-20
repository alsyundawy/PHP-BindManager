<?php

declare(strict_types=1);

/** @var array<int, array<string, mixed>> $users */
/** @var string $csrfToken */
$templateVars = get_defined_vars();
$users        = (array) ($templateVars['users'] ?? []);
$csrfToken    = (string) ($templateVars['csrfToken'] ?? '');

ob_start();
?>
<section class="pbm-page-heading">
    <div>
        <h1>User Management</h1>
        <div class="pbm-muted">Configure administrative accounts and role-based permissions (RBAC).</div>
    </div>
    <a class="pbm-btn pbm-btn-primary" href="/users/create">
        <i class="fa-solid fa-user-plus me-1"></i>New user
    </a>
</section>

<article class="pbm-card">
    <div class="pbm-card-head">
        <h2 class="pbm-card-title">System Users</h2>
        <span class="pbm-badge"><?= count($users) ?> accounts</span>
    </div>
    <?php if ($users === []) : ?>
        <div style="padding: 40px 0; text-align: center;" class="pbm-muted">
            <i class="fa-solid fa-users" style="font-size: 2rem; margin-bottom: 12px; display: block;"></i>
            No users found. <a href="/users/create">Create first user &rarr;</a>
        </div>
    <?php else : ?>
        <div class="pbm-table-wrap">
            <table class="pbm-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u) : ?>
                        <?php
                        $uid      = (int) ($u['id'] ?? 0);
                        $role     = (string) ($u['role_name'] ?? 'viewer');
                        $isActive = (int) ($u['is_active'] ?? 1) === 1;
                        ?>
                        <tr>
                            <td>
                                <strong>
                                    <?= htmlspecialchars(
                                        (string) ($u['username'] ?? ''),
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </strong>
                            </td>
                            <td><?= htmlspecialchars((string) ($u['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <span class="pbm-badge">
                                    <?= htmlspecialchars(ucfirst($role), ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($isActive) : ?>
                                    <span style="color: var(--pbm-success); font-weight: 600; font-size: .85rem;">
                                        <i class="fa-solid fa-circle-check me-1"></i>Active
                                    </span>
                                <?php else : ?>
                                    <span style="color: var(--pbm-muted); font-weight: 600; font-size: .85rem;">
                                        <i class="fa-solid fa-circle-xmark me-1"></i>Disabled
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= htmlspecialchars(
                                    (string) ($u['last_login_at'] ?? 'Never'),
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars(
                                    substr((string) ($u['created_at'] ?? ''), 0, 10),
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <a
                                        class="pbm-btn"
                                        style="padding: 3px 8px; font-size: .8rem;"
                                        href="/users/<?= $uid ?>/edit"
                                        title="Edit user"
                                    >
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <?php if ($uid !== (int) ($_SESSION['user_id'] ?? 0)) : ?>
                                        <form
                                            method="post"
                                            action="/users/<?= $uid ?>/delete"
                                            style="margin: 0;"
                                            onsubmit="return confirm('Delete this user? This cannot be undone.');"
                                        >
                                            <input
                                                type="hidden"
                                                name="_csrf_token"
                                                value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>"
                                            >
                                            <button
                                                class="pbm-btn"
                                                style="padding: 3px 8px; font-size: .8rem; color: var(--pbm-danger);"
                                                type="submit"
                                                title="Delete user"
                                            >
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</article>
<?php
$content = (string) ob_get_clean();
$title   = 'User Management - PHP-BindManager';
require_once __DIR__ . '/../layouts/app.php';
