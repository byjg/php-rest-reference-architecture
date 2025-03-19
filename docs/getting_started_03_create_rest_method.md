I'll help you fix and improve this text. Let me analyze the Markdown document first.

# Getting Started - Creating a REST Method

In this tutorial, we'll create a new REST method to update the status of the `example_crud` table.

We'll cover the following topics:

- OpenAPI Attributes
- Protecting the endpoint
- Validating input
- Saving to the database
- Returning results
- Unit testing

## OpenAPI Attributes

First, we'll add OpenAPI attributes to our REST method using 
the [zircote/swagger-php](https://zircote.github.io/swagger-php/guide/) library.

While the OpenAPI specification offers numerous attributes, we must define at least these three essential sets:

### 1. Method Attribute

This defines the HTTP method:

- `OA\Get` - For retrieving data
- `OA\Post` - For creating data
- `OA\Put` - For updating data
- `OA\Delete` - For deleting/canceling data

Example:

```php
#[OA\Put(
    path: "/example/crud/status",
    security: [
        ["jwt-token" => []]
    ],
    tags: ["Example"],
    description: "Update the status of the ExampleCrud"
)]
```

The `security` attribute defines the security schema. Without it, the endpoint remains public.

### 2. Request Attribute

This defines the input to the method using `OA\RequestBody` or `OA\Parameter`.

Example:

```php
#[OA\RequestBody(
    description: "The status to be updated",
    required: true,
    content: new OA\JsonContent(
        required: ["status"],
        properties: [
            new OA\Property(property: "id", type: "integer", format: "int32"),
            new OA\Property(property: "status", type: "string")
        ]
    )
)]
```

### 3. Response Attribute

This defines the expected output using `OA\Response`.

```php
#[OA\Response(
    response: 200,
    description: "The operation result",
    content: new OA\JsonContent(
        required: ["result"],
        properties: [
            new OA\Property(property: "result", type: "string")
        ]
    )
)]
```

Place these attributes at the beginning of your method. Following our pattern, we'll add this method at the end of the `ExampleCrudRest` class:

```php
#[OA\Put()]                 // complete with the attributes above
#[OA\RequestBody()]         // complete with the attributes above
#[OA\Response()]            // complete with the attributes above
public function putExampleCrudStatus(HttpResponse $response, HttpRequest $request)
{
    // Code to be added
}
```

## Protecting the Endpoint

If you've set the `security` property in your attributes, you need to validate the token:

```php
public function putExampleCrudStatus(HttpResponse $response, HttpRequest $request) 
{
    // Secure the endpoint 
    // Use one of the following methods:
    
    // a. Require a user with admin role
    JwtContext::requireRole($request, "admin");
    
    // b. OR require any authenticated user
    JwtContext::requireAuthenticated($request);
    
    // c. OR do nothing to make the endpoint public
}
```

Both methods return the token content. If the token is invalid or missing, an `Error401Exception` will be thrown. If the user lacks the required role, an `Error403Exception` will be thrown.

The default token content structure is:

```php
$data = [
    "userid" => "123",
    "name" => "John Doe",
    "role" => "admin" // or "user"
]
```

## Validating Input

Next, validate that the incoming request matches the OpenAPI specifications:

```php
public function putExampleCrudStatus(HttpResponse $response, HttpRequest $request) 
{
...
    // The line below will validate again the OpenAPI attributes
    // If the request doesn't match, an exception `Error400Exception` will be thrown
    // If the request matches, the payload will be returned
    $payload = OpenApiContext::validateRequest($request);
}
```

## Updating Status in the Repository

After validating the payload, we can update the record status using the repository pattern:

```php
/**
 * Update the status of an Example CRUD record
 * 
 * @param HttpResponse $response 
 * @param HttpRequest $request 
 * @return void
 */
public function putExampleCrudStatus(HttpResponse $response, HttpRequest $request) 
{
    // Previous code for payload validation...
    
    // Update the record status
    $exampleCrudRepo = Psr11::container()->get(ExampleCrudRepository::class);
    $model = $exampleCrudRepo->get($payload["id"]);
    
    if (!$model) {
        throw new NotFoundException("Record not found");
    }
    
    $model->setStatus($payload["status"]);
    $exampleCrudRepo->save($model);
    
    // Return response...
}
```

## Returning the Response

After updating the record, we need to return a standardized response as specified in our OpenAPI schema:

```php
public function putExampleCrudStatus(HttpResponse $response, HttpRequest $request) 
{
    // Previous code for update logic...
    
    // Return standardized response
    $response->write([
        "result" => "ok"
    ]);
}
```

## Unit Testing

To ensure our endpoint works correctly and continues to function as expected, we'll create a functional test. This test simulates calling the endpoint and validates both the response format and the business logic.

Create or update the test file `tests/Functional/Rest/ExampleCrudTest.php`:

```php
/**
 * @covers \YourNamespace\Controller\ExampleCrudController
 */
public function testUpdateStatus()
{
    // Authenticate to get a valid token (if required)
    $authResult = json_decode(
        $this->assertRequest(Credentials::requestLogin(Credentials::getAdminUser()))
            ->getBody()
            ->getContents(), 
        true
    );
    
    // Prepare test data
    $recordId = 1;
    $newStatus = 'new status';

    // Create mock API request
    $request = new FakeApiRequester();
    $request
        ->withPsr7Request($this->getPsr7Request())
        ->withMethod('PUT')
        ->withPath("/example/crud/status")
        ->withRequestBody(json_encode([
            'id' => $recordId,
            'status' => $newStatus
        ]))
        ->withRequestHeader([
            "Authorization" => "Bearer " . $authResult['token'],
            "Content-Type" => "application/json"
        ])
        ->assertResponseCode(200);
    
    // Execute the request and get response
    $response = $this->assertRequest($request);
    $responseData = json_decode($response->getBody()->getContents(), true);
    
    // There is no necessary to Assert expected response format and data
    // because the assertRequest will do it for you.
    // $this->assertIsArray($responseData);
    // $this->assertArrayHasKey('result', $responseData);
    // $this->assertEquals('ok', $responseData['result']);
    
    // Verify the database was updated correctly
    $repository = Psr11::container()->get(ExampleCrudRepository::class);
    $updatedRecord = $repository->get($recordId);
    $this->assertEquals($newStatus, $updatedRecord->getStatus());
}
```

This test performs the following validations:
1. Ensures the endpoint returns a 200 status code
2. Verifies the response has the expected JSON structure
3. Confirms the database record was actually updated with the new status