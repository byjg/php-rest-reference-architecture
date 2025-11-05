---
sidebar_position: 1
---

# Rest Methods API integrated with OpenAPI

There are two ways to create a REST Method API:

- using an existing OpenAPI specification in JSON format
- documenting your application and generating the OpenAPI specification from your code

## Using existing OpenAPI specification

If you already have an OpenAPI specification in JSON format, you can use it to create your Rest Method API.
Place the file `openapi.json` in the `public/docs` folder.

There is one requirement in your specification. You need for each method to define a `operationId` property as follows:

```json
    "paths": {
        "/login": {
            "post": {
                "operationId": "POST::/login::RestReferenceArchitecture\\Rest\\Login::mymethod",
        }
    }
```

The `operationId` is composed by the following parts:

- HTTP Method (not used, any string will work)
- Path (not used, any string will work)
- Namespace of the class (required)
- Method of the class (required)

With the definition above every request to `POST /login` will be handled by the method `mymethod` of the class `RestReferenceArchitecture\Rest\Login`.

The only requirement is that the method must receive two parameters:

```php
namespace RestReferenceArchitecture\Rest;

use ByJG\RestServer\HttpRequest;
use ByJG\RestServer\HttpResponse;

class Login
    public function mymethod(HttpRequest $request, HttpResponse $response)
    {
        // ...
    }
}
```

We use the package byjg/restserver to handle the requests. Please refer to the documentation of this package at [https://github.com/byjg/restserver/tree/bump#2-processing-the-request-and-response](https://github.com/byjg/restserver/tree/bump#2-processing-the-request-and-response)

## Documenting your application with PHPDOC and generating the OpenAPI specification

If you don't have an OpenAPI specification, you can document your application with PHPDOC and generate the OpenAPI specification from your code.

```php
namespace RestReferenceArchitecture\Rest;

use ByJG\RestServer\HttpRequest;
use ByJG\RestServer\HttpResponse;
use OpenApi\Attributes as OA;

class Login

    /**
     * Do log in
     */
    #[OA\Post(
        path: "/login",
        tags: ["Login"],
    )]
    #[OA\RequestBody(
        description: "The Login Data",
        required: true,
        content: new OA\JsonContent(
            required: [ "username", "password" ],
            properties: [
                new OA\Property(property: "username", description: "The Username", type: "string", format: "string"),
                new OA\Property(property: "password", description: "The Password",  type: "string", format: "string")
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "The object to be created",
        content: new OA\JsonContent(
            required: [ "token" ],
            properties: [
                new OA\Property(property: "token", type: "string"),
                new OA\Property(property: "data", properties: [
                    new OA\Property(property: "userid", type: "string"),
                    new OA\Property(property: "name", type: "string"),
                    new OA\Property(property: "role", type: "string"),
                ])
            ]
        )
    )]
    public function mymethod(HttpRequest $request, HttpResponse $response)
    {
        // ...
    }
}
```

After documenting your code, you can generate the OpenAPI specification with the following command:

```bash
APP_ENV=dev composer run openapi
```

The OpenAPI specification will be generated in the folder `public/docs`.

We use the package zircote/swagger-php to generate the OpenAPI specification.
Please refer to the documentation of this package at [https://zircote.github.io/swagger-php/](https://zircote.github.io/swagger-php/) to learn more about the PHPDOC annotations.

We use the package byjg/restserver to handle the requests. Please refer to the documentation of this package at [https://github.com/byjg/restserver](https://github.com/byjg/restserver)
