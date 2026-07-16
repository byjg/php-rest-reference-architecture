# Changelog - Version 6.0

## Overview

Version 6.0 is a major release that brings significant improvements to the PHP REST Reference Architecture, including PHP 8.3+ requirement, enhanced dependency injection, improved authentication handling, and comprehensive refactoring for better code quality and maintainability.

## New Features

### PHP 8.4 Support
- Added support for PHP 8.4 alongside PHP 8.3
- Updated Dockerfile to use the latest PHP base image
- Added `#[Override]` attributes to method definitions for better type safety

### ActiveRecord Pattern Support
- Implemented `DummyActiveRecord` with full CRUD functionality
- Added `getByName` method for querying records by name
- Integrated UUID-based attributes for ActiveRecord models

### Enhanced Migration System
- Replaced native CLI calls with Migration API for better programmatic control
- Added support for additional migration commands and options
- Support for multiple database drivers (MySQL, PostgreSQL, SQLite, etc.)
- Improved migration command usage help and error handling

### Configuration Improvements
- Migrated configuration bootstrap from `bootstrap.php` to `config/ConfigBootstrap.php` for better modularity
- Enhanced `loadConfigFromJson` to support multi-location configuration loading with priority order
- Support for multiple database schemas with improved flexibility
- Added backward compatibility with legacy `mysql_connection` configuration

### Unattended Setup
- Added unattended mode to PostCreateScript with JSON configuration support
- Support for `setup.json` configuration file for automated project initialization
- Added CI workflow to test unattended setup
- Option to remove example files and clean configs during setup

### Scriptify Integration
- Added `scriptify terminal` support with preload script and environment variable configuration
- Enhanced documentation with helper examples and customization options
- Added `uuid_to_bin` helper function
- Reusable helper scripts for scriptify

### API Testing Console
- Added interactive API Test Console to `index.html` for testing public and protected endpoints
- Updated database schema to support larger field values
- Cleanup logic in PostCreateScript to optionally remove API test examples

### Documentation Enhancements
- Added complete HTML documentation file for PHP REST Reference Architecture
- Added detailed PHP component dependency documentation
- Enhanced documentation structure with standardized sidebar positions
- Improved consistency across all documentation files

### Service Layer Architecture
- Introduced service layer for entity handling with improved separation of concerns
- Refactored authentication and user management with `UsersService` and `UsersRepository`
- Enhanced JWT functionality with role-based authorization

### Environment and Deployment
- Consolidated `docker-compose-dev.yml` into `docker-compose.yml` for streamlined setup
- Added `.env.sample` creation in PostCreateScript with local database examples
- Updated GitHub workflow with improved test file handling and CI environment support
- Set git configuration to local scope in PostCreateScript

## Breaking Changes

| Before | After | Description |
|--------|-------|-------------|
| `Psr11::container()->get()` | `Config::get()` | Replaced `Psr11` with `Config` for dependency injection to simplify usage and centralize environment initialization |
| `JwtContext::requireAuthenticated()` | `#[RequireAuthenticated]` attribute | Removed method in favor of PHP attributes for cleaner authentication logic |
| `JwtContext::requireRole()` | `#[RequireRole]` attribute | Removed method in favor of PHP attributes for role-based authorization |
| `DbDriverInterface` | `DatabaseExecutor` | Updated to match the latest byjg/micro-orm library version for consistency |
| `UsersDBDataset` | `UsersRepository` | Replaced custom dataset implementation with standard repository pattern |
| `UserDefinition` (custom class) | `UsersService` | Simplified user management with service layer architecture |
| `bootstrap.php` | `config/ConfigBootstrap.php` | Moved configuration bootstrap for better modularity |
| `docker-compose-dev.yml` | `docker-compose.yml` | Consolidated docker compose files for simpler environment setup |
| PHP 8.2 and earlier | PHP 8.3+ | Minimum PHP version requirement increased to 8.3 |
| `--yes` flag in migration reset | Removed | Removed obsolete flag from migration commands |
| `getByEmail()` method | `get()` with email field | Refactored for improved flexibility and consistency |
| Legacy `mysql_connection` config | Multi-schema configuration | Enhanced database configuration format (backward compatible) |

## Dependency Updates

All ByJG components have been updated to version 6.0:
- `byjg/config: ^6.0`
- `byjg/anydataset-db: ^6.0`
- `byjg/micro-orm: ^6.0`
- `byjg/authuser: ^6.0`
- `byjg/mailwrapper: ^6.0`
- `byjg/restserver: ^6.0`
- `byjg/swagger-test: ^6.0`
- `byjg/migration: ^6.0`
- `byjg/scriptify: ^6.0`
- `byjg/shortid: ^6.0`
- `byjg/jinja-php: ^6.0`

External dependencies:
- `zircote/swagger-php: ^4.7|^5.2` (updated to support version 5.x)
- `phpunit/phpunit: ^10.5|^11.5` (updated)
- `vimeo/psalm: ^5.9|^6.13` (updated)

## Bug Fixes

- Fixed GitHub workflow to use `localtest` database matching test environment config
- Fixed index.html rendering issues
- Fixed file permissions in post-create script
- Fixed grammar, formatting, and clarity issues in documentation
- Fixed UUID keyGen handling
- Simplified `save` method in `BaseRepository` by removing redundant primary key handling logic
- Excluded timestamp fields from dummy test data generation logic
- Fixed OpenAPI request parsing logic with improved validation
- Enhanced error validation in JWT handling

## Upgrade Path from 5.x to 6.x

### Step 1: Update PHP Version
Ensure your environment is running PHP 8.3 or later:
```bash
php -v  # Should show PHP 8.3.0 or higher
```

### Step 2: Update Dependencies
Update your `composer.json` to use version 6.0 dependencies:
```bash
composer update
```

### Step 3: Replace Psr11 with Config
Replace all instances of `Psr11` with `Config`:

**Before:**
```php
use RestReferenceArchitecture\Psr11;

$service = Psr11::container()->get(SomeService::class);
```

**After:**
```php
use RestReferenceArchitecture\Config;

$service = Config::get(SomeService::class);
```

### Step 4: Update JWT Authentication
Replace `JwtContext` method calls with PHP attributes:

**Before:**
```php
public function myMethod(Request $request, Response $response)
{
    JwtContext::requireAuthenticated();
    JwtContext::requireRole(['admin']);
    // ... rest of code
}
```

**After:**

```php
use RestReferenceArchitecture\Attribute\RequireAuthenticated;
use RestReferenceArchitecture\Attribute\RequireRole;

#[RequireAuthenticated]
#[RequireRole(['admin'])]
public function myMethod(Request $request, Response $response)
{
    // ... rest of code
}
```

### Step 5: Update Database References
Replace `DbDriverInterface` with `DatabaseExecutor`:

**Before:**
```php
use ByJG\AnyDataset\Db\DbDriverInterface;

public function __construct(DbDriverInterface $db)
{
    // ...
}
```

**After:**
```php
use ByJG\MicroOrm\DatabaseExecutor;

public function __construct(DatabaseExecutor $db)
{
    // ...
}
```

### Step 6: Update Bootstrap Configuration
If you have custom bootstrap code, migrate it from `bootstrap.php` to `config/ConfigBootstrap.php`:

**Before:**
```php
// bootstrap.php
require_once __DIR__ . '/vendor/autoload.php';
// Custom initialization
```

**After:**
```php
// config/ConfigBootstrap.php
<?php
namespace RestReferenceArchitecture;

class ConfigBootstrap
{
    public static function bootstrap()
    {
        // Custom initialization
    }
}
```

### Step 7: Update Docker Compose References
Replace references to `docker-compose-dev.yml` with `docker-compose.yml`:

**Before:**
```bash
docker compose -f docker-compose-dev.yml up -d
```

**After:**
```bash
docker compose -f docker-compose.yml up -d
# Or use the composer script:
composer run up-local-dev
```

### Step 8: Update User Management (if customized)
If you customized user management, migrate from `UsersDBDataset` to the new repository pattern:

**Before:**
```php
$usersDataset = new UsersDBDataset(/* ... */);
```

**After:**
```php
use RestReferenceArchitecture\Service\UsersService;
use RestReferenceArchitecture\Repository\UsersRepository;

$usersRepository = new UsersRepository();
$usersService = new UsersService($usersRepository);
```

### Step 9: Update Repository Methods
Replace specific getter methods with the generic `get()` method:

**Before:**
```php
$user = $repository->getByEmail($email);
```

**After:**
```php
$user = $repository->get(['email' => $email]);
```

### Step 10: Update Migration Commands
Remove the `--yes` flag from migration reset commands:

**Before:**
```bash
composer migrate reset --yes
```

**After:**
```bash
composer migrate reset
```

### Step 11: Run Psalm and PHPUnit
After making all changes, run static analysis and tests:
```bash
composer psalm
composer test
```

### Step 12: Review Configuration Format
If you use database configuration files, consider migrating to the new multi-schema format. The legacy format is still supported but the new format provides more flexibility:

**Legacy format (still supported):**
```php
return [
    'mysql_connection' => 'mysql://user:pass@localhost/db'
];
```

**New format (recommended):**
```php
return [
    'databases' => [
        'default' => 'mysql://user:pass@localhost/db',
        'reporting' => 'mysql://user:pass@localhost/reporting'
    ]
];
```

## Additional Notes

- All documentation has been updated to reflect the new patterns and APIs
- The project now includes comprehensive HTML documentation
- OpenAPI documentation generation is now part of the post-create script
- Better support for UUID-based primary keys with `UuidSeedGenerator`
- Enhanced type declarations and validation throughout the codebase
- Improved code generation templates for REST endpoints, repositories, and models
- Better error handling and validation across the application

## Resources

- [Complete dependency graph documentation](docs/)
- [Getting Started Guide](docs/getting-started/installation.md)
- [Unattended Setup Documentation](docs/getting-started/unattended-setup.md)
- [Service Layer Documentation](docs/guides/services.md)
- [ORM Documentation](docs/guides/orm.md)
- [Scriptify Documentation](docs/reference/scriptify.md)
