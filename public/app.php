<?php

require_once __DIR__ . '/../vendor/autoload.php';

use RestTemplate\Psr11;
use ByJG\RestServer\HttpRequestHandler;
use ByJG\RestServer\Route\OpenApiRouteList;

/**
 * @SWG\Swagger(
 *     schemes={"http"},
 *     basePath="",
 *     consumes={"application/json"},
 *     produces={"application/json"},
 *     @SWG\Info(
 *         version="1.0.0",
 *         title="Title",
 *         description="Description",
 *         @SWG\Contact(
 *             email="email@example.com"
 *         ),
 *         @SWG\License(
 *             name="Proprietary",
 *         )
 *     ),
 *     @SWG\ExternalDocumentation(
 *         description="Bitbucket Docs",
 *         url="https://example.com"
 *     )
 * )
 * @SWG\SecurityScheme(
 *   securityDefinition="jwt-token",
 *   type="apiKey",
 *   in="header",
 *   name="Authorization"
 * )
 * @SWG\SecurityScheme(
 *   securityDefinition="query-token",
 *   type="apiKey",
 *   in="query",
 *   name="token"
 * )
 * @SWG\SecurityScheme(
 *   securityDefinition="basic-http",
 *   type="basic"
 * )
 * @SWG\Definition(
 *   definition="error",
 *   @SWG\Property(property="error",
 *      @SWG\Property(property="type", type="string", description="A class de Exceção"),
 *      @SWG\Property(property="message", type="string", description="A mensagem de erro"),
 *      @SWG\Property(property="file", type="string", description="O arquivo que gerou o erro"),
 *      @SWG\Property(property="line", type="integer", description="A linha do erro")
 *   )
 * )
 */

$server = new HttpRequestHandler();

$server->handle(Psr11::container()->get(OpenApiRouteList::class));
