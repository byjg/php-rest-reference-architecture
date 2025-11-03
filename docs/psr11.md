---
sidebar_position: 3
---

# PSR-11 Container

The project uses the [PSR-11](https://www.php-fig.org/psr/psr-11/) container to manage dependencies.

## Configuration Structure

The configuration is organized by environment in the `config/{environment}/` folders. Each environment can have:

- **credentials.env** - Environment variables (database connections, API keys, JWT secrets, etc.)
- **Numbered PHP files** - Dependency injection bindings organized by layer:
  - `01-infrastructure.php` - Database, Cache, Logging
  - `02-security.php` - JWT, Authentication, CORS
  - `03-api.php` - OpenAPI routes, Middleware
  - `04-repositories.php` - Repository bindings
  - `05-services.php` - Service bindings
  - `06-external.php` - External services (Email, etc.)

You must set the `APP_ENV` environment variable to specify which environment to use.

### Example: credentials.env

```env
WEB_SERVER=localhost
DASH_SERVER=localhost
WEB_SCHEMA=http
API_SERVER=localhost
API_SCHEMA=http
DBDRIVER_CONNECTION=mysql://root:mysqlp455w0rd@mysql-container/mydb
EMAIL_CONNECTION=smtp://username:password@mail.example.com
JWT_SECRET=your_secret_key_here
CORS_SERVERS=.*
```

### Example: 01-infrastructure.php

```php
<?php

use ByJG\Cache\Psr16\BaseCacheEngine;
use ByJG\Cache\Psr16\NoCacheEngine;
use ByJG\Config\DependencyInjection as DI;
use ByJG\Config\Param;

return [
    BaseCacheEngine::class => DI::bind(NoCacheEngine::class)->toSingleton(),

    DbDriverInterface::class => DI::bind(Factory::class)
        ->withFactoryMethod("getDbRelationalInstance", [Param::get('DBDRIVER_CONNECTION')])
        ->toSingleton(),
];
```

The configuration is loaded by the [byjg/config](https://github.com/byjg/config) library.

## Get the configuration

Use the `Psr11::get()` method:

```php
Psr11::get('WEB_SERVER');
```

## Environment Hierarchy

The available environments are defined in the class `RestReferenceArchitecture\Psr11` in the method `environment()`.

The project has four environments with the following inheritance hierarchy:

```mermaid
graph TD
    dev[dev - Development]
    test[test - Testing]
    staging[staging - Staging]
    prod[prod - Production]

    dev --> test
    dev --> staging
    staging --> prod

    style dev fill:#90EE90
    style test fill:#87CEEB
    style staging fill:#FFD700
    style prod fill:#FF6347
```

**Inheritance Rules:**
- **test** inherits from **dev**
- **staging** inherits from **dev** (with caching enabled)
- **prod** inherits from **staging** and **dev** (with caching enabled)

Child environments override parent configurations. For example:
- `config/dev/credentials.env` defines base database connection
- `config/prod/credentials.env` overrides with production database connection
- `config/prod/01-infrastructure.php` overrides to use FileSystemCache instead of NoCache

You can modify the environment hierarchy in `src/Psr11.php`:

```php
public static function environment()
{
    $dev = new Environment('dev');
    $test = new Environment('test', [$dev]);
    $staging = new Environment('staging', [$dev], new FileSystemCacheEngine());
    $prod = new Environment('prod', [$staging, $dev], new FileSystemCacheEngine());

    if (is_null(self::$definition)) {
        self::$definition = (new Definition())
            ->addEnvironment($dev)
            ->addEnvironment($test)
            ->addEnvironment($staging)
            ->addEnvironment($prod);
    }

    return self::$definition;
}
```

---

**[← Back to Index](../README.md)** | **[Next: Dependency Injection →](psr11_di.md)**
