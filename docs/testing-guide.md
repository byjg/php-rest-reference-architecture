---
sidebar_position: 230
---

# Complete Testing Guide

This guide covers all aspects of testing your REST API, from unit tests to integration tests, using FakeApiRequester for in-process API testing.

## Table of Contents

- [Overview](#overview)
- [Test Structure](#test-structure)
- [FakeApiRequester](#fakeapirequester)
- [Writing API Tests](#writing-api-tests)
- [Testing Authentication](#testing-authentication)
- [Testing Authorization](#testing-authorization)
- [Testing Validation](#testing-validation)
- [Testing CRUD Operations](#testing-crud-operations)
- [Unit Testing Services](#unit-testing-services)
- [Test Data Management](#test-data-management)
- [Best Practices](#best-practices)

## Overview

The reference architecture provides a complete testing framework that allows you to test your API **without** running a web server.

### Testing Approach

- **Integration Tests**: Test complete API endpoints using `FakeApiRequester`
- **Unit Tests**: Test services and business logic in isolation
- **Schema Validation**: Automatically validate responses against OpenAPI schema
- **In-Process**: No web server required, tests run directly in PHPUnit

### Key Components

| Component          | Purpose                  | Location                         |
|--------------------|--------------------------|----------------------------------|
| `FakeApiRequester` | In-process API testing   | `src/Util/FakeApiRequester.php`  |
| `BaseApiTestCase`  | Base class for API tests | `tests/Rest/BaseApiTestCase.php` |
| `Credentials`      | Test user credentials    | `tests/Rest/Credentials.php`     |

## Test Structure

### Directory Layout

```
tests/
└── Rest/
    ├── BaseApiTestCase.php     # Base test case with schema + DB reset
    ├── Credentials.php         # Helper for authenticating test users
    ├── DummyTest.php           # Repository pattern example CRUD tests
    ├── DummyHexTest.php        # Hex/UUID example
    ├── LoginTest.php           # Authentication flow
    └── ... (add your own files here)
```

:::info Want unit tests?
Add additional directories (e.g., `tests/Service`) as needed—PHPUnit's configuration already looks at the whole `tests/` tree.
:::

### Running Tests

```bash
# Run all tests using the composer script
APP_ENV=test composer run test

# Run a specific test file
APP_ENV=test ./vendor/bin/phpunit tests/Rest/DummyTest.php

# Run a single test method
APP_ENV=test ./vendor/bin/phpunit --filter testFullCrud tests/Rest/DummyTest.php

# Generate coverage (optional)
APP_ENV=test ./vendor/bin/phpunit --coverage-html coverage/
```

## FakeApiRequester

The `FakeApiRequester` class enables in-process API testing without a web server.

**Location**: `src/Util/FakeApiRequester.php`

### How It Works

1. **Creates PSR-7 Request**: Builds HTTP request object
2. **Routes to Controller**: Uses OpenAPI routing
3. **Executes Middleware**: Applies JWT authentication, validation
4. **Returns PSR-7 Response**: Returns HTTP response object
5. **Validates Schema**: Checks response against OpenAPI schema

### Basic Usage

```php
use RestReferenceArchitecture\Util\FakeApiRequester;

$request = (new FakeApiRequester())
    ->withPsr7Request($this->getPsr7Request())
    ->withMethod('GET')
    ->withPath('/dummy/1')
    ->withRequestHeader(['Authorization' => 'Bearer ' . $token])
    ->expectStatus(200);

$response = $this->sendRequest($request);
$data = json_decode($response->getBody()->getContents(), true);
```

### FakeApiRequester Methods

```php
// HTTP Method & Path
->withMethod('GET')             // GET, POST, PUT, DELETE, PATCH
->withPath('/api/products')     // API endpoint path

// Request Body
->withRequestBody(json_encode(['name' => 'Product']))
->withRequestBody('<xml>...</xml>')

// Headers
->withRequestHeader(['Authorization' => 'Bearer token'])
->withRequestHeader(['Content-Type' => 'application/json'])

// Query Parameters
->withQuery(['page' => 2, 'size' => 50])

// Expected Response
->expectStatus(200)             // Assert HTTP status code
->expectJsonContains(['name' => 'Product'])
```

## Writing API Tests

### BaseApiTestCase

All API tests should extend `BaseApiTestCase`:

**Location**: `tests/Rest/BaseApiTestCase.php`

```php title="tests/Rest/BaseApiTestCase.php"
namespace Test\Rest;

use ByJG\ApiTools\Base\Schema;
use ByJG\ApiTools\OpenApiValidation;
use ByJG\Config\Config;
use ByJG\DbMigration\Database\MySqlDatabase;
use ByJG\DbMigration\Migration;
use ByJG\Util\Uri;
use ByJG\WebRequest\Psr7\Request;
use Exception;
use PHPUnit\Framework\TestCase;

class BaseApiTestCase extends TestCase
{
    use OpenApiValidation;

    protected static bool $databaseReset = false;
    protected string $filePath = __DIR__ . '/../../public/docs/openapi.json';

    protected function setUp(): void
    {
        $this->setSchema(Schema::getInstance(file_get_contents($this->filePath)));
        $this->resetDb();
    }

    protected function tearDown(): void
    {
        $this->setSchema(null);
    }

    public function getPsr7Request(): Request
    {
        $uri = Uri::getInstanceFromString()
            ->withScheme(Config::get('API_SCHEMA'))
            ->withHost(Config::get('API_SERVER'));

        return Request::getInstance($uri);
    }

    protected function resetDb(): void
    {
        if (!self::$databaseReset) {
            if (Config::definition()->getCurrentEnvironment() !== 'test') {
                throw new Exception('This test can only be executed in test environment');
            }
            Migration::registerDatabase(MySqlDatabase::class);
            $migration = new Migration(new Uri(Config::get('DBDRIVER_CONNECTION')), __DIR__ . '/../../db');
            $migration->prepareEnvironment();
            $migration->reset();
            self::$databaseReset = true;
        }
    }
}
```

### What BaseApiTestCase Provides

```php
// PSR-7 Request Factory
$psr7Request = $this->getPsr7Request();

// Send a FakeApiRequester and validate against OpenAPI automatically
$response = $this->sendRequest($request);

// Reset the database once per test process
$this->resetDb();
```

### Basic Test Example

```php
<?php

namespace Test\Rest;

use RestReferenceArchitecture\Util\FakeApiRequester;

class ProductTest extends BaseApiTestCase
{
    public function testGetProduct(): void
    {
        $loginResponse = $this->sendRequest(
            Credentials::requestLogin(Credentials::getAdminUser())
        );
        $token = json_decode($loginResponse->getBody()->getContents(), true)['token'];

        $request = (new FakeApiRequester())
            ->withPsr7Request($this->getPsr7Request())
            ->withMethod('GET')
            ->withPath('/products/1')
            ->withRequestHeader(['Authorization' => "Bearer {$token}"])
            ->expectStatus(200);

        $response = $this->sendRequest($request);
        $product = json_decode($response->getBody()->getContents(), true);

        $this->assertArrayHasKey('id', $product);
        $this->assertArrayHasKey('name', $product);
        $this->assertSame(1, $product['id']);
    }
}
```

## Testing Authentication

### Test User Credentials

**Location**: `tests/Rest/Credentials.php`

```php
use Test\Rest\Credentials;

// Admin user
$adminCreds = Credentials::getAdminUser();
// Returns: ['username' => 'admin', 'password' => 'admin']

// Regular user
$userCreds = Credentials::getRegularUser();
// Returns: ['username' => 'user', 'password' => 'user']

// Login request
$loginRequest = Credentials::requestLogin(Credentials::getAdminUser());
$response = $this->sendRequest($loginRequest);
$data = json_decode($response->getBody()->getContents(), true);
$token = $data['token'];
```

### Testing Unauthorized Access

```php
public function testGetUnauthorized()
{
    $this->expectException(Error401Exception::class);
    $this->expectExceptionMessage('Absent authorization token');

    $request = new FakeApiRequester();
    $request
        ->withPsr7Request($this->getPsr7Request())
        ->withMethod('GET')
        ->withPath('/dummy/1')
        ->assertResponseCode(401);

    $this->assertRequest($request);
}
```

### Testing Invalid Credentials

```php
public function testLoginInvalidCredentials()
{
    $this->expectException(Error401Exception::class);
    $this->expectExceptionMessage('Username or password is invalid');

    $this->assertRequest(Credentials::requestLogin([
        'username' => 'invalid',
        'password' => 'wrong'
    ]));
}
```

### Testing Token Expiration

```php
public function testExpiredToken()
{
    // Create expired token
    $expiredToken = JwtWrapper::createToken([
        'userid' => 1,
        'name' => 'Test User',
        'role' => 'user'
    ], -3600); // Expired 1 hour ago

    $this->expectException(Error401Exception::class);

    $request = new FakeApiRequester();
    $request
        ->withPsr7Request($this->getPsr7Request())
        ->withMethod('GET')
        ->withPath('/products')
        ->withRequestHeader(['Authorization' => "Bearer {$expiredToken}"])
        ->assertResponseCode(401);

    $this->assertRequest($request);
}
```

## Testing Authorization

### Testing Role Requirements

```php
public function testInsufficientPrivileges()
{
    $this->expectException(Error403Exception::class);
    $this->expectExceptionMessage('Insufficient privileges');

    // Login as regular user
    $loginResponse = $this->assertRequest(
        Credentials::requestLogin(Credentials::getRegularUser())
    );
    $data = json_decode($loginResponse->getBody()->getContents(), true);
    $token = $data['token'];

    // Try admin-only endpoint
    $request = new FakeApiRequester();
    $request
        ->withPsr7Request($this->getPsr7Request())
        ->withMethod('DELETE')
        ->withPath('/products/1')
        ->withRequestHeader(['Authorization' => "Bearer {$token}"])
        ->assertResponseCode(403);

    $this->assertRequest($request);
}
```

### Testing Different Roles

```php
public function testAdminCanDelete()
{
    $loginResponse = $this->assertRequest(
        Credentials::requestLogin(Credentials::getAdminUser())
    );
    $data = json_decode($loginResponse->getBody()->getContents(), true);

    $request = new FakeApiRequester();
    $request
        ->withPsr7Request($this->getPsr7Request())
        ->withMethod('DELETE')
        ->withPath('/products/1')
        ->withRequestHeader(['Authorization' => "Bearer {$data['token']}"])
        ->assertResponseCode(200);

    $this->assertRequest($request);
}

public function testUserCannotDelete()
{
    $this->expectException(Error403Exception::class);

    $loginResponse = $this->assertRequest(
        Credentials::requestLogin(Credentials::getRegularUser())
    );
    $data = json_decode($loginResponse->getBody()->getContents(), true);

    $request = new FakeApiRequester();
    $request
        ->withPsr7Request($this->getPsr7Request())
        ->withMethod('DELETE')
        ->withPath('/products/1')
        ->withRequestHeader(['Authorization' => "Bearer {$data['token']}"])
        ->assertResponseCode(403);

    $this->assertRequest($request);
}
```

## Testing Validation

### Testing Required Fields

```php
public function testCreateWithoutRequiredField()
{
    $this->expectException(Error400Exception::class);

    $loginResponse = $this->assertRequest(
        Credentials::requestLogin(Credentials::getAdminUser())
    );
    $data = json_decode($loginResponse->getBody()->getContents(), true);

    $request = new FakeApiRequester();
    $request
        ->withPsr7Request($this->getPsr7Request())
        ->withMethod('POST')
        ->withPath('/products')
        ->withRequestBody(json_encode([
            // Missing required 'name' field
            'price' => 99.99
        ]))
        ->withRequestHeader(['Authorization' => "Bearer {$data['token']}"])
        ->assertResponseCode(400);

    $this->assertRequest($request);
}
```

### Testing Data Type Validation

```php
public function testCreateWithInvalidType()
{
    $this->expectException(Error400Exception::class);

    $loginResponse = $this->assertRequest(
        Credentials::requestLogin(Credentials::getAdminUser())
    );
    $data = json_decode($loginResponse->getBody()->getContents(), true);

    $request = new FakeApiRequester();
    $request
        ->withPsr7Request($this->getPsr7Request())
        ->withMethod('POST')
        ->withPath('/products')
        ->withRequestBody(json_encode([
            'name' => 'Product',
            'price' => 'not-a-number'  // Invalid type
        ]))
        ->withRequestHeader(['Authorization' => "Bearer {$data['token']}"])
        ->assertResponseCode(400);

    $this->assertRequest($request);
}
```

### Testing Business Rule Validation

```php
public function testCreateWithNegativePrice()
{
    $this->expectException(Error400Exception::class);
    $this->expectExceptionMessage('Price cannot be negative');

    $loginResponse = $this->assertRequest(
        Credentials::requestLogin(Credentials::getAdminUser())
    );
    $data = json_decode($loginResponse->getBody()->getContents(), true);

    $request = new FakeApiRequester();
    $request
        ->withPsr7Request($this->getPsr7Request())
        ->withMethod('POST')
        ->withPath('/products')
        ->withRequestBody(json_encode([
            'name' => 'Product',
            'price' => -10.00  // Negative price
        ]))
        ->withRequestHeader(['Authorization' => "Bearer {$data['token']}"])
        ->assertResponseCode(400);

    $this->assertRequest($request);
}
```

## Testing CRUD Operations

### Complete CRUD Test

**Location**: `tests/Rest/DummyTest.php:142`

```php
public function testFullCrud()
{
    // Login
    $loginResponse = $this->assertRequest(
        Credentials::requestLogin(Credentials::getAdminUser())
    );
    $loginData = json_decode($loginResponse->getBody()->getContents(), true);
    $token = $loginData['token'];

    // CREATE
    $createRequest = new FakeApiRequester();
    $createRequest
        ->withPsr7Request($this->getPsr7Request())
        ->withMethod('POST')
        ->withPath('/dummy')
        ->withRequestBody(json_encode(['field' => 'test value']))
        ->withRequestHeader(['Authorization' => "Bearer {$token}"])
        ->assertResponseCode(200);

    $createResponse = $this->assertRequest($createRequest);
    $created = json_decode($createResponse->getBody()->getContents(), true);
    $id = $created['id'];

    // READ
    $getRequest = new FakeApiRequester();
    $getRequest
        ->withPsr7Request($this->getPsr7Request())
        ->withMethod('GET')
        ->withPath("/dummy/{$id}")
        ->withRequestHeader(['Authorization' => "Bearer {$token}"])
        ->assertResponseCode(200);

    $getResponse = $this->assertRequest($getRequest);
    $retrieved = json_decode($getResponse->getBody()->getContents(), true);

    $this->assertEquals($id, $retrieved['id']);
    $this->assertEquals('test value', $retrieved['field']);

    // UPDATE
    $retrieved['field'] = 'updated value';

    $updateRequest = new FakeApiRequester();
    $updateRequest
        ->withPsr7Request($this->getPsr7Request())
        ->withMethod('PUT')
        ->withPath('/dummy')
        ->withRequestBody(json_encode($retrieved))
        ->withRequestHeader(['Authorization' => "Bearer {$token}"])
        ->assertResponseCode(200);

    $this->assertRequest($updateRequest);

    // Verify update
    $verifyRequest = new FakeApiRequester();
    $verifyRequest
        ->withPsr7Request($this->getPsr7Request())
        ->withMethod('GET')
        ->withPath("/dummy/{$id}")
        ->withRequestHeader(['Authorization' => "Bearer {$token}"])
        ->assertResponseCode(200);

    $verifyResponse = $this->assertRequest($verifyRequest);
    $verified = json_decode($verifyResponse->getBody()->getContents(), true);

    $this->assertEquals('updated value', $verified['field']);
}
```

### Testing List Endpoint

```php
public function testList()
{
    $loginResponse = $this->assertRequest(
        Credentials::requestLogin(Credentials::getRegularUser())
    );
    $data = json_decode($loginResponse->getBody()->getContents(), true);

    $request = new FakeApiRequester();
    $request
        ->withPsr7Request($this->getPsr7Request())
        ->withMethod('GET')
        ->withPath('/dummy?page=0&size=20')
        ->withRequestHeader(['Authorization' => "Bearer {$data['token']}"])
        ->assertResponseCode(200);

    $response = $this->assertRequest($request);
    $list = json_decode($response->getBody()->getContents(), true);

    $this->assertIsArray($list);
    $this->assertGreaterThanOrEqual(0, count($list));

    if (count($list) > 0) {
        $this->assertArrayHasKey('id', $list[0]);
        $this->assertArrayHasKey('field', $list[0]);
    }
}
```

### Testing Pagination

```php
public function testPagination()
{
    $loginResponse = $this->assertRequest(
        Credentials::requestLogin(Credentials::getRegularUser())
    );
    $data = json_decode($loginResponse->getBody()->getContents(), true);
    $token = $data['token'];

    // Get first page
    $page1Request = new FakeApiRequester();
    $page1Request
        ->withPsr7Request($this->getPsr7Request())
        ->withMethod('GET')
        ->withPath('/dummy?page=0&size=10')
        ->withRequestHeader(['Authorization' => "Bearer {$token}"])
        ->assertResponseCode(200);

    $page1Response = $this->assertRequest($page1Request);
    $page1 = json_decode($page1Response->getBody()->getContents(), true);

    // Get second page
    $page2Request = new FakeApiRequester();
    $page2Request
        ->withPsr7Request($this->getPsr7Request())
        ->withMethod('GET')
        ->withPath('/dummy?page=1&size=10')
        ->withRequestHeader(['Authorization' => "Bearer {$token}"])
        ->assertResponseCode(200);

    $page2Response = $this->assertRequest($page2Request);
    $page2 = json_decode($page2Response->getBody()->getContents(), true);

    // Verify pages are different
    if (count($page1) > 0 && count($page2) > 0) {
        $this->assertNotEquals($page1[0]['id'], $page2[0]['id']);
    }
}
```

## Unit Testing Services

### Service Test Example

```php
<?php

namespace Test\Unit\Service;

use PHPUnit\Framework\TestCase;
use RestReferenceArchitecture\Service\ProductService;
use RestReferenceArchitecture\Repository\ProductRepository;
use RestReferenceArchitecture\Model\Product;

class ProductServiceTest extends TestCase
{
    protected ProductService $service;
    protected ProductRepository $repository;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ProductRepository::class);
        $this->service = new ProductService($this->repository);
    }

    public function testGetOrFail()
    {
        $product = new Product();
        $product->setId(1);
        $product->setName('Test Product');

        $this->repository
            ->expects($this->once())
            ->method('get')
            ->with(1)
            ->willReturn($product);

        $result = $this->service->getOrFail(1);

        $this->assertEquals(1, $result->getId());
        $this->assertEquals('Test Product', $result->getName());
    }

    public function testGetOrFailThrowsException()
    {
        $this->expectException(Error404Exception::class);

        $this->repository
            ->expects($this->once())
            ->method('get')
            ->with(999)
            ->willReturn(null);

        $this->service->getOrFail(999);
    }

    public function testCreate()
    {
        $payload = [
            'name' => 'New Product',
            'price' => 99.99
        ];

        $this->repository
            ->expects($this->once())
            ->method('getMapper')
            ->willReturn($mockMapper);

        $this->repository
            ->expects($this->once())
            ->method('save');

        $result = $this->service->create($payload);

        $this->assertInstanceOf(Product::class, $result);
    }
}
```

## Test Data Management

### Using Helper Methods

```php
class ProductTest extends BaseApiTestCase
{
    /**
     * Get sample product data
     */
    protected function getSampleData(bool $array = false)
    {
        $sample = [
            'name' => 'Test Product',
            'price' => 99.99,
            'stock' => 100
        ];

        if ($array) {
            return $sample;
        }

        ObjectCopy::copy($sample, $model = new Product());
        return $model;
    }

    public function testCreate()
    {
        // Use as array
        $payload = $this->getSampleData(true);

        // Use as model
        $model = $this->getSampleData(false);
    }
}
```

### Database Reset

```php
protected function setUp(): void
{
    parent::setUp();
    $this->resetDb();  // Resets database to migration state
}
```

### Seeding Test Data

```php
protected function seedTestData()
{
    $productService = Config::get(ProductService::class);

    for ($i = 1; $i <= 10; $i++) {
        $productService->create([
            'name' => "Product {$i}",
            'price' => $i * 10.00,
            'stock' => $i * 5
        ]);
    }
}

public function testListWithData()
{
    $this->seedTestData();

    // Now test list endpoint
    // ...
}
```

## Best Practices

### 1. Test One Thing Per Test

```php
// Good - Single responsibility
public function testCreateProduct() { /* ... */ }
public function testGetProduct() { /* ... */ }
public function testUpdateProduct() { /* ... */ }

// Bad - Multiple concerns
public function testProductCrud() {
    // create, read, update, delete all in one test
}
```

### 2. Use Descriptive Test Names

```php
// Good - Clear intent
public function testCreateProductWithNegativePriceThrowsException() { }
public function testUserCannotDeleteOtherUsersProducts() { }

// Bad - Vague
public function testProduct1() { }
public function testFailure() { }
```

### 3. Arrange-Act-Assert Pattern

```php
public function testCreateProduct()
{
    // ARRANGE
    $loginResponse = $this->assertRequest(
        Credentials::requestLogin(Credentials::getAdminUser())
    );
    $token = json_decode($loginResponse->getBody()->getContents(), true)['token'];

    // ACT
    $request = new FakeApiRequester();
    $request
        ->withPsr7Request($this->getPsr7Request())
        ->withMethod('POST')
        ->withPath('/products')
        ->withRequestBody(json_encode($this->getSampleData(true)))
        ->withRequestHeader(['Authorization' => "Bearer {$token}"])
        ->assertResponseCode(200);

    $response = $this->assertRequest($request);

    // ASSERT
    $data = json_decode($response->getBody()->getContents(), true);
    $this->assertArrayHasKey('id', $data);
    $this->assertGreaterThan(0, $data['id']);
}
```

### 4. Test Both Success and Failure Cases

```php
public function testCreateProductSuccess() { /* ... */ }
public function testCreateProductWithoutName() { /* ... */ }
public function testCreateProductWithInvalidPrice() { /* ... */ }
public function testCreateProductWithoutAuthentication() { /* ... */ }
public function testCreateProductWithoutAuthorization() { /* ... */ }
```

### 5. Use Data Providers for Similar Tests

```php
/**
 * @dataProvider invalidProductDataProvider
 */
public function testCreateWithInvalidData($payload, $expectedMessage)
{
    $this->expectException(Error400Exception::class);
    $this->expectExceptionMessage($expectedMessage);

    $productService = Config::get(ProductService::class);
    $productService->create($payload);
}

public function invalidProductDataProvider(): array
{
    return [
        'missing name' => [
            ['price' => 99.99],
            'Product name is required'
        ],
        'negative price' => [
            ['name' => 'Product', 'price' => -10],
            'Price cannot be negative'
        ],
        'invalid type' => [
            ['name' => 'Product', 'price' => 'not-a-number'],
            'Price must be a number'
        ]
    ];
}
```

### 6. Clean Up After Tests

```php
protected function tearDown(): void
{
    // Reset database state
    parent::tearDown();
}
```

### 7. Use Helper Classes

```php
// Credentials helper
$adminCreds = Credentials::getAdminUser();

// Test data helper
$sampleProduct = $this->getSampleData(true);
```

### 8. Test Edge Cases

```php
public function testListWithNoResults() { /* ... */ }
public function testGetNonExistentProduct() { /* ... */ }
public function testUpdateDeletedProduct() { /* ... */ }
public function testPaginationBeyondLastPage() { /* ... */ }
```

## Related Documentation

- [REST API Development](rest.md)
- [Attributes System](attributes.md) - Testing validation attributes
- [Error Handling](error-handling.md) - Testing error responses
- [Service Patterns](service-patterns.md) - Unit testing services
- [JWT Authentication](jwt-advanced.md) - Testing authentication
