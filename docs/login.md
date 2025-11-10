---
sidebar_position: 70
---

# Login Integration with JWT

This project includes user management, login, and JWT authentication out of the box.

For most use cases, you only need to configure the dependency injection settings. No code changes are required.

## User Table Structure

The database table created by the project is `users` with the following structure:

```sql
CREATE TABLE `users` (
    userid binary(16) DEFAULT (uuid_to_bin(uuid())) NOT NULL,
    `uuid` varchar(36) GENERATED ALWAYS AS (insert(insert(insert(insert(hex(`userid`),9,0,'-'),14,0,'-'),19,0,'-'),24,0,'-')) VIRTUAL,
    name varchar(50),
    email varchar(120),
    username varchar(20) not null,
    password char(40) not null,
    created datetime,
    admin enum('yes','no'),
    PRIMARY KEY (userid)
);
```

The `RestReferenceArchitecture\Model\User` class provides the model mapping for this table.

## Customize Field Mapping

If your database uses different field names, you can customize the mapping in `config/dev/02-security.php`:

```php
<?php

use RestReferenceArchitecture\Model\User;
use ByJG\Authenticate\Definition\UserDefinition;
use ByJG\Config\DependencyInjection as DI;

return [
    UserDefinition::class => DI::bind(UserDefinitionAlias::class)
        ->withConstructorArgs([
            'users',                         // Table name
            User::class,                     // User model class
            UserDefinition::LOGIN_IS_EMAIL,  // Login type
            [
                // Model property => Database column
                'userid'   => 'userid',
                'name'     => 'name',
                'email'    => 'email',
                'username' => 'username',
                'password' => 'password',
                'created'  => 'created',
                'admin'    => 'admin'
            ]
        ])
        ->toSingleton(),
];
```

For complete customization options, refer to the [byjg/authuser](https://github.com/byjg/authuser) documentation.

## Password Policy Configuration

Configure password requirements in `config/dev/02-security.php`:

```php
<?php

use ByJG\Authenticate\Definition\PasswordDefinition;
use ByJG\Config\DependencyInjection as DI;

return [
    PasswordDefinition::class => DI::bind(PasswordDefinition::class)
        ->withConstructorArgs([[
            PasswordDefinition::MINIMUM_CHARS => 12,      // Minimum password length
            PasswordDefinition::REQUIRE_UPPERCASE => 1,   // Number of uppercase letters
            PasswordDefinition::REQUIRE_LOWERCASE => 1,   // Number of lowercase letters
            PasswordDefinition::REQUIRE_SYMBOLS => 1,     // Number of special characters
            PasswordDefinition::REQUIRE_NUMBERS => 1,     // Number of digits
            PasswordDefinition::ALLOW_WHITESPACE => 0,    // Allow spaces (0=no, 1=yes)
            PasswordDefinition::ALLOW_SEQUENTIAL => 0,    // Allow sequences like "abc" or "123"
            PasswordDefinition::ALLOW_REPEATED => 0       // Allow repeated characters like "aaa"
        ]])
        ->toSingleton(),
];
```

## JWT Configuration

### Available Endpoints

The project provides the following JWT-related endpoints:

- **`POST /login`** - Generate a JWT token
- **`POST /refresh`** - Refresh an existing token
- **`GET /sampleprotected/ping`** - Example protected endpoint (requires authentication)

### Configure JWT Secret

:::warning Security
Never commit your JWT secret to version control. Each environment should have a unique secret.
:::

The JWT secret is configured in each environment's `credentials.env` file:

**`config/dev/credentials.env`**:
```env
JWT_SECRET=jwt_super_secret_key
```

**`config/prod/credentials.env`**:
```env
JWT_SECRET=your_production_secret_here_minimum_64_chars_recommended
```

The secret is automatically loaded in `config/dev/02-security.php`:

```php
<?php

use ByJG\JwtWrapper\JwtKeyInterface;
use ByJG\JwtWrapper\JwtHashHmacSecret;
use ByJG\Config\DependencyInjection as DI;
use ByJG\Config\Param;

return [
    JwtKeyInterface::class => DI::bind(JwtHashHmacSecret::class)
        ->withConstructorArgs([Param::get('JWT_SECRET')])
        ->toSingleton(),
];
```

### Testing Authentication

1. **Get a token:**
```bash
curl -X POST http://localhost:8080/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin@example.com","password":"!P4ssw0rdstr!"}'
```

2. **Use the token:**
```bash
curl -X GET http://localhost:8080/sampleprotected/ping \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## Using JWT in Your Code

### Protect Endpoints with Attributes

The authentication gate is provided by `ByJG\RestServer\Attributes\RequireAuthenticated`, so you do not need to build a custom attribute—just import it alongside your project-specific ones.

```php
<?php

use ByJG\RestServer\HttpRequest;
use ByJG\RestServer\HttpResponse;
use ByJG\RestServer\Attributes\RequireAuthenticated;
use RestReferenceArchitecture\Attributes\RequireRole;
use RestReferenceArchitecture\Model\User;

class MyProtectedRest
{
    // Require any authenticated user
    #[RequireAuthenticated]
    public function getProtectedData(HttpResponse $response, HttpRequest $request)
    {
        $response->write(['message' => 'You are authenticated!']);
    }

    // Require specific role
    #[RequireRole(User::ROLE_ADMIN)]
    public function getAdminData(HttpResponse $response, HttpRequest $request)
    {
        $response->write(['message' => 'You are an admin!']);
    }
}
```

### Access JWT Data

```php
<?php

use RestReferenceArchitecture\Util\JwtContext;

// Access user information captured by JwtContext::parseJwt()
$userId = JwtContext::getUserId();
$userName = JwtContext::getName();
$userRole = JwtContext::getRole();  // "admin" or "user"
```

:::tip Need the raw payload?
If you want the entire decoded token, call `HttpRequest::param('jwt.data')` inside your controller. The helper methods above already read from the same source while keeping your code cleaner.
:::

For more information, refer to the [byjg/jwt-wrapper](https://github.com/byjg/jwt-wrapper) documentation.
