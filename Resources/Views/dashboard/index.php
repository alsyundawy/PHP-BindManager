<!doctype html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - PHP-BindManager</title>
    <style>
        :root{--bg:#0b1120;--surface:#111827;--surface-2:#0f172a;--text:#e5e7eb;--muted:#94a3b8;--border:#1f2937;--primary:#2563eb;--ok:#16a34a;--warn:#f59e0b;--radius:18px}
        *{box-sizing:border-box}html,body{height:100%;margin:0;overflow:hidden;font-family:Inter,system-ui,sans-serif;background:var(--bg);color:var(--text)}
        .app{display:grid;grid-template-columns:280px 1fr;grid-template-rows:72px 1fr;height:100dvh}.sidebar{grid-row:1/-1;background:#0f172a;border-right:1px solid var(--border);padding:20px;overflow:auto}.header{display:flex;align-items:center;justify-content:space-between;padding:0 24px;border-bottom:1px solid var(--border);background:rgba(11,17,32,.85);backdrop-filter:blur(12px)}.main{padding:24px;overflow:auto}.brand{display:flex;gap:12px;align-items:center;margin-bottom:24px}.brand svg{width:28px;height:28px}.nav a{display:block;padding:12px 14px;border-radius:12px;color:#cbd5e1;text-decoration:none;margin-bottom:8px;background:transparent}.nav a.active,.nav a:hover{background:#111827;color:#fff}.grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px}.card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:18px}.kpi{font-size:2rem;font-weight:800;font-variant-numeric:tabular-nums lining-nums}.muted{color:var(--muted)}.row{display:flex;justify-content:space-between;align-items:center}.badge{display:inline-flex;padding:6px 10px;border-radius:999px;background:rgba(37,99,235,.18);color:#bfdbfe;font-size:.8rem}.ok{color:#86efac}.warn{color:#fcd34d}form{margin:0}@media (max-width: 992px){.app{grid-template-columns:1fr;grid-template-rows:64px auto 1fr}.sidebar{grid-row:auto;border-right:0;border-bottom:1px solid var(--border)}.grid{grid-template-columns:1fr}}
    </style>
</head>
<body>
<div class="app">
    <aside class="sidebar">
        <div class="brand">
            <svg viewBox="0 0 32 32" aria-label="PHP-BindManager logo" fill="none"><rect x="3" y="3" width="26" height="26" rx="8" stroke="currentColor" stroke-width="2"/><path d="M10 11h12M10 16h9M10 21h7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            <strong>PHP-BindManager</strong>
        </div>
        <nav class="nav" aria-label="Primary navigation">
            <a class="active" href="/dashboard">Dashboard</a>
            <a href="#zones">Zones</a>
            <a href="#records">Records</a>
            <a href="#security">Security</a>
            <a href="#settings">Settings</a>
        </nav>
    </aside>
    <header class="header">
        <div>
            <div class="muted">Signed in</div>
            <strong>User #<?= e((string) ($userId ?? '0')) ?> · <?= e((string) ($role ?? 'viewer')) ?></strong>
        </div>
        <form method="post" action="/logout">
            <input type="hidden" name="_csrf_token" value="<?= e($_SESSION['_csrf']['value'] ?? '') ?>">
            <button type="submit" style="background:#111827;border:1px solid #1f2937;color:#fff;border-radius:12px;padding:10px 14px">Logout</button>
        </form>
    </header>
    <main class="main">
        <div class="row" style="margin-bottom:18px"><div><h1 style="margin:0 0 6px;font-size:2rem">Operations dashboard</h1><div class="muted">Compact control panel for BIND9 management.</div></div><span class="badge">Phase 2.1</span></div>
        <section class="grid">
            <article class="card"><div class="muted">Zones</div><div class="kpi">128</div><div class="ok">+12 this week</div></article>
            <article class="card"><div class="muted">Pending checks</div><div class="kpi">07</div><div class="warn">Needs review</div></article>
            <article class="card"><div class="muted">Session state</div><div class="kpi">OK</div><div class="muted">Secure cookie + CSRF active</div></article>
        </section>
    </main>
</div>
</body>
</html>
