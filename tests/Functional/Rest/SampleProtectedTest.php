<?php

namespace Test\Functional\Rest;

use ByJG\Swagger\SwaggerRequester;
use ByJG\Swagger\SwaggerTestCase;

/**
 * Create a TestCase inherited from SwaggerTestCase
 */
class SampleProtectedTest extends SwaggerTestCase
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
    public function testGetUnauthorized()
    {
        $request = new SwaggerRequester();
        $request
            ->withMethod('GET')
            ->withPath("/sampleprotected/ping")
            ->assertResponseCode(401)
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
    public function testGetAuthorized()
    {
        $request = new SwaggerRequester();
        $request
            ->withMethod('POST')
            ->withPath("/login")
            ->assertResponseCode(200)
            ->withRequestBody(Credentials::getAdminUser())
        ;
        $result = $this->assertRequest($request);

        $request = new SwaggerRequester();
        $request
            ->withMethod('GET')
            ->withPath("/sampleprotected/ping")
            ->assertResponseCode(200)
            ->withRequestHeader([
                "Authorization" => "Bearer " . $result['token']
            ])
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
    public function testGetAuthorizedRole1()
    {
        $request = new SwaggerRequester();
        $request
            ->withMethod('POST')
            ->withPath("/login")
            ->assertResponseCode(200)
            ->withRequestBody(Credentials::getAdminUser())
        ;
        $result = $this->assertRequest($request);

        $request = new SwaggerRequester();
        $request
            ->withMethod('GET')
            ->withPath("/sampleprotected/pingadm")
            ->assertResponseCode(200)
            ->withRequestHeader([
                "Authorization" => "Bearer " . $result['token']
            ])
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
    public function testGetAuthorizedRole2()
    {
        $request = new SwaggerRequester();
        $request
            ->withMethod('POST')
            ->withPath("/login")
            ->assertResponseCode(200)
            ->withRequestBody(Credentials::getRegularUser())
        ;
        $result = $this->assertRequest($request);

        $request = new SwaggerRequester();
        $request
            ->withMethod('GET')
            ->withPath("/sampleprotected/pingadm")
            ->assertResponseCode(401)
            ->withRequestHeader([
                "Authorization" => "Bearer " . $result['token']
            ])
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
    public function testAddUser()
    {
        $request = new SwaggerRequester();
        $request
            ->withMethod('POST')
            ->withPath("/login")
            ->assertResponseCode(200)
            ->withRequestBody(Credentials::getAdminUser())
        ;
        $result = $this->assertRequest($request);

        $request = new SwaggerRequester();
        $request
            ->withMethod('POST')
            ->withPath("/sampleprotected/adduser")
            ->assertResponseCode(200)
            ->withRequestBody([
                "name" => 'Test',
                "username" => 'test',
                'email' => 'test@example.com',
                'password' => 'somepass'
            ])
            ->withRequestHeader([
                "Authorization" => "Bearer " . $result['token']
            ])
        ;
        $this->assertRequest($request);
    }
}
