<?php

require_once __DIR__ . '/../vendor/autoload.php';

use RestTemplate\Psr11;
use ByJG\RestServer\HttpRequestHandler;
use ByJG\RestServer\Route\OpenApiRouteList;

class App
{
    public static function run()
    {
        $server = new HttpRequestHandler();

        $server->handle(Psr11::container()->get(OpenApiRouteList::class));
    }
}

App::run();