<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e((string) $statusCode) ?> - Error</title>
    <style>
        body { margin: 0; font-family: Inter, system-ui, sans-serif; background: #020617; color: #e2e8f0; }
        .wrap { min-height: 100vh; display: grid; place-items: center; padding: 24px; }
        .box { width: 100%; max-width: 640px; background: #0f172a; border: 1px solid #1e293b; border-radius: 16px; padding: 28px; }
        h1 { margin-top: 0; }
        p { color: #cbd5e1; }
        a { color: #93c5fd; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="box">
        <h1><?= e((string) $statusCode) ?></h1>
        <p><?= e($message ?? 'An error occurred.') ?></p>
        <p><a href="/">Return to home</a></p>
    </div>
</div>
</body>
</html>
