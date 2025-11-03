---
sidebar_position: 2
---

# Functional Unit Tests

## Running the Tests

The project includes functional tests. You can run them from your IDE or command line.

```bash
# Create an empty database for testing
APP_ENV=test composer run migrate -- reset --yes

# Set optional values
# export TEST_ADMIN_USER=admin@example.com
# export TEST_ADMIN_PASSWORD='!P4ssw0rdstr!'
# export TEST_REGULAR_USER=user@example.com
# export TEST_REGULAR_PASSWORD='!P4ssw0rdstr!'

# Run the tests
APP_ENV=test composer run test
```

## Creating Your Tests

We can test the RestAPI as follows:

```php
namespace Test\Functional\Rest;


use ByJG\ApiTools\Base\Schema;
use RestReferenceArchitecture\Util\FakeApiRequester;

/**
 * Create a TestCase inherited from SwaggerTestCase
 */
class SampleTest extends BaseApiTestCase
{
    protected $filePath = __DIR__ . '/../../../public/docs/openapi.json';

    protected function setUp(): void
    {
        $schema = Schema::getInstance(file_get_contents($this->filePath));
        $this->setSchema($schema);

        parent::setUp();
    }

    public function testPing()
    {
        $request = new FakeApiRequester();
        $request
            ->withPsr7Request($this->getPsr7Request())
            ->withMethod('GET')
            ->withPath("/sample/ping")
        ;
        $this->assertRequest($request);
    }
}
```

The `BaseApiTestCase` is a class that extends the `ByJG\ApiTools\Base\SwaggerTestCase` and provides some helper methods to test the RestAPI.

The `testPing` method will test the `/sample/ping` endpoint. The `assertRequest` method will test the endpoint and will throw an exception if the endpoint does not match the OpenAPI specification for the status code `200`.

There is no necessary to have a webserver running to test the RestAPI. The `BaseApiTestCase` will create the request and will pass to the `FakeApiRequester` object. The `FakeApiRequester` will call the endpoint as a PHP method and will try to match the result with the OpenAPI specification.

However, as it is a functional test, you need to have the database and other resources accessed by the endpoint running.

## Sending Body Data to Tests

We can send body data to the test as follows:

```php
public function testPing()
{
    $request = new FakeApiRequester();
    $request
        ->withPsr7Request($this->getPsr7Request())
        ->withMethod('POST')
        ->withPath("/sample/ping")
        ->withBody([
            'name' => 'John Doe'
        ])
    ;
    $this->assertRequest($request);
}
```

## Sending Query Parameters to Tests

```php
public function testPing()
{
    $request = new FakeApiRequester();
    $request
        ->withPsr7Request($this->getPsr7Request())
        ->withMethod('GET')
        ->withPath("/sample/ping")
        ->withQuery([
            'name' => 'John Doe'
        ])
    ;
    $this->assertRequest($request);
}
```

## Expecting a Specific Status Code

```php
public function testPing()
{
    $request = new FakeApiRequester();
    $request
        ->withPsr7Request($this->getPsr7Request())
        ->withMethod('GET')
        ->withPath("/sample/ping")
        ->assertResponseCode(404)
    ;
    $this->assertRequest($request);
}
```

## Expecting a Specific Response Body

```php
public function testPing()
{
    $request = new FakeApiRequester();
    $request
        ->withPsr7Request($this->getPsr7Request())
        ->withMethod('GET')
        ->withPath("/sample/ping")
        ->assertResponseBody([
            'message' => 'pong'
        ])
    ;
    $this->assertRequest($request);
}
```

---

**[← Previous: Rest Methods API](rest.md)** | **[Next: PSR-11 Container →](psr11.md)**
