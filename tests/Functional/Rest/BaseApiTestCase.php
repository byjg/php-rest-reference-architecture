<?php


namespace Test\Functional\Rest;

use Builder\Psr11;
use ByJG\Util\Psr7\Request;
use ByJG\Util\Uri;

class BaseApiTestCase extends \ByJG\ApiTools\ApiTestCase
{
    public function getPsr7Request()
    {
        $uri = Uri::getInstanceFromString()
            ->withScheme(Psr11::container()->get("API_SCHEMA"))
            ->withHost(Psr11::container()->get("API_SERVER"));

        return Request::getInstance($uri);
    }
}