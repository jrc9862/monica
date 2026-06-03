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

### Docker (Laravel Sail)

```bash
./vendor/bin/sail up
./vendor/bin/sail artisan migrate
```

## Architecture

### Domain-Driven Layout

All feature logic lives under `app/Domains/`, organized by three top-level domains:

- `Contact/` — everything that can happen to or from a contact
- `Settings/` — account-level administration
- `Vault/` — vault-level management (journals, calendar, files, etc.)

Each domain is broken into `Manage<Feature>/` subdirectories (e.g., `ManageContact/`, `ManageNotes/`). Each `Manage*` folder contains:

| Subfolder          | Purpose                                               |
| ------------------ | ----------------------------------------------------- |
| `Services/`        | Business logic classes extending `BaseService`        |
| `Web/Controllers/` | Inertia controllers                                   |
| `Web/ViewHelpers/` | Data-shaping for Inertia responses                    |
| `Dav/`             | CardDAV/CalDAV export/import logic (where applicable) |

### Service Pattern

Every write operation goes through a `Service` class. Services extend `BaseService` and implement `ServiceInterface` (two methods: `rules()` for validation, `permissions()` for authorization). Call them via:

```php
(new CreateContact)->execute($data);
```

`BaseService` resolves and validates `author_id`, `account_id`, `vault_id` automatically based on the declared `permissions()` array. Permission strings like `author_must_be_vault_editor` trigger built-in checks — no manual guard needed.

### ViewHelper Pattern

Controllers do not build response arrays directly. Instead, dedicated `ViewHelper` classes (one per page/component) build the arrays passed to `Inertia::render()`. This keeps controllers thin and keeps data-shaping testable in isolation.

### Frontend Stack

- **Vue 3** with `<script setup>` SFCs
- **Inertia.js** for server-driven SPA navigation (no separate API calls from the frontend; controllers return Inertia responses)
- **Tailwind CSS v4** + Ant Design Vue components
- **Ziggy** for named route generation in JS
- **laravel-vue-i18n** for translations
- Pages live in `resources/js/Pages/`, shared components in `resources/js/Components/`

### Key Models

The data hierarchy is: `Account → Vault → Contact`. Users belong to an Account and can have different permission levels (view/edit/manage) per Vault.

### API

A separate REST API is defined in `routes/api.php`. API resources live in `app/Http/Resources/`. The API is documented via Scribe (`knuckleswtf/scribe`).

### DAV (CardDAV/CalDAV)

CardDAV and CalDAV sync is provided via `monicahq/laravel-sabre`. DAV-specific logic lives in each domain's `Dav/` subfolder and `DavClient/` for the client-side sync.

## Testing Conventions

- Tests use `DatabaseTransactions` (rollback after each test, not `RefreshDatabase`)
- `TestCase` provides helpers: `createUser()`, `createVault()`, `createAccount()`, `setPermissionInVault()`
- Unit tests for services mirror the domain path: `tests/Unit/Domains/Contact/ManageContact/Services/CreateContactTest.php`
- Feature/controller tests live in `tests/Feature/Controllers/`

## Code Style

- PHP: Laravel Pint (laravel preset). Run `vendor/bin/pint` before committing.
- JS/Vue: ESLint + Prettier. Husky runs lint-staged on commit.
- PHPStan at level 5; Psalm also runs in CI.
- `thecodingmachine/safe` is used — prefer `Safe\` function variants (e.g., `Safe\preg_replace`) where available to avoid returning `false` on failure.
