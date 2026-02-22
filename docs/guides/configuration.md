---
sidebar_position: 220
title: Configuration
---

# Configuration Deep Dive

Advanced configuration topics including environment inheritance, layered configs, credentials management, and multi-environment setup.

## Table of Contents

- [Overview](#overview)
- [Environment Hierarchy](#environment-hierarchy)
- [Configuration Layers](#configuration-layers)
- [Environment-Specific Configs](#environment-specific-configs)
- [Credentials Management](#credentials-management)
- [Configuration Bootstrap](#configuration-bootstrap)
- [Best Practices](#best-practices)

## Overview

The reference architecture uses a sophisticated configuration system with:

- **Environment Inheritance**: dev → test, dev → staging → prod
- **Layered Configuration**: Infrastructure → Security → API → Business → External
- **Automatic Loading**: Numbered files loaded in order
- **Environment Variables**: Support for `.env` files and environment variables

**Location**: `config/`

## Environment Hierarchy

### Environment Structure

**Location**: `config/ConfigBootstrap.php:21`

```
Development (dev)
├── Test (test)         # Inherits from dev
└── Staging (staging)   # Inherits from dev
    └── Production (prod)  # Inherits from staging
```

### How Inheritance Works

```php
// config/ConfigBootstrap.php
Config::definition()
    ->addEnvironment('dev')       // Base development environment
    ->addEnvironment('test', 'dev')   // Test inherits from dev
    ->addEnvironment('staging', 'dev') // Staging inherits from dev
    ->addEnvironment('prod', 'staging'); // Prod inherits from staging
```

### Inheritance Example

```
config/
├── dev/
│   └── 01-infrastructure/
│       └── 01-database.php     # Database: localhost
├── staging/
│   └── 01-infrastructure/
│       └── 01-database.php     # Override: staging-db.example.com
└── prod/
    └── 01-infrastructure/
        └── 01-database.php     # Override: prod-db.example.com
```

**Result**:
- `dev` uses `localhost`
- `test` inherits `localhost` from `dev`
- `staging` overrides with `staging-db.example.com`
- `prod` inherits `staging-db.example.com` and overrides with `prod-db.example.com`

## Configuration Layers

Configuration files are numbered to control loading order:

### Layer Structure

```
config/
└── {environment}/
    ├── 01-infrastructure/    # Database, cache, storage
    ├── 02-security/          # JWT, auth, permissions
    ├── 03-api/               # REST server, OpenAPI, routes
    ├── 04-repositories/      # Data access layer
    ├── 05-services/          # Business logic layer
    └── 06-external/          # Third-party APIs, mail
```

### Loading Order

Files are loaded in this order:
1. **Infrastructure Layer** (01-xxx)
2. **Security Layer** (02-xxx)
3. **API Layer** (03-xxx)
4. **Repository Layer** (04-xxx)
5. **Service Layer** (05-xxx)
6. **External Services Layer** (06-xxx)

Within each layer, files are loaded alphabetically.

## Environment-Specific Configs

### Development Environment

```php title="config/dev/01-infrastructure/01-database.php"
return [
    'DBDRIVER_CONNECTION' => fn() => 'mysql://root:password@localhost/myapp_dev'
];
```

### Test Environment

```php title="config/test/01-infrastructure/01-database.php"
return [
    // Override for test database
    'DBDRIVER_CONNECTION' => fn() => 'mysql://root:password@localhost/myapp_test'
];
```

### Production Environment

```php title="config/prod/01-infrastructure/01-database.php"
return [
    // Use environment variable
    'DBDRIVER_CONNECTION' => fn() => getenv('DATABASE_URL')
];
```

### Conditional Configuration

```php
// config/dev/01-infrastructure/02-cache.php
use ByJG\Config\Config;

return [
    'cache' => function() {
        if (Config::get('environment') === 'dev') {
            // No cache in development
            return new NullCache();
        }

        // Redis cache in other environments
        return new RedisCache(Config::get('redis.connection'));
    }
];
```

## Credentials Management

### Using .env Files

**File**: `.env` (not committed to git)

:::caution Keep `.env` out of version control
This file contains secrets — it is (and should remain) listed in `.gitignore`.
:::

```ini title=".env"
# Database
DATABASE_URL=mysql://user:pass@localhost/myapp

# JWT
JWT_SECRET=OFbOmC2VxlgQHNrBLa/wyj7/fFkgPnLpckbXMVuIU7Sqb3RTztNx3xzEYaoeA31JUpvBjkD7FRKBFGQ0+fnTig==

# External APIs
STRIPE_KEY=sk_live_xxxxxxxxxxxx
SENDGRID_API_KEY=SG.xxxxxxxxxxxx

# AWS
AWS_ACCESS_KEY=AKIAXXXXXXXXXXXXXXXX
AWS_SECRET_KEY=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

### Loading .env in Config

**File**: `config/dev/01-infrastructure/00-env.php`

```php
// Load .env file
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../../..');
$dotenv->load();

return [
    'DATABASE_URL' => fn() => $_ENV['DATABASE_URL'],
    'JWT_SECRET' => fn() => $_ENV['JWT_SECRET'],
    'STRIPE_KEY' => fn() => $_ENV['STRIPE_KEY']
];
```

### Credentials File (Alternative)

**File**: `config/credentials.php` (not committed to git)

```php
<?php

return [
    'dev' => [
        'database' => 'mysql://root:pass@localhost/myapp_dev',
        'jwt_secret' => 'dev-secret-key',
    ],
    'prod' => [
        'database' => getenv('DATABASE_URL'),
        'jwt_secret' => getenv('JWT_SECRET'),
    ]
];
```

**File**: `config/dev/01-infrastructure/01-database.php`

```php
$credentials = require __DIR__ . '/../../credentials.php';
$env = Config::definition()->getCurrentEnvironment();

return [
    'DBDRIVER_CONNECTION' => fn() => $credentials[$env]['database']
];
```

### Gitignore Setup

```gitignore
# .gitignore
.env
.env.*
config/credentials.php
config/*/credentials.php
```

## Configuration Bootstrap

### Bootstrap Process

**File**: `config/ConfigBootstrap.php`

```php
public static function init(?string $environment = null): void
{
    // 1. Create config definition
    Config::definition()
        ->addEnvironment('dev')
        ->addEnvironment('test', 'dev')
        ->addEnvironment('staging', 'dev')
        ->addEnvironment('prod', 'staging');

    // 2. Set current environment
    Config::definition()->setCurrentEnvironment($environment);

    // 3. Load configuration files
    $configDir = __DIR__ . '/' . $environment;

    // Files loaded in order:
    // - 01-infrastructure/
    // - 02-security/
    // - 03-api/
    // - 04-repositories/
    // - 05-services/
    // - 06-external/

    // 4. Register with dependency injection
    foreach ($configFiles as $file) {
        $configs = require $file;
        foreach ($configs as $key => $value) {
            Config::bind($key, $value);
        }
    }
}
```

### Custom Bootstrap

Create environment-specific bootstrap:

**File**: `config/prod/00-bootstrap.php`

```php
// Production-specific initialization
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', '/var/log/php/error.log');

// Set timezone
date_default_timezone_set('UTC');

// Enable OpCache
if (function_exists('opcache_reset')) {
    opcache_reset();
}

return [];
```

## Best Practices

### 1. Use Environment Variables for Secrets

```php
// Good - Environment variable
return [
    'JWT_SECRET' => fn() => getenv('JWT_SECRET')
];

// Bad - Hardcoded secret
return [
    'JWT_SECRET' => fn() => 'my-secret-key-123'
];
```

### 2. Layer Configuration Properly

```php
// Good - Layered by concern
config/dev/01-infrastructure/01-database.php
config/dev/02-security/01-jwt.php
config/dev/03-api/01-rest.php

// Bad - Mixed concerns
config/dev/app.php  // Everything in one file
```

### 3. Use Closures for Lazy Loading

```php
// Good - Lazy loaded
return [
    'database' => fn() => new DatabaseExecutor(Config::get('DBDRIVER_CONNECTION'))
];

// Bad - Eager loaded
return [
    'database' => new DatabaseExecutor(getenv('DATABASE_URL'))
];
```

### 4. Document Configuration Options

```php
/**
 * Database Configuration
 *
 * DBDRIVER_CONNECTION: Database connection string
 * Format: <schema>://user:pass@host/database
 * Example: mysql://root:password@localhost/myapp
 */
return [
    'DBDRIVER_CONNECTION' => fn() => getenv('DATABASE_URL')
        ?? 'mysql://root:password@localhost/myapp_dev'
];
```

### 5. Validate Required Config

```php
return [
    'JWT_SECRET' => function() {
        $secret = getenv('JWT_SECRET');

        if (empty($secret)) {
            throw new \RuntimeException('JWT_SECRET environment variable is required');
        }

        if (strlen(base64_decode($secret)) < 64) {
            throw new \RuntimeException('JWT_SECRET must be a base64-encoded string that decodes to at least 64 bytes.);
        }

        return $secret;
    }
];
```

### 6. Separate Public from Private Config

```
config/
├── dev/
│   ├── 01-infrastructure/
│   │   ├── 01-database.php      # Public (committed)
│   │   └── 01-database-credentials.php  # Private (ignored)
```

### 7. Use Defaults with Fallbacks

```php
return [
    'cache_ttl' => fn() => (int)(getenv('CACHE_TTL') ?: 3600),
    'api_timeout' => fn() => (int)(getenv('API_TIMEOUT') ?: 30),
    'max_upload_size' => fn() => getenv('MAX_UPLOAD_SIZE') ?: '10M'
];
```

### 8. Environment-Specific Caching

```php
// config/dev/01-infrastructure/02-cache.php
return [
    'cache' => fn() => new NullCache()  // No caching in dev
];

// config/prod/01-infrastructure/02-cache.php
return [
    'cache' => fn() => new RedisCache([
        'host' => getenv('REDIS_HOST'),
        'port' => getenv('REDIS_PORT')
    ])
];
```

## Related Documentation

- [Getting Started Guide](../getting-started/installation.md)
- [PSR-11 Dependency Injection](../concepts/dependency-injection.md)
- [JWT Configuration](jwt-advanced.md)
