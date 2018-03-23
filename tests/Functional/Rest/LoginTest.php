<?php

namespace Test\Functional\Rest;

use ByJG\Swagger\SwaggerRequester;
use ByJG\Swagger\SwaggerTestCase;

/**
 * Create a TestCase inherited from SwaggerTestCase
 */
class LoginTest extends SwaggerTestCase
{
    protected $filePath = __DIR__ . '/../../../web/docs/swagger.json';

    /**
     * @throws \ByJG\Swagger\Exception\HttpMethodNotFoundException
     * @throws \ByJG\Swagger\Exception\InvalidDefinitionException
     * @throws \ByJG\Swagger\Exception\NotMatchedException
     * @throws \ByJG\Swagger\Exception\PathNotFoundException
     * @throws \ByJG\Swagger\Exception\RequiredArgumentNotFound
     * @throws \Exception
     */
    public function testLoginOk()
    {
        $request = new SwaggerRequester();
        $request
            ->withMethod('POST')
            ->withPath("/login")
            ->assertResponseCode(200)
            ->withRequestBody(Credentials::getAdminUser())
        ;
        $this->assertRequest($request);
    }

    /**
     * @throws \ByJG\Swagger\Exception\HttpMethodNotFoundException
     * @throws \ByJG\Swagger\Exception\InvalidDefinitionException
     * @throws \ByJG\Swagger\Exception\NotMatchedException
     * @throws \ByJG\Swagger\Exception\PathNotFoundException
     * @throws \ByJG\Swagger\Exception\RequiredArgumentNotFound
     * @throws \Exception
     */
    public function testLoginOk2()
    {
        $request = new SwaggerRequester();
        $request
            ->withMethod('POST')
            ->withPath("/login")
            ->assertResponseCode(200)
            ->withRequestBody(Credentials::getRegularUser())
        ;
        $this->assertRequest($request);
    }

    /**
     * @throws \ByJG\Swagger\Exception\HttpMethodNotFoundException
     * @throws \ByJG\Swagger\Exception\InvalidDefinitionException
     * @throws \ByJG\Swagger\Exception\NotMatchedException
     * @throws \ByJG\Swagger\Exception\PathNotFoundException
     * @throws \ByJG\Swagger\Exception\RequiredArgumentNotFound
     * @throws \Exception
     */
    public function testLoginFail()
    {
        $request = new SwaggerRequester();
        $request
            ->withMethod('POST')
            ->withPath("/login")
            ->assertResponseCode(401)
            ->withRequestBody([
                'username' => 'invalid',
                'password' => 'invalid'
            ])
        ;
        $this->assertRequest($request);
    }
}
