# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## app/ directory

### Top-level vs. domain code

`app/` contains two kinds of PHP:

- **Cross-cutting infrastructure** (direct children): `Models/`, `Services/BaseService.php`, `Http/`, `Providers/`, `Actions/`, `Helpers/`, `Interfaces/`, `Listeners/`, `Mail/`, `Notifications/`, `Policies/`, `Traits/`
- **Feature business logic** lives exclusively in `Domains/` — do not add feature-specific code outside of that tree

### Adding a new feature

Follow the existing shape of any `Manage<Feature>/` folder:

1. `Services/<Action>.php` — extends `BaseService`, declares `rules()` and `permissions()`
2. `Web/Controllers/<Thing>Controller.php` — thin; delegates to service + ViewHelper
3. `Web/ViewHelpers/<Thing>ViewHelper.php` — returns plain arrays; no Eloquent queries in the View layer
4. Register routes in `routes/web.php`

### Permission strings (BaseService)

Use these strings verbatim in `permissions()` — they trigger automatic resolution/validation:

| String                                 | What it enforces                                         |
| -------------------------------------- | -------------------------------------------------------- |
| `author_must_belong_to_account`        | Resolves `$this->author` from `author_id` + `account_id` |
| `author_must_be_account_administrator` | `$this->author->is_account_administrator`                |
| `vault_must_belong_to_account`         | Resolves `$this->vault`                                  |
| `author_must_be_vault_manager`         | Vault permission level ≤ MANAGE                          |
| `author_must_be_vault_editor`          | Vault permission level ≤ EDIT                            |
| `author_must_be_in_vault`              | Vault permission level ≤ VIEW                            |
| `contact_must_belong_to_vault`         | Resolves `$this->contact`                                |
| `group_must_belong_to_vault`           | Resolves `$this->group`                                  |

Dependencies are enforced automatically (e.g., `author_must_be_vault_editor` requires both `vault_must_belong_to_account` and `author_must_belong_to_account` also be declared).

### Safe functions

`thecodingmachine/safe` is a project dependency. Use `Safe\` prefixed wrappers (e.g. `Safe\preg_replace`, `Safe\json_decode`) instead of native PHP functions where available, to avoid `false`-on-failure return types that PHPStan flags.
