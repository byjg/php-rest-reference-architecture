<?php

require_once __DIR__ . '/../bootstrap.php';

use ByJG\Config\Config;
use ByJG\RestServer\HttpRequestHandler;
use ByJG\RestServer\Route\OpenApiRouteList;

class App
{
    public static function run()
    {
        $server = Config::get(HttpRequestHandler::class);
        $server->handle(Config::get(OpenApiRouteList::class));
    }
}

App::run();
