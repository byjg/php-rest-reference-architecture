<?php

namespace Test\Functional\Rest;

use Builder\Psr11;
use ByJG\Util\Psr7\Request;
use ByJG\Util\Uri;
use RestTemplate\Util\FakeApiRequester;

class Credentials
{
    public static function getAdminUser()
    {
        return [
            'username' => (getenv('TEST_ADMIN_USER') ? getenv('TEST_ADMIN_USER') : 'admin@example.com'),
            'password' => (getenv('TEST_ADMIN_PASSWORD') ? getenv('TEST_ADMIN_PASSWORD') : 'pwd'),
        ];
    }

    public static function getRegularUser()
    {
        return [
            'username' => (getenv('TEST_REGULAR_USER') ? getenv('TEST_REGULAR_USER') : 'user@example.com'),
            'password' => (getenv('TEST_REGULAR_PASSWORD') ? getenv('TEST_REGULAR_PASSWORD') : 'pwd'),
        ];
    }

    public static function requestLogin($cred)
    {
        $uri = Uri::getInstanceFromString()
            ->withScheme(Psr11::container()->get("API_SCHEMA"))
            ->withHost(Psr11::container()->get("API_SERVER"));

        $psr7Request = Request::getInstance($uri);

        $request = new FakeApiRequester();
        $request
            ->withPsr7Request($psr7Request)
            ->withMethod('POST')
            ->withPath("/login")
            ->assertResponseCode(200)
            ->withRequestBody($cred)
        ;
        return $request;
    }
}
