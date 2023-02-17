<?php

namespace RestTemplate;

/**
 * @OA\Info(
 *     description="Information about the API",
 *     version="1.0.0",
 *     title="API Title",
 *     termsOfService="http://localhost:8080/terms/",
 *     @OA\Contact(
 *         email="someone@example.com"
 *     ),
 *     @OA\License(
 *         name="Proprietary"
 *     )
 * )
 * @OA\Server(
 *     description="Local Development server",
 *     url="http://localhost:8080"
 * )
 * @OA\ExternalDocumentation(
 *     description="Find out more about Swagger",
 *     url="http://localhostL:8080/docs"
 * )
 * 
 * @OA\SecurityScheme(
 *   securityScheme="jwt-token",
 *   type="apiKey",
 *   in="header",
 *   name="Authorization"
 * )
 * @OA\SecurityScheme(
 *   securityScheme="query-token",
 *   type="apiKey",
 *   in="query",
 *   name="token"
 * )
 * @OA\SecurityScheme(
 *   securityScheme="basic-http",
 *   type="http"
 * )
 * @OA\Schema(
 *   schema="error",
 *   @OA\Property(property="error",
 *      @OA\Property(property="type", type="string", description="A class de Exceção"),
 *      @OA\Property(property="message", type="string", description="A mensagem de erro"),
 *      @OA\Property(property="file", type="string", description="O arquivo que gerou o erro"),
 *      @OA\Property(property="line", type="integer", description="A linha do erro")
 *   )
 * )
 */
class OpenApiSpec
{

}
