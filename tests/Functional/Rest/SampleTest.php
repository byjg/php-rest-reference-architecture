<?php

namespace Test\Functional\Rest;

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
     * @throws \ByJG\Swagger\Exception\InvalidDefinitionException
     * @throws \ByJG\Swagger\Exception\NotMatchedException
     * @throws \ByJG\Swagger\Exception\RequiredArgumentNotFound
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testPing()
    {
        $this->makeRequest('GET', "/sample/ping");
    }

    /**
     * Test Dummy
     *
     * @throws \ByJG\Swagger\Exception\InvalidDefinitionException
     * @throws \ByJG\Swagger\Exception\NotMatchedException
     * @throws \ByJG\Swagger\Exception\RequiredArgumentNotFound
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testDummyOk()
    {
        $this->makeRequest('GET', "/sample/dummy/e");
    }

    /**
     * Test Dummy
     *
     * @throws \ByJG\Swagger\Exception\InvalidDefinitionException
     * @throws \ByJG\Swagger\Exception\NotMatchedException
     * @throws \ByJG\Swagger\Exception\RequiredArgumentNotFound
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testDummyOk2()
    {
        $this->makeRequest('GET', "/sample/dummy/1");
    }

    /**
     * Just test ping
     *
     * @throws \ByJG\Swagger\Exception\InvalidDefinitionException
     * @throws \ByJG\Swagger\Exception\NotMatchedException
     * @throws \ByJG\Swagger\Exception\RequiredArgumentNotFound
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testDummyNotFound()
    {
        $this->makeRequest('GET', "/sample/dummy/not", 404);
    }

    /**
     * @throws \ByJG\Swagger\Exception\InvalidDefinitionException
     * @throws \ByJG\Swagger\Exception\NotMatchedException
     * @throws \ByJG\Swagger\Exception\RequiredArgumentNotFound
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testDummySaveOk()
    {
        $this->makeRequest(
            'POST',             // The method
            "/sample/dummy",             // The path defined in the swagger.json
            200,           // The expected status code
            null,                 // The parameters 'in path'
            [
                'field' => 'new field'
            ]  // The request body
        );
    }
}
