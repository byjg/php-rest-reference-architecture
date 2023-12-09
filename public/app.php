<?php

require_once __DIR__ . '/../vendor/autoload.php';

use ByJG\RestServer\HttpRequestHandler;
use ByJG\RestServer\Route\OpenApiRouteList;
use RestTemplate\Psr11;

class App
{
    public static function run()
    {
        $server = Psr11::container()->get(HttpRequestHandler::class);
        $server->handle(Psr11::container()->get(OpenApiRouteList::class));
    }
}

App::run();
