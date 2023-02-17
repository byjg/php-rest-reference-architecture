<?php

require_once __DIR__ . '/../vendor/autoload.php';

use ByJG\RestServer\HttpRequestHandler;
use ByJG\RestServer\Middleware\CorsMiddleware;
use ByJG\RestServer\Route\OpenApiRouteList;
use RestTemplate\Psr11;

class App
{
    public static function run()
    {
        $server = new HttpRequestHandler();
        $server->withMiddleware(Psr11::container()->get(CorsMiddleware::class));

        $server->handle(Psr11::container()->get(OpenApiRouteList::class));
    }
}

App::run();
