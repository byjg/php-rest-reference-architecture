<?php

require_once __DIR__ . '/../vendor/autoload.php';

use \ByJG\RestServer\ServerRequestHandler;
use \RestTemplate\Psr11;

/**
 * @SWG\Swagger(
 *     schemes={"http"},
 *     basePath="",
 *     host="__HOSTNAME__",
 *     consumes={"application/json"},
 *     produces={"application/json"},
 *     @SWG\Info(
 *         version="1.0.0",
 *         title="Title",
 *         description="Description",
 *         termsOfService="http://__HOSTNAME__/terms/",
 *         @SWG\Contact(
 *             email="email@example.com"
 *         ),
 *         @SWG\License(
 *             name="Proprietary",
 *             url="http://__HOSTNAME__/LICENSE"
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

$server = new ServerRequestHandler();

// $server->setPathHandler("get", "/user", \ByJG\RestServer\HandleOutput\JsonCleanHandler::class);
// $server->setMimeTypeHandler("image/png", \ByJG\RestServer\HandleOutput\HtmlHandler::class);

$server->setRoutesSwagger(
    __DIR__ . '/docs/swagger.json',
    Psr11::container()->get('CACHE_ROUTES')
);
$server->handle();
