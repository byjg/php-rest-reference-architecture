---
sidebar_position: 250
---

# JWT Authentication - Advanced Guide

Complete guide to JWT (JSON Web Token) authentication including token generation, validation, refresh tokens, and custom claims.

## Table of Contents

- [Overview](#overview)
- [JwtContext Utility](#jwtcontext-utility)
- [Login Flow](#login-flow)
- [Token Structure](#token-structure)
- [Custom JWT Claims](#custom-jwt-claims)
- [Token Refresh](#token-refresh)
- [Protecting Endpoints](#protecting-endpoints)
- [Accessing User Information](#accessing-user-information)
- [Token Expiration](#token-expiration)
- [Security Best Practices](#security-best-practices)

## Overview

The reference architecture uses JWT tokens for stateless authentication. Tokens are:

- **Self-contained**: Contain user information and permissions
- **Stateless**: No server-side session storage required
- **Secure**: Cryptographically signed to prevent tampering
- **Expirable**: Time-limited validity

### Key Components

| Component              | Purpose                    | Location                         |
|------------------------|----------------------------|----------------------------------|
| `JwtContext`           | Token creation and parsing | `src/Util/JwtContext.php`        |
| `Login` REST           | Login and token endpoints  | `src/Rest/Login.php`             |
| `RequireAuthenticated` | Endpoint authentication    | ByJG\RestServer\Attributes       |
| `RequireRole`          | Role-based authorization   | `src/Attributes/RequireRole.php` |

## JwtContext Utility

The `JwtContext` class provides methods for creating tokens and extracting user information.

**Location**: `src/Util/JwtContext.php`

### Available Methods

```php
// Create a UserToken (token + claims) from a User instance or login string
JwtContext::createUserMetadata(User|string $user, string $password = ""): UserToken

// Create JWT token with custom data
JwtContext::createToken(array $properties): string

// Parse JWT from request (called automatically)
JwtContext::parseJwt(HttpRequest $request): void

// Extract user information from token
JwtContext::getUserId(): ?string
JwtContext::getRole(): ?string
JwtContext::getName(): ?string
```

## Login Flow

### Login Endpoint

**Location**: `src/Rest/Login.php:59`

```php
#[OA\Post(path: "/login", tags: ["Login"])]
#[ValidateRequest]
public function post(HttpResponse $response, HttpRequest $request)
{
    $json = ValidateRequest::getPayload();

    // AuthUser validates credentials and returns a token + claims
    $userToken = JwtContext::createUserMetadata($json["username"], $json["password"]);

    $response->getResponseBag()->setSerializationRule(SerializationRuleEnum::SingleObject);
    $response->write(['token' => $userToken->token]);
    $response->write(['data' => $userToken->data]);
}
```

### Client Login Request

```bash
POST /login
Content-Type: application/json

{
    "username": "john@example.com",
    "password": "secret123"
}
```

### Login Response

```json
{
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "data": {
        "userid": "550e8400-e29b-41d4-a716-446655440000",
        "name": "John Doe",
        "role": "admin"
    }
}
```

### Using the Token

```bash
GET /products
Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

## Token Structure

### Default JWT Payload

**Location**: `src/Util/JwtContext.php:24`

```php
public static function createUserMetadata(User|string $user, $password = ""): UserToken
{
    $usersService = Config::get(UsersService::class);
    $jwtWrapper = Config::get(JwtWrapper::class);
    $expires = 3600; // 1 hour access token
    $tokenFields = [
        UserField::Userid,
        UserField::Name,
        UserField::Role,
    ];

    return empty($password)
        ? $usersService->createInsecureAuthToken(
            login: $user,
            jwtWrapper: $jwtWrapper,
            expires: $expires,
            tokenUserFields: $tokenFields
        )
        : $usersService->createAuthToken(
            login: $user,
            password: $password,
            jwtWrapper: $jwtWrapper,
            expires: $expires,
            tokenUserFields: $tokenFields
        );
}
```

`UserToken::$data` is what ends up inside the JWT. Add or remove values by changing the `$tokenFields` array.

### Decoded Token Example

```json
{
    "userid": "550e8400-e29b-41d4-a716-446655440000",
    "name": "John Doe",
    "role": "admin",
    "iat": 1704067200,
    "exp": 1704672000
}
```

### Token Components

- **`userid`**: Unique user identifier (UUID)
- **`name`**: User's display name
- **`role`**: User's role (admin/user)
- **`iat`**: Issued At timestamp
- **`exp`**: Expiration timestamp

## Custom JWT Claims

### Adding Custom Claims

Extend `JwtContext` to add custom claims:

```php
<?php

namespace RestReferenceArchitecture\Util;

use ByJG\Authenticate\Enum\UserField;
use ByJG\Authenticate\Model\UserToken;
use ByJG\Authenticate\Service\UsersService;
use ByJG\Config\Config;
use ByJG\JwtWrapper\JwtWrapper;

class CustomJwtContext extends JwtContext
{
    public static function createUserMetadata(User|string $user, string $password = ""): UserToken
    {
        $usersService = Config::get(UsersService::class);
        $jwtWrapper = Config::get(JwtWrapper::class);
        $expires = 3600;
        $tokenFields = [
            UserField::Userid,
            UserField::Name,
            UserField::Role,
            UserField::Email,      // built-in extra claim
            'department',          // custom property (must exist in your model/properties)
        ];

        return empty($password)
            ? $usersService->createInsecureAuthToken(
                login: $user,
                jwtWrapper: $jwtWrapper,
                expires: $expires,
                tokenUserFields: $tokenFields
            )
            : $usersService->createAuthToken(
                login: $user,
                password: $password,
                jwtWrapper: $jwtWrapper,
                expires: $expires,
                tokenUserFields: $tokenFields
            );
    }

    // Add getter methods
    public static function getEmail(): ?string
    {
        return self::getRequestParam("email");
    }

    public static function getDepartment(): ?string
    {
        return self::getRequestParam("department");
    }

    public static function getPermissions(): ?array
    {
        $perms = self::getRequestParam("permissions");
        return $perms ? json_decode($perms, true) : null;
    }

    public static function getTenantId(): ?string
    {
        return self::getRequestParam("tenant_id");
    }
}
```

This approach copies the default implementation so you can tweak the `$tokenFields` array before AuthUser generates the token. Use `UserField` enum values for built-in columns (userid, name, email, etc.) and literal strings for custom fields exposed by your `User` model or `users_property` table.

### Update DI Configuration

Register your custom class in `config/dev/02-security.php` (or the equivalent file for each environment):

```php
use ByJG\Config\DependencyInjection as DI;
use RestReferenceArchitecture\Util\CustomJwtContext;
use RestReferenceArchitecture\Util\JwtContext;

return [
    JwtContext::class => DI::bind(CustomJwtContext::class)->toSingleton(),
];
```

### Using Custom Claims

```php
#[RequireAuthenticated]
public function getMyData(HttpResponse $response, HttpRequest $request): void
{
    $userId = CustomJwtContext::getUserId();
    $email = CustomJwtContext::getEmail();
    $department = CustomJwtContext::getDepartment();
    $permissions = CustomJwtContext::getPermissions();
    $tenantId = CustomJwtContext::getTenantId();

    $response->write([
        'userId' => $userId,
        'email' => $email,
        'department' => $department,
        'permissions' => $permissions,
        'tenantId' => $tenantId
    ]);
}
```

## Token Refresh

### Refresh Token Endpoint

**Location**: `src/Rest/Login.php:77`

```php
#[OA\Post(path: "/refreshtoken", tags: ["Login"])]
#[RequireAuthenticated]
public function refreshToken(HttpResponse $response, HttpRequest $request)
{
    $diff = ($request->param("jwt.exp") - time()) / 60;

    if ($diff > 5) {
        throw new Error401Exception("You only can refresh the token 5 minutes before expire");
    }

    /** @var UsersService $usersService */
    $usersService = Config::get(UsersService::class);
    $user = $usersService->getById(JwtContext::getUserId());

    $userToken = JwtContext::createUserMetadata($user);

    $response->getResponseBag()->setSerializationRule(SerializationRuleEnum::SingleObject);
    $response->write(['token' => $userToken->token]);
    $response->write(['data' => $userToken->data]);
}
```

### Client Refresh Request

```bash
POST /refreshtoken
Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

### Refresh Response

```json
{
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "data": {
        "userid": "550e8400-e29b-41d4-a716-446655440000",
        "name": "John Doe",
        "role": "admin"
    }
}
```

### Automatic Token Refresh

Implement client-side automatic refresh:

```javascript
// JavaScript example
let token = localStorage.getItem('jwt_token');
let refreshTimer;

async function refreshToken() {
    const response = await fetch('/refreshtoken', {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`
        }
    });

    const data = await response.json();
    token = data.token;
    localStorage.setItem('jwt_token', token);

    // Schedule next refresh (e.g., 1 hour before expiration)
    scheduleRefresh();
}

function scheduleRefresh() {
    // Refresh 1 hour before expiration (token valid for 7 days)
    const refreshIn = (6 * 24 * 60 * 60 * 1000); // 6 days in ms
    refreshTimer = setTimeout(refreshToken, refreshIn);
}

// Start refresh cycle
scheduleRefresh();
```

## Protecting Endpoints

### Require Authentication

```php
use RestReferenceArchitecture\Attributes\RequireAuthenticated;

#[OA\Get(path: "/profile", tags: ["User"])]
#[RequireAuthenticated]
public function getProfile(HttpResponse $response, HttpRequest $request): void
{
    // Only authenticated users can access
    $userId = JwtContext::getUserId();
    $name = JwtContext::getName();

    $response->write([
        'userId' => $userId,
        'name' => $name
    ]);
}
```

### Require Specific Role

```php
use RestReferenceArchitecture\Attributes\RequireRole;
use RestReferenceArchitecture\Model\User;

#[OA\Delete(path: "/users/{id}", tags: ["Admin"])]
#[RequireRole(User::ROLE_ADMIN)]
public function deleteUser(HttpResponse $response, HttpRequest $request): void
{
    // Only admins can access
    $id = $request->param('id');
    // Delete user logic...
}
```

### Multiple Authorization Levels

```php
// Public endpoint - No authentication
#[OA\Get(path: "/products", tags: ["Products"])]
public function listProducts(...) { }

// Authenticated - Any logged-in user
#[OA\Post(path: "/orders", tags: ["Orders"])]
#[RequireAuthenticated]
public function createOrder(...) { }

// Admin only
#[OA\Delete(path: "/products/{id}", tags: ["Products"])]
#[RequireRole(User::ROLE_ADMIN)]
public function deleteProduct(...) { }
```

## Accessing User Information

### In REST Controllers

```php
#[RequireAuthenticated]
public function getCurrentUser(HttpResponse $response, HttpRequest $request): void
{
    $userId = JwtContext::getUserId();
    $name = JwtContext::getName();
    $role = JwtContext::getRole();

    $response->write([
        'id' => $userId,
        'name' => $name,
        'role' => $role
    ]);
}
```

### In Services

```php
class OrderService extends BaseService
{
    public function createOrder(array $orderData): Order
    {
        // Get current user from JWT
        $userId = JwtContext::getUserId();

        // Add user to order
        $orderData['user_id'] = $userId;
        $orderData['created_by'] = JwtContext::getName();

        return $this->create($orderData);
    }

    public function listMyOrders(): array
    {
        $userId = JwtContext::getUserId();

        $query = $this->repository->listQuery(
            filter: [
                ['user_id = :user_id', ['user_id' => $userId]]
            ]
        );

        return $this->repository->getRepository()->getByQuery($query);
    }
}
```

### Checking Permissions

```php
class ProductService extends BaseService
{
    public function delete(int $productId): void
    {
        $role = JwtContext::getRole();

        // Business rule: Only admins can delete
        if ($role !== User::ROLE_ADMIN) {
            throw new Error403Exception('Only administrators can delete products');
        }

        // Additional check: users can only delete their own products
        $product = $this->getOrFail($productId);
        $userId = JwtContext::getUserId();

        if ($role !== User::ROLE_ADMIN && $product->getUserId() !== $userId) {
            throw new Error403Exception('You can only delete your own products');
        }

        parent::delete($productId);
    }
}
```

## Token Expiration

### Default Expiration

**Location**: `src/Util/JwtContext.php:56`

```php
public static function createToken(array $properties = []): mixed
{
    $jwt = Config::get(JwtWrapper::class);

    // Token valid for 7 days (in seconds)
    $expirationTime = 60 * 60 * 24 * 7;

    $jwtData = $jwt->createJwtData($properties, $expirationTime);
    return $jwt->generateToken($jwtData);
}
```

### Custom Expiration

```php
class CustomJwtContext extends JwtContext
{
    public static function createToken(
        array $properties = [],
        ?int $expirationSeconds = null
    ): mixed {
        $jwt = Config::get(JwtWrapper::class);

        // Default to 24 hours if not specified
        $expirationSeconds = $expirationSeconds ?? (60 * 60 * 24);

        $jwtData = $jwt->createJwtData($properties, $expirationSeconds);
        return $jwt->generateToken($jwtData);
    }

    // Short-lived token for sensitive operations
    public static function createShortLivedToken(array $properties): mixed
    {
        return self::createToken($properties, 60 * 15); // 15 minutes
    }

    // Long-lived token for remember-me
    public static function createLongLivedToken(array $properties): mixed
    {
        return self::createToken($properties, 60 * 60 * 24 * 30); // 30 days
    }
}
```

### Handling Expired Tokens

```php
try {
    // Token validation happens automatically in RequireAuthenticated
    $this->assertRequest($request);
} catch (Error401Exception $e) {
    // Token expired or invalid
    if (strpos($e->getMessage(), 'expired') !== false) {
        // Redirect to refresh token endpoint or login
        return ['error' => 'Token expired', 'action' => 'refresh'];
    }

    return ['error' => 'Unauthorized'];
}
```

## Security Best Practices

### 1. Store JWT Secret Securely

Configure in `.env` or environment variables:

```bash
# .env
JWT_SECRET=your-super-secret-key-min-32-characters
```

Never commit secrets to version control.

### 2. Use HTTPS Only

Always transmit tokens over HTTPS:

```php
// In production environment config
if (Config::get('environment') === 'prod') {
    if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
        throw new Error400Exception('HTTPS required');
    }
}
```

### 3. Validate Token on Every Request

The `RequireAuthenticated` attribute handles this automatically:

```php
#[RequireAuthenticated]  // Validates token automatically
public function protectedEndpoint(...) { }
```

### 4. Short Token Expiration

Use shorter expiration times for sensitive operations:

```php
$metadata = [
    'userid' => JwtContext::getUserId(),
    'name' => JwtContext::getName(),
    'role' => JwtContext::getRole(),
];

// Regular operations: 7 days
$regularToken = JwtContext::createToken($metadata);

// Admin operations: 1 hour
$adminToken = CustomJwtContext::createToken($metadata, 60 * 60);

// Financial operations: 15 minutes
$financialToken = CustomJwtContext::createToken($metadata, 60 * 15);
```

### 5. Implement Token Blacklist (Optional)

For logout or compromised tokens:

```php
class TokenBlacklist
{
    protected CacheInterface $cache;

    public function blacklist(string $token, int $expirationTime): void
    {
        // Store token in cache until it would expire anyway
        $this->cache->set("blacklist:{$token}", true, $expirationTime);
    }

    public function isBlacklisted(string $token): bool
    {
        return $this->cache->has("blacklist:{$token}");
    }
}

// Custom authentication attribute
class RequireValidToken extends RequireAuthenticated
{
    public function processBefore(HttpResponse $response, HttpRequest $request): void
    {
        parent::processBefore($response, $request);

        $token = $this->extractToken($request);
        $blacklist = Config::get(TokenBlacklist::class);

        if ($blacklist->isBlacklisted($token)) {
            throw new Error401Exception('Token has been revoked');
        }
    }
}
```

### 6. Rotate Tokens Regularly

Encourage clients to refresh tokens:

```php
class Login
{
    public function post(HttpResponse $response, HttpRequest $request)
    {
        $payload = ValidateRequest::getPayload();
        $userToken = JwtContext::createUserMetadata($payload['username'], $payload['password']);

        $response->write([
            'token' => $userToken->token,
            'expires_in' => 60 * 60 * 24 * 7, // 7 days
            'refresh_after' => 60 * 60 * 24 * 3, // Suggest refresh after 3 days
            'data' => $userToken->data
        ]);
    }
}
```

### 7. Validate User Still Exists

Check user validity on critical operations:

```php
#[RequireAuthenticated]
public function deleteAccount(HttpResponse $response, HttpRequest $request): void
{
    $userId = JwtContext::getUserId();

    // Verify user still exists and is active
    $usersService = Config::get(UsersService::class);
    $user = $usersService->getById($userId);

    if ($user === null || $user->getDeletedAt() !== null) {
        throw new Error401Exception('Account is no longer active');
    }

    // Proceed with deletion...
}
```

### 8. Rate Limit Authentication Endpoints

```php
use RestReferenceArchitecture\Attributes\RateLimit;

#[OA\Post(path: "/login", tags: ["Login"])]
#[RateLimit(maxRequests: 5, windowSeconds: 60)]  // 5 attempts per minute
#[ValidateRequest]
public function post(HttpResponse $response, HttpRequest $request)
{
    // Login logic...
}
```

### 9. Log Authentication Events

```php
public function post(HttpResponse $response, HttpRequest $request)
{
    $json = ValidateRequest::getPayload();
    $usersService = Config::get(UsersService::class);

    try {
        $user = $usersService->isValidUser($json["username"], $json["password"]);

        if ($user === null) {
            throw new Error401Exception('Invalid credentials');
        }

        // Log successful login
        $logger->info('User logged in', [
            'user_id' => $user->getUserid(),
            'username' => $json["username"],
            'ip' => $_SERVER['REMOTE_ADDR']
        ]);

        // Generate token...

    } catch (Error401Exception $e) {
        // Log failed login attempt
        $logger->warning('Failed login attempt', [
            'username' => $json["username"],
            'ip' => $_SERVER['REMOTE_ADDR']
        ]);

        throw $e;
    }
}
```

### 10. Multi-Factor Authentication (Optional)

```php
public function post(HttpResponse $response, HttpRequest $request)
{
    $json = ValidateRequest::getPayload();
    $usersService = Config::get(UsersService::class);
    $user = $usersService->isValidUser($json["username"], $json["password"]);

    if ($user === null) {
        throw new Error401Exception('Invalid credentials');
    }

    // Check if MFA is enabled for user via properties table
    if ($usersService->hasProperty($user->getUserid(), 'mfa_enabled', 'yes')) {
        $tempToken = $this->createTempToken($user);

        $response->write([
            'mfa_required' => true,
            'temp_token' => $tempToken,
            'message' => 'Please provide MFA code'
        ]);
        return;
    }

    $userToken = JwtContext::createUserMetadata($user);
    $response->write(['token' => $userToken->token, 'data' => $userToken->data]);
}

#[OA\Post(path: "/login/verify-mfa", tags: ["Login"])]
public function verifyMfa(HttpResponse $response, HttpRequest $request)
{
    $json = ValidateRequest::getPayload();

    // Verify MFA code
    if ($this->verifyMfaCode($json['temp_token'], $json['mfa_code'])) {
        $user = $this->getUserFromTempToken($json['temp_token']);
        $userToken = JwtContext::createUserMetadata($user);

        $response->write(['token' => $userToken->token, 'data' => $userToken->data]);
    } else {
        throw new Error401Exception('Invalid MFA code');
    }
}
```

## Related Documentation

- [Attributes System](attributes.md) - RequireAuthenticated and RequireRole
- [REST API Development](rest.md) - Protecting endpoints
- [Error Handling](error-handling.md) - Authentication errors
- [Testing Guide](testing-guide.md) - Testing authentication
- [Configuration](configuration-advanced.md) - JWT configuration
