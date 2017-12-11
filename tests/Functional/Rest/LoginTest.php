<?php

namespace Test\Functional\Rest;

/**
 * Create a TestCase inherited from SwaggerTestCase
 */
class LoginTest extends \ByJG\Swagger\SwaggerTestCase
{
    protected $filePath = __DIR__ . '/../../../web/docs/swagger.json';

    /**
     * @throws \ByJG\Swagger\Exception\InvalidDefinitionException
     * @throws \ByJG\Swagger\Exception\NotMatchedException
     * @throws \ByJG\Swagger\Exception\RequiredArgumentNotFound
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testLoginOk()
    {
        $this->makeRequest(
            'POST',             // The method
            "/login",             // The path defined in the swagger.json
            200,           // The expected status code
            null,                 // The parameters 'in path'
            Credentials::getAdminUser()
        );
    }

    /**
     * @throws \ByJG\Swagger\Exception\InvalidDefinitionException
     * @throws \ByJG\Swagger\Exception\NotMatchedException
     * @throws \ByJG\Swagger\Exception\RequiredArgumentNotFound
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testLoginOk2()
    {
        $this->makeRequest(
            'POST',             // The method
            "/login",             // The path defined in the swagger.json
            200,           // The expected status code
            null,                 // The parameters 'in path'
            Credentials::getRegularUser()
        );
    }

    /**
     * @throws \ByJG\Swagger\Exception\InvalidDefinitionException
     * @throws \ByJG\Swagger\Exception\NotMatchedException
     * @throws \ByJG\Swagger\Exception\RequiredArgumentNotFound
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
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
