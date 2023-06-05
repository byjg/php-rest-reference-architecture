# RestAPI Functional Test

## Running the tests

The project has some tests implemented. You can run the tests from the VSCode interface or from the command line.

```php
# Create an empty database for test
APP_TEST=test composer run migrate reset yes

# Set optional values
# export TEST_ADMIN_USER=admin@example.com
# export TEST_ADMIN_PASSWORD='!P4ssw0rdstr!'
# export TEST_REGULAR_USER=user@example.com
# export TEST_REGULAR_PASSWORD='!P4ssw0rdstr!'

# Run the tests
APP_TEST=test composer run test
```

## Creating your tests

We can test the RestAPI as follows:

```php
namespace Test\Functional\Rest;


use ByJG\ApiTools\Base\Schema;
use RestTemplate\Util\FakeApiRequester;

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

There is no necessary have a webserver running to test the RestAPI. The `BaseApiTestCase` will create the request and will pass to the `FakeApiRequester` object. The `FakeApiRequester` will call the endpoint as a PHP method and will try to match the result with the OpenAPI specification.

However, as it is a functional test you need to have the database and other resources accessed by the endpoint running.

## Send body data to the test

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

## Send query parameters to the test

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

## Expect a specific status code

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

## Expect a specific response body

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
