# PHP-BindManager: Production Implementation Tasks

## Phase 1: Core Framework & Parameter Routing

- [x] Update `App/Http/Router.php` to support `{param}` route patterns and `Router::json()` helper
- [x] Add `Tests/Unit/Http/RouterParameterTest.php` for parameterized routing
- [x] Register `Routes/api.php` in `App/Application.php`
- [x] Fix Intelephense / SonarLint warnings in `App/Application.php`

## Phase 2: Domain Services & Repositories

- [x] Add `delete(int $id)` method to `App/Repositories/Dns/ZoneRepository.php`
- [x] Add `all()` and `find(int $id)` methods to `App/Repositories/Dns/RecordRepository.php`
- [x] Implement `deploy(int $zoneId)` in `App/Services/Dns/ZoneFileService.php`

## Phase 3: Route Handlers (Web, DNS, System, API)

- [x] Fix PHPStan and Psalm diagnostics in `Routes/web.php`
- [x] Update `Routes/web.php` dashboard route to supply recent zones list
- [x] Expand `Routes/dns.php` with zone show, deploy, delete and record create, delete
- [x] Expand `Routes/system.php` with system stats and operations
- [x] Implement `Routes/api.php` with full v1 REST API endpoints

## Phase 4: Views & UI Templates

- [x] Fix line length warnings in `Resources/Views/zones/create.php` and `index.php`
- [x] Create `Resources/Views/zones/show.php` (zone details, records, deploy, raw export)
- [x] Update `Resources/Views/records/index.php` with zone filter and delete buttons
- [x] Create `Resources/Views/records/create.php` record creation form
- [x] Update `Resources/Views/dashboard/index.php` to render real recent zones
- [x] Ensure `Resources/Views/errors/generic.php` is styled and error-free

## Phase 5: Linting, Quality Assurance & Documentation

- [x] Update `psalm.xml` issue handlers for `ForbiddenCode` and `PossiblyUnusedReturnValue`
- [x] Fix line-length (MD013) and emphasis style (MD049) in `README.md`
- [x] Run `composer php-cs-fixer` to format all modified files
- [x] Run `composer test` and add integration tests for zone/record workflows
- [x] Run `composer phpstan`, `composer psalm`, `composer phpcs` and ensure zero errors
