# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## About

Monica is an open-source Personal Relationship Manager (PRM) built with Laravel 12, Inertia.js, and Vue 3. It lets users document their lives and relationships across isolated "vaults."

## Commands

### PHP

```bash
# Run all tests
vendor/bin/phpunit

# Run a single test file
vendor/bin/phpunit tests/Unit/Domains/Contact/ManageContact/Services/CreateContactTest.php

# Run a single test method
vendor/bin/phpunit --filter "it_creates_a_contact" tests/Unit/Domains/Contact/ManageContact/Services/CreateContactTest.php

# Static analysis
vendor/bin/phpstan
vendor/bin/psalm

# Format PHP (Laravel Pint, uses laravel preset)
vendor/bin/pint
```

### JavaScript

```bash
yarn dev          # Start Vite dev server
yarn build        # Build frontend + SSR bundle
yarn lint         # ESLint
yarn format       # Prettier
```

### Database (local SQLite dev setup)

```bash
php artisan migrate
php artisan migrate:fresh --seed

# Test DB (separate SQLite file)
php artisan migrate:fresh --database=testing
php artisan db:seed --database=testing
```

### Docker (production)

```bash
docker compose up -d --build --no-pull   # Build and start all services
docker compose exec app php artisan migrate --force
docker compose exec app php artisan storage:link
docker compose logs app --tail=50
```

## Architecture

### Domain-Driven Layout

All feature logic lives under `app/Domains/`, organized by three top-level domains:

- `Contact/` — everything that can happen to or from a contact
- `Settings/` — account-level administration
- `Vault/` — vault-level management (journals, calendar, files, etc.)

**Do not add feature-specific code outside `app/Domains/`.** Top-level `app/` folders (`Models/`, `Http/`, `Providers/`, etc.) are for cross-cutting infrastructure only.

Each domain is broken into `Manage<Feature>/` subdirectories (e.g., `ManageContact/`, `ManageNotes/`). Each `Manage*` folder contains:

| Subfolder          | Purpose                                               |
| ------------------ | ----------------------------------------------------- |
| `Services/`        | Business logic classes extending `BaseService`        |
| `Web/Controllers/` | Inertia controllers                                   |
| `Web/ViewHelpers/` | Data-shaping for Inertia responses                    |
| `Dav/`             | CardDAV/CalDAV export/import logic (where applicable) |

When adding a new feature, follow this pattern:

1. `Services/<Action>.php` — extends `BaseService`, declares `rules()` and `permissions()`
2. `Web/Controllers/<Thing>Controller.php` — thin; delegates to service + ViewHelper
3. `Web/ViewHelpers/<Thing>ViewHelper.php` — returns plain arrays; no Eloquent queries
4. Register routes in `routes/web.php`

### Service Pattern

Every write operation goes through a `Service` class. Services extend `BaseService` and implement `ServiceInterface` (two methods: `rules()` for validation, `permissions()` for authorization). Call them via:

```php
(new CreateContact)->execute($data);
```

`BaseService` resolves and validates `author_id`, `account_id`, `vault_id` automatically based on the declared `permissions()` array. After `validate()`, resolved objects are available as `$this->author`, `$this->vault`, `$this->contact`.

Permission strings for `permissions()`:

| String                                 | What it enforces          |
| -------------------------------------- | ------------------------- |
| `author_must_belong_to_account`        | Resolves `$this->author`  |
| `author_must_be_account_administrator` | Account admin check       |
| `vault_must_belong_to_account`         | Resolves `$this->vault`   |
| `author_must_be_vault_manager`         | Vault level ≤ MANAGE      |
| `author_must_be_vault_editor`          | Vault level ≤ EDIT        |
| `author_must_be_in_vault`              | Vault level ≤ VIEW        |
| `contact_must_belong_to_vault`         | Resolves `$this->contact` |

### ViewHelper Pattern

Controllers do not build response arrays directly. Dedicated `ViewHelper` classes (one per page/component) build the arrays passed to `Inertia::render()`. This keeps controllers thin and data-shaping testable in isolation.

### Frontend Stack

- **Vue 3** with `<script setup>` SFCs
- **Inertia.js** — no direct API calls from pages; data arrives via Inertia props, mutations go through Inertia form submissions or `router.visit()`
- **Tailwind CSS v4** — no `tailwind.config.js`; add custom tokens via CSS variables. Processed by `@tailwindcss/vite`
- **Ant Design Vue** components
- **Ziggy** — use `route('route.name', params)` helper (globally available) for named routes
- **laravel-vue-i18n** — wrap all user-facing strings in `trans()`
- **lucide-vue-next** for icons
- Flash messages: call `flash(message, level)` from `methods.js`; `Toaster.vue` listens via the event bus
- Pages live in `resources/js/Pages/`, shared components in `resources/js/Shared/`

### Key Models

The data hierarchy is: `Account → Vault → Contact`. Users belong to an Account and can have different permission levels (view/edit/manage) per Vault.

### API

A separate REST API is defined in `routes/api.php`. API resources live in `app/Http/Resources/`. The API is documented via Scribe (`knuckleswtf/scribe`).

### DAV (CardDAV/CalDAV)

CardDAV and CalDAV sync is provided via `monicahq/laravel-sabre`. DAV-specific logic lives in each domain's `Dav/` subfolder and `DavClient/` for the client-side sync.

## Testing Conventions

- Tests use `DatabaseTransactions` (rollback after each test — do **not** switch to `RefreshDatabase`)
- Test methods are `snake_case` prefixed with `it_` and annotated `/** @test */`
- Unit tests for services mirror the domain path: `tests/Unit/Domains/Contact/ManageContact/Services/CreateContactTest.php`
- Feature/controller tests live in `tests/Feature/Controllers/`
- Tests run against the `testing` database connection (separate SQLite file)

`TestCase` helpers:

```php
$user    = $this->createUser();
$admin   = $this->createAdministrator();
$account = $this->createAccount();
$vault   = $this->createVault($account);
$vault   = $this->createVaultUser($user, Vault::PERMISSION_EDIT);
         = $this->setPermissionInVault($user, Vault::PERMISSION_MANAGE, $vault);
```

## Code Style

- PHP: Laravel Pint (laravel preset). Run `vendor/bin/pint` before committing.
- JS/Vue: ESLint + Prettier. Husky runs lint-staged on commit.
- PHPStan at level 5; Psalm also runs in CI.
- `thecodingmachine/safe` is used — prefer `Safe\` function variants (e.g., `Safe\preg_replace`, `Safe\json_decode`) where available to avoid `false`-on-failure return types.
