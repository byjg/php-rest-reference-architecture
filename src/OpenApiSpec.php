<?php

namespace RestTemplate;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    description: "Information about the API",
    title: "API Title",
    termsOfService: "http://localhost:8080/terms/",
    contact: new OA\Contact(
        email: "someone@example.com"
    ),
    license: new OA\License(
        name: "Proprietary"
    )
)]
#[OA\Server(
    url: "http://localhost:8080",
    description: "Local Development server"
)]
#[OA\ExternalDocumentation(
    description: "Find out more about Swagger",
    url: "http://localhost:8080/docs"
)]
#[OA\SecurityScheme(
    securityScheme: "jwt-token",
    type: "apiKey",
    name: "Authorization",
    in: "header"
)]
#[OA\Schema(
    schema: "error",
    required: ["error"],
    properties: [
        new OA\Property(
            property: "error",
            required: ["type", "message", "file", "line"],
            properties: [
                new OA\Property(property: "type", description: "A class de Exceção", type: "string"),
                new OA\Property(property: "message", description: "A mensagem de erro", type: "string"),
                new OA\Property(property: "file", description: "O arquivo que gerou o erro", type: "string"),
                new OA\Property(property: "line", description: "A linha do erro", type: "integer")
            ],
            type: "object"
        )
    ],
    type: "object"
)]
class OpenApiSpec
{

}
