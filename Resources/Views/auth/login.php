<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - PHP-BindManager</title>
    <style>
        body { margin: 0; font-family: Inter, system-ui, sans-serif; background: #0f172a; color: #e2e8f0; }
        .container { min-height: 100vh; display: grid; place-items: center; padding: 24px; }
        .panel { width: 100%; max-width: 420px; background: #111827; border: 1px solid #1f2937; border-radius: 18px; padding: 28px; }
        input { width: 100%; padding: 12px 14px; margin-top: 8px; margin-bottom: 16px; border-radius: 12px; border: 1px solid #334155; background: #0f172a; color: #fff; box-sizing: border-box; }
        button { width: 100%; border: 0; border-radius: 12px; padding: 12px 14px; background: #2563eb; color: #fff; font-weight: 600; cursor: pointer; }
        label { display: block; font-size: .92rem; color: #cbd5e1; }
        p { color: #94a3b8; }
    </style>
</head>
<body>
<div class="container">
    <form class="panel" method="post" action="/login" autocomplete="off">
        <h1>Sign in</h1>
        <p>Use your administrator account to access PHP-BindManager.</p>
        <input type="hidden" name="_csrf_token" value="<?= e($csrfToken ?? '') ?>">
        <label for="username">Username</label>
        <input id="username" name="username" type="text" required maxlength="64">
        <label for="password">Password</label>
        <input id="password" name="password" type="password" required maxlength="255">
        <button type="submit">Login</button>
    </form>
</div>
</body>
</html>
