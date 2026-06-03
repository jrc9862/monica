# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## tests/ directory

### Layout

```
tests/
  Unit/Domains/        # Mirror of app/Domains/ — one test per Service/ViewHelper
  Feature/Controllers/ # HTTP-level controller tests
  Feature/Auth/        # Authentication flows
  TestCase.php         # Base class with shared factory helpers
  ApiTestCase.php      # Base class for API endpoint tests
```

### TestCase helpers

`TestCase` provides methods to reduce boilerplate:

```php
$user    = $this->createUser();              // creates User + acts as Sanctum user
$admin   = $this->createAdministrator();
$account = $this->createAccount();
$vault   = $this->createVault($account);
$vault   = $this->createVaultUser($user, Vault::PERMISSION_EDIT);  // vault + pivot row
         = $this->setPermissionInVault($user, Vault::PERMISSION_MANAGE, $vault);
```

### Isolation strategy

All tests use `DatabaseTransactions` (wraps each test in a rolled-back transaction). Do **not** switch to `RefreshDatabase` — the test suite is designed around transaction isolation.

### Running a single test

```bash
vendor/bin/phpunit --filter "it_creates_a_contact"
# or by file:
vendor/bin/phpunit tests/Unit/Domains/Contact/ManageContact/Services/CreateContactTest.php
```

### Test database

Tests use a separate `testing` connection (SQLite by default, configured in `phpunit.xml`). The connection is called `testing` — match this in any factory or seeder call you add for test setup.

### Naming conventions

- Test methods are `snake_case` prefixed with `it_` and annotated `/** @test */`
- Service test class names match the service: `CreateContact` → `CreateContactTest`
- Mirror the domain path in `tests/Unit/Domains/` exactly
