# Documentation Notes: Architecture and Engineering Guidelines

## 1. System Philosophy & Bounds

PHP-BindManager is built with an enterprise-first, authoritative-only architecture:

- **Authoritative DNS Focus**: Configured strictly as Primary/Secondary (Master/Slave) nameserver.
  Recursive resolution and RPZ bloat are intentionally excluded to eliminate cache poisoning
  and DNS amplification vectors.
- **Zero CDN Dependency**: All frontend libraries (Bootstrap 3.5.8, jQuery 3.7.1, Font Awesome 6.7.2)
  reside locally in `Public/assets/vendor/`. No external asset requests are made at runtime,
  ensuring offline functionality, air-gapped support, and zero third-party tracking.
- **Modern Responsive Design**: Inspired by Visual Subnet Calculator, utilizing semantic CSS custom
  properties, dual `data-theme` / `data-bs-theme` synchronization, glassmorphism headers, `100dvh`
  viewport units, and notch / punch-hole cutout safe area protections.

## 2. Cross-Device & Mobile Viewport Engineering

To ensure a seamless user experience across devices ranging from compact smartphones (Xiaomi, Redmi, Poco)
to 2K monitors:

- Viewport configuration: `<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">`.
- CSS Safe Area Insets: Standardized `env(safe-area-inset-top)`, `env(safe-area-inset-right)`,
  `env(safe-area-inset-bottom)`, and `env(safe-area-inset-left)` paddings.
- Viewport Height: `min-height: 100vh` fallback with `min-height: 100dvh` to prevent browser dynamic
  toolbar clipping.
- Tables & Data: Wrapped in `.pbm-table-wrap` / `.table-responsive` with `-webkit-overflow-scrolling: touch`
  to prevent layout breaking.

## 3. Strict Quality & Linter Compliance

The codebase adheres strictly to:

- **PHP 8.4+ / 8.5 compatibility**: `declare(strict_types=1);` in all PHP scripts and view templates.
- **PSR-12 Standard**: Validated through PHP_CodeSniffer and PHP-CS-Fixer.
- **Static Analysis**: PHPStan Level 8 and Psalm level 4 with zero errors.
- **Testing**: 100% PHPUnit pass rate with strict error reporting and zero coverage driver warnings.
- **Tooling Engine**: Trunk validation across Markdown, CSS, and PHP files.

## 4. IDE Integration & Diagnostic Suppression Best Practices

- **PHP Resolver Plugin (`stoildobreff.php-resolver`)**: The extension's `PHPWorkspaceDiagnosticsProvider`
  naively scans symbols and emits `Unresolved parent: ... (not found in workspace index)` because it lacks
  internal PHP core interface knowledge (e.g. `RuntimeException`, `Throwable`, `LogicException`). All
  diagnostic modules (`enableWorkspaceDiagnosticsModule`, `enableDeadCodeModule`, `enableUnusedImportModule`,
  `enableIndexHealthModule`) are disabled in `.vscode/settings.json`, and heavy directories (`vendor`,
  `node_modules`, `Storage`) are excluded.
- **Intelephense Undefined Variables**: The schema requires enum strings (`"on"`, `"local"`, `"off"`).
  Configured strictly as `"off"` in `.vscode/settings.json`.
- **Session Cookie Flags (SonarLint S3330)**: `session_set_cookie_params` uses explicit `secure: true` and
  `httponly: true` to guarantee compliance with static security analysis and zero-unencrypted transmission policy.
- **Strict Line Length ($\le 120$ characters)**: Enforced across all PHP controllers, services, repositories,
  HTML/PHP view templates, and unit tests.
