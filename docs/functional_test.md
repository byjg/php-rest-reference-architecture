---
sidebar_position: 240
---

# Functional Unit Tests

## Running the Tests

The project includes functional tests. You can run them from your IDE or command line.

```bash
# Create or reset the testing database
APP_ENV=test composer migrate -- reset --yes

# Optional: customise test credentials
# export TEST_ADMIN_USER=admin@example.com
# export TEST_ADMIN_PASSWORD='!P4ssw0rdstr!'
# export TEST_REGULAR_USER=user@example.com
# export TEST_REGULAR_PASSWORD='!P4ssw0rdstr!'

# Execute the suite
APP_ENV=test composer run test
```

:::info Database resets automatically
`tests/Rest/BaseApiTestCase.php` already calls `Migration::reset()` the first time a test runs, but pre-resetting with the command above avoids surprises if you run the suite outside PHPUnit (e.g., invoking migrations manually).
:::

## Creating Your Tests

We can test the RestAPI as follows:

```php
namespace Test\Rest;

use RestReferenceArchitecture\Util\FakeApiRequester;

class SampleTest extends BaseApiTestCase
{
    public function testPing(): void
    {
        $request = (new FakeApiRequester())
            ->withPsr7Request($this->getPsr7Request())
            ->withMethod('GET')
            ->withPath('/sample/ping')
            ->expectStatus(200);

        $this->sendRequest($request);
    }
}
```

`BaseApiTestCase` extends `PHPUnit\Framework\TestCase` and mixes in the `OpenApiValidation` trait, so every call to `sendRequest()` validates the result against `public/docs/openapi.json`. All routing happens in-memory via `FakeApiRequester`, so you don't need a running web server—only a configured database.

However, as it is a functional test, you need to have the database and other resources accessed by the endpoint running.

## Sending Body Data to Tests

We can send body data to the test as follows:

```php
public function testPingWithBody()
{
    $request = (new FakeApiRequester())
        ->withPsr7Request($this->getPsr7Request())
        ->withMethod('POST')
        ->withPath('/sample/ping')
        ->withRequestBody(json_encode([
            'name' => 'John Doe'
        ]));

    $this->sendRequest($request);
}
```

## Sending Query Parameters to Tests

```php
public function testPingWithQuery()
{
    $request = (new FakeApiRequester())
        ->withPsr7Request($this->getPsr7Request())
        ->withMethod('GET')
        ->withPath('/sample/ping')
        ->withQuery(['name' => 'John Doe']);

    $this->sendRequest($request);
}
```

## Expecting a Specific Status Code

```php
public function testPingNotFound()
{
    $request = (new FakeApiRequester())
        ->withPsr7Request($this->getPsr7Request())
        ->withMethod('GET')
        ->withPath('/sample/ping')
        ->expectStatus(404);

    $this->sendRequest($request);
}
```

## Expecting a Specific Response Body

```php
public function testPingResponse()
{
    $request = (new FakeApiRequester())
        ->withPsr7Request($this->getPsr7Request())
        ->withMethod('GET')
        ->withPath('/sample/ping')
        ->expectJsonContains(['result' => 'pong']);

    $this->sendRequest($request);
}
```
