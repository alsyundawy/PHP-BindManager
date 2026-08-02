# Fase 3 — Frontend & Theme System

Implemented frontend foundation with responsive dashboard layout, light/dark/auto theme persistence, sidebar, navbar, KPI cards, recent zones table, system health widgets, and minified runtime assets.

## Asset strategy
- Bootstrap 5.3.8 and Tailwind CSS 4.x remain documented as integration targets; the dashboard uses a small custom CSS layer to avoid loading unused framework payloads. Bootstrap 5.3.8 is currently the documented Bootstrap 5.3 release.
- Critical CSS is preloaded. JavaScript is deferred. Non-critical images can use `data-src` lazy loading handled by `app.min.js`.
- Theme preference is stored in localStorage with a cookie fallback.
- CSS uses responsive breakpoints for mobile, tablet, desktop, and ultrawide layouts.

## Integration
Copy the files into the repository root, then update the dashboard route to render `Resources/Views/dashboard/index.php`.
