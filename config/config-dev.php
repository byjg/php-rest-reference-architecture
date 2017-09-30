<?php

use \ByJG\RestServer\RoutePattern;
use \ByJG\RestServer\HandleOutput\JsonHandler;

return [

    'HOST' => 'localhost',

    'ROUTE_CLASSMAP' => [
        'login' => 'RestTemplate.Rest.Login',
        'sample' => 'RestTemplate.Rest.Sample',
        'sampleprotected' => 'RestTemplate.Rest.SampleProtected',
    ],
    'ROUTE_PATH' => [
        new RoutePattern('POST', '/{module:login}', JsonHandler::class),
    ],
    'ROUTE_PATH_EXTRA' => [
        // Specific for this Environment
        new RoutePattern('GET', '/{module:sample}/{action:ping}', JsonHandler::class),
        new RoutePattern('GET', '/{module:sampleprotected}/{action:ping}', JsonHandler::class),
    ],

    'JWT_SERVER' => "localhost",
    'JWT_SECRET' => '/R2/isXLfFD+xqxP9rfD/UDVwA5rVZzEe9tQhBYLJrU=',





    'DOCKERFILE' => [
        // Specific for this Environment
    ],
    'DOCKER_CMD_ARGS' => [
        '-v $PWD:/srv/web'
    ],
    'DOCKER_BEFORE_RUN' => [

    ],
];
