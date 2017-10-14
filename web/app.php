<?php

require_once __DIR__ . '/../vendor/autoload.php';

use \ByJG\RestServer\ServerRequestHandler;
use \Framework\Psr11;

/**
 * @SWG\Swagger(
 *     schemes={"http"},
 *     basePath="/rest",
 *     host="HOSTNAME",
 *     consumes={"application/json"},
 *     produces={"application/json"},
 *     @SWG\Info(
 *         version="1.0.0",
 *         title="Title",
 *         description="Description",
 *         termsOfService="http://localhost/terms/",
 *         @SWG\Contact(
 *             email="email@example.com"
 *         ),
 *         @SWG\License(
 *             name="Proprietary",
 *             url="http://host/"
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
 * @SWG\Definition(
 *   definition="errorProperties",
 *   @SWG\Property(property="type", type="string", description="A class de Exceção"),
 *   @SWG\Property(property="message", type="string", description="A mensagem de erro"),
 *   @SWG\Property(property="file", type="string", description="O arquivo que gerou o erro"),
 *   @SWG\Property(property="line", type="integer", description="A linha do erro")
 * )
 * @SWG\Definition(
 *   definition="error",
 *   @SWG\Property(property="error", ref="#/definitions/errorProperties")
 * )
 */

ServerRequestHandler::handle(
    Psr11::container()->get('ROUTE_CLASSMAP'),
    array_merge(
        Psr11::container()->get('ROUTE_PATH'),
        Psr11::container()->get('ROUTE_PATH_EXTRA')
    )
);
