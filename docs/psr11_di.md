---
sidebar_position: 4
---

# Dependency Injection

:::info Prerequisites
Before continuing, review the [PSR-11 Container](psr11.md) documentation and the [byjg/config Dependency Injection](https://github.com/byjg/config#dependency-injection) documentation.
:::

## Overview

Dependency Injection (DI) decouples your code from specific implementations, making it easier to swap dependencies based on environment or requirements.

## Example: Environment-Specific Cache

You might want caching enabled in production but disabled in development for easier debugging.

**Development** - `config/dev/01-infrastructure.php`:

```php
<?php

use ByJG\Cache\Psr16\BaseCacheEngine;
use ByJG\Cache\Psr16\NoCacheEngine;
use ByJG\Config\DependencyInjection as DI;

return [
    BaseCacheEngine::class => DI::bind(NoCacheEngine::class)
        ->toSingleton(),
];
```

**Production** - `config/prod/01-infrastructure.php`:

```php
<?php

use ByJG\Cache\Psr16\BaseCacheEngine;
use ByJG\Cache\Psr16\FileSystemCacheEngine;
use ByJG\Config\DependencyInjection as DI;

return [
    BaseCacheEngine::class => DI::bind(FileSystemCacheEngine::class)
        ->toSingleton(),
];
```

**Usage in Code:**

```php
<?php

use ByJG\Config\Config;
use ByJG\Cache\Psr16\BaseCacheEngine;

// Get the cache instance (implementation depends on APP_ENV)
$cache = Config::get(BaseCacheEngine::class);

// Use it the same way regardless of environment
$cache->set('key', 'value', 3600);
$value = $cache->get('key');
```

The application automatically returns the correct implementation based on the `APP_ENV` environment variable.

## Common DI Patterns

### Constructor Injection

```php
DummyService::class => DI::bind(DummyService::class)
    ->withInjectedConstructor()
    ->toSingleton(),
```

The container automatically injects dependencies defined in the constructor based on their type hints.

### Constructor with Parameters

```php
JwtWrapper::class => DI::bind(JwtWrapper::class)
    ->withConstructorArgs([
        Param::get('API_SERVER'),
        Param::get(JwtKeyInterface::class)
    ])
    ->toSingleton(),
```

Mix environment parameters and other dependencies.

### Factory Method

```php
DbDriverInterface::class => DI::bind(Factory::class)
    ->withFactoryMethod("getDbRelationalInstance", [
        Param::get('DBDRIVER_CONNECTION')
    ])
    ->toSingleton(),
```

Use a factory method instead of a constructor.

### Singleton vs Transient

```php
// Singleton - Same instance every time
MyService::class => DI::bind(MyService::class)->toSingleton(),

// Transient - New instance every time
MyService::class => DI::bind(MyService::class),
```

## Configuration Organization

Dependencies are organized by layer in numbered files:

- `01-infrastructure.php` - Database, Cache, Logging
- `02-security.php` - JWT, Authentication, User Management
- `03-api.php` - OpenAPI, Routes, Middleware
- `04-repositories.php` - Data access layer
- `05-services.php` - Business logic layer
- `06-external.php` - Email, SMS, external APIs

This organization makes it easy to find and modify related configurations.
