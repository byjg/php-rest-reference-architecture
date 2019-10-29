<?php

namespace Test\Functional\Rest;

use ByJG\Swagger\SwaggerRequester;
use ByJG\Swagger\SwaggerTestCase;

/**
 * Create a TestCase inherited from SwaggerTestCase
 */
class SampleTest extends SwaggerTestCase
{
    protected $filePath = __DIR__ . '/../../../web/docs/swagger.json';

    /**
     * Just test ping
     *
     * @throws \ByJG\Swagger\Exception\DefinitionNotFoundException
     * @throws \ByJG\Swagger\Exception\GenericSwaggerException
     * @throws \ByJG\Swagger\Exception\HttpMethodNotFoundException
     * @throws \ByJG\Swagger\Exception\InvalidDefinitionException
     * @throws \ByJG\Swagger\Exception\InvalidRequestException
     * @throws \ByJG\Swagger\Exception\NotMatchedException
     * @throws \ByJG\Swagger\Exception\PathNotFoundException
     * @throws \ByJG\Swagger\Exception\RequiredArgumentNotFound
     * @throws \ByJG\Swagger\Exception\StatusCodeNotMatchedException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testPing()
    {
        $request = new SwaggerRequester();
        $request
            ->withMethod('GET')
            ->withPath("/sample/ping")
        ;
        $this->assertRequest($request);
    }

    /**
     * Test Dummy
     *
     * @throws \ByJG\Swagger\Exception\DefinitionNotFoundException
     * @throws \ByJG\Swagger\Exception\GenericSwaggerException
     * @throws \ByJG\Swagger\Exception\HttpMethodNotFoundException
     * @throws \ByJG\Swagger\Exception\InvalidDefinitionException
     * @throws \ByJG\Swagger\Exception\InvalidRequestException
     * @throws \ByJG\Swagger\Exception\NotMatchedException
     * @throws \ByJG\Swagger\Exception\PathNotFoundException
     * @throws \ByJG\Swagger\Exception\RequiredArgumentNotFound
     * @throws \ByJG\Swagger\Exception\StatusCodeNotMatchedException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testDummyOk()
    {
        $request = new SwaggerRequester();
        $request
            ->withMethod('GET')
            ->withPath("/sample/dummy/e")
        ;
        $this->assertRequest($request);
    }

    /**
     * Test Dummy
     *
     * @throws \ByJG\Swagger\Exception\DefinitionNotFoundException
     * @throws \ByJG\Swagger\Exception\GenericSwaggerException
     * @throws \ByJG\Swagger\Exception\HttpMethodNotFoundException
     * @throws \ByJG\Swagger\Exception\InvalidDefinitionException
     * @throws \ByJG\Swagger\Exception\InvalidRequestException
     * @throws \ByJG\Swagger\Exception\NotMatchedException
     * @throws \ByJG\Swagger\Exception\PathNotFoundException
     * @throws \ByJG\Swagger\Exception\RequiredArgumentNotFound
     * @throws \ByJG\Swagger\Exception\StatusCodeNotMatchedException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testDummyOk2()
    {
        $request = new SwaggerRequester();
        $request
            ->withMethod('GET')
            ->withPath("/sample/dummy/1")
        ;
        $this->assertRequest($request);
    }

    /**
     * Just test ping
     *
     * @throws \ByJG\Swagger\Exception\DefinitionNotFoundException
     * @throws \ByJG\Swagger\Exception\GenericSwaggerException
     * @throws \ByJG\Swagger\Exception\HttpMethodNotFoundException
     * @throws \ByJG\Swagger\Exception\InvalidDefinitionException
     * @throws \ByJG\Swagger\Exception\InvalidRequestException
     * @throws \ByJG\Swagger\Exception\NotMatchedException
     * @throws \ByJG\Swagger\Exception\PathNotFoundException
     * @throws \ByJG\Swagger\Exception\RequiredArgumentNotFound
     * @throws \ByJG\Swagger\Exception\StatusCodeNotMatchedException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testDummyNotFound()
    {
        $request = new SwaggerRequester();
        $request
            ->withMethod('GET')
            ->withPath("/sample/dummy/not")
            ->assertResponseCode(404)
        ;
        $this->assertRequest($request);
    }

    /**
     * @throws \ByJG\Swagger\Exception\DefinitionNotFoundException
     * @throws \ByJG\Swagger\Exception\GenericSwaggerException
     * @throws \ByJG\Swagger\Exception\HttpMethodNotFoundException
     * @throws \ByJG\Swagger\Exception\InvalidDefinitionException
     * @throws \ByJG\Swagger\Exception\InvalidRequestException
     * @throws \ByJG\Swagger\Exception\NotMatchedException
     * @throws \ByJG\Swagger\Exception\PathNotFoundException
     * @throws \ByJG\Swagger\Exception\RequiredArgumentNotFound
     * @throws \ByJG\Swagger\Exception\StatusCodeNotMatchedException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testDummySaveOk()
    {
        $request = new SwaggerRequester();
        $request
            ->withMethod('POST')
            ->withPath("/sample/dummy")
            ->assertResponseCode(200)
            ->withRequestBody([
                'field' => 'new field'
            ])
        ;
        $this->assertRequest($request);
    }
}
