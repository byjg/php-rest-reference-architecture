# Getting Started - Creating a Rest Method

This part of the tutorial we are going to create a new Rest Method to update the status of the `example_crud` table.

We will cover the following topics:

- OpenAPI Attributes
- Protect the endpoint
- Validate the input
- Save to the Database
- Return the result
- Unit Test

## OpenAPI Attributes

The first step is to add the OpenAPI attributes to the Rest Method. 
We use the [zircote/swagger-php](https://zircote.github.io/swagger-php/guide/) library to add the attributes.

The list of OpenAPI attributes is to vast, however, there are a minimal of 3 sets of of attributes we must define.

The first set is to define what will be the method attribute. It can be:

- OA\Get - to retrieve data
- OA\Post - to create data
- OA\Put - For Update
- OA\Delete - For Delete/Cancel

e.g.

```php
#[OA\Put(
    path: "/example/crud/status",
    security: [
        ["jwt-token" => []]
    ],
    tags: ["Example"],
    description: 'Update the status of the ExampleCrud',
)]
```

The `security` attribute is used to define the security schema. If you don't define it, the endpoint will be public.

The second set it the request attribute. It can be, `OA\RequestBody` or `OA\Parameter` attribute. 
It is used to define the input of the method.

e.g.

```php
#[OA\RequestBody(
    description: "The status to be updated",
    required: true,
    content: new OA\JsonContent(
        required: [ "status" ],
        properties: [
            new OA\Property(property: "id", type: "integer", format: "int32"),
            new OA\Property(property: "status", type: "string", format: "string")
        ]
    )
)]
```

The third set is the response attribute. It is `OA\Response` attribute.

```php
#[OA\Response(
    response: 200,
    description: "The object rto be created",
    content: new OA\JsonContent(
        required: [ "result" ],
        properties: [
            new OA\Property(property: "result", type: "string", format: "string")
        ]
    )
)]
```

The attributes need to be in the beginning of the method. The method can be anywhere in the code,
but the follow the pattern we will put it in the end of the class `ExampleCrudRest`.

e.g

```php
<?php
#[OA\Put()]                 # complete with the attributes above
#[OA\RequestBody()]         # complete with the attributes above
#[OA\Response()]            # complete with the attributes above
public function putExampleCrudStatus(HttpResponse $response, HttpRequest $request)  // <-- required
{
    // Code to be added
}
```

## Protect the endpoint

The next step is to protect the endpoint. We will use the `JwtToken` to protect the endpoint.
If in the attribute you set the `security` property, then you need to say how to validate the token.

```php
public function putExampleCrudStatus(HttpResponse $response, HttpRequest $request) 
{
    // Secure the encpoint 
    // Use one of the following methods:
    
    // a. Require a user with role admin
    JwtContext::requireRole($request, "admin");
    
    // b. OR require any logged user
    JwtContext::requireAuthenticated($request);
    
    // c. OR do nothing to make the endpoint public
}
```

Both methods will return the content of the token. If there is no token or the token is invalid,
an exception `Error401Exception` will be thrown.

If the token is valid, but the user doesn't have the required role, an exception `Error403Exception` will be thrown.

The default token content is:

```php
$data = [
    "userid" => "123",
    "name" => "John Doe",
    "role" => "admin" or "user"
]
```

## Validate the input

The next step is to validate the input. We will check if the request matches with the OpenAPI attributes.

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

## Call the Repository

As we have the payload with the correct information, we can call the repository to update the status.

```php
public function putExampleCrudStatus(HttpResponse $response, HttpRequest $request) 
{
...
    $exampleCrudRepo = Psr11::container()->get(ExampleCrudRepository::class);
    $model = $exampleCrudRepo->get($payload["id"]);
    $model->setStatus($payload["status"]);
    $exampleCrudRepo->save($model);
```

## Return the result

The last step is to return the result as specified in the OpenAPI attributes.

```php
public function putExampleCrudStatus(HttpResponse $response, HttpRequest $request) 
{
... 
    $response->write([
        "result" => "ok"
    ]);
}
```

## Unit Test

A vital piece of our code is to guarantee it will continue to run as expected.
To do that we need to create a unit test to validate the code.

The test we will create is a functional test that will fake calling the endpoint 
and validate the result if is matching with the OpenAPI attributes and if processing what is expected.

We will add the test in the file `tests/Functional/Rest/ExampleCrudTest.php`.

```php
public function testUpdateStatus()
{
    // If you need to login to get a token, use the code below
    $result = json_decode($this->assertRequest(Credentials::requestLogin(Credentials::getAdminUser()))->getBody()->getContents(), true);

    // Execute the unit test
    $request = new FakeApiRequester();                              // It will mock the API call
    $request
        ->withPsr7Request($this->getPsr7Request())                  // PSR7 Request to be used
        ->withMethod('PUT')                                         // Method to be used
        ->withPath("/example/crud/status")                          // Path to be used
        ->withRequestBody(json_encode([                             // Request Body to be used
            'id' => 1,
            'status' => 'new status'
        ]))
        ->assertResponseCode(200)                                   // Expected Response Code
        ->withRequestHeader([                                       // If your method requires a token use this.
            "Authorization" => "Bearer " . $result['token']
        ])
    ;
    $body = $this->assertRequest($request);
    $bodyAr = json_decode($body->getBody()->getContents(), true);  // If necessary work with the result of the request
}
```
