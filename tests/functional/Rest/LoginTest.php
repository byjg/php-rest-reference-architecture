<?php

namespace Test;

/**
 * Create a TestCase inherited from SwaggerTestCase
 */
class LoginTest extends \ByJG\Swagger\SwaggerTestCase
{
    protected $filePath = __DIR__ . '/../../../web/docs/swagger.json';

    public function testLoginOk()
    {
        $this->makeRequest(
            'POST',             // The method
            "/login",             // The path defined in the swagger.json
            200,           // The expected status code
            null,                 // The parameters 'in path'
            [
                'username' => 'admin',
                'password' => 'pwd'
            ]  // The request body
        );
    }

    public function testLoginFail()
    {
        $this->makeRequest(
            'POST',             // The method
            "/login",             // The path defined in the swagger.json
            401,           // The expected status code
            null,                 // The parameters 'in path'
            [
                'username' => 'invalid',
                'password' => 'invalid'
            ]  // The request body
        );
    }
}
