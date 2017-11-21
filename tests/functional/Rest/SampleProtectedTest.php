<?php

namespace Test;

/**
 * Create a TestCase inherited from SwaggerTestCase
 */
class SampleProtectedTest extends \ByJG\Swagger\SwaggerTestCase
{
    protected $filePath = __DIR__ . '/../../../web/docs/swagger.json';

    public function testGetUnauthorized()
    {
        $this->makeRequest('GET', "/sampleprotected/ping", 401);
    }

    public function testGetAuthorized()
    {
        $result = $this->makeRequest(
            'POST',             // The method
            "/login",             // The path defined in the swagger.json
            200,           // The expected status code
            null,                 // The parameters 'in path'
            [
                'username' => 'admin',
                'password' => 'pwd'
            ]  // The request body
        );
        $this->makeRequest('GET', "/sampleprotected/ping", 401);
    }
}
