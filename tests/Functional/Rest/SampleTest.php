<?php

namespace Test\Functional\Rest;

use ByJG\ApiTools\ApiRequester;
use ByJG\ApiTools\ApiTestCase;

/**
 * Create a TestCase inherited from SwaggerTestCase
 */
class SampleTest extends ApiTestCase
{
    protected $filePath = __DIR__ . '/../../../web/docs/swagger.json';

    /**
     * Just test ping
     *
     * @throws \ByJG\ApiTools\Exception\DefinitionNotFoundException
     * @throws \ByJG\ApiTools\Exception\HttpMethodNotFoundException
     * @throws \ByJG\ApiTools\Exception\InvalidDefinitionException
     * @throws \ByJG\ApiTools\Exception\NotMatchedException
     * @throws \ByJG\ApiTools\Exception\PathNotFoundException
     * @throws \ByJG\ApiTools\Exception\StatusCodeNotMatchedException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testPing()
    {
        $request = new ApiRequester();
        $request
            ->withMethod('GET')
            ->withPath("/sample/ping")
        ;
        $this->assertRequest($request);
    }

    /**
     * Test Dummy
     *
     * @throws \ByJG\ApiTools\Exception\DefinitionNotFoundException
     * @throws \ByJG\ApiTools\Exception\HttpMethodNotFoundException
     * @throws \ByJG\ApiTools\Exception\InvalidDefinitionException
     * @throws \ByJG\ApiTools\Exception\NotMatchedException
     * @throws \ByJG\ApiTools\Exception\PathNotFoundException
     * @throws \ByJG\ApiTools\Exception\StatusCodeNotMatchedException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testDummyOk()
    {
        $request = new ApiRequester();
        $request
            ->withMethod('GET')
            ->withPath("/sample/dummy/e")
        ;
        $this->assertRequest($request);
    }

    /**
     * Test Dummy
     *
     * @throws \ByJG\ApiTools\Exception\DefinitionNotFoundException
     * @throws \ByJG\ApiTools\Exception\HttpMethodNotFoundException
     * @throws \ByJG\ApiTools\Exception\InvalidDefinitionException
     * @throws \ByJG\ApiTools\Exception\NotMatchedException
     * @throws \ByJG\ApiTools\Exception\PathNotFoundException
     * @throws \ByJG\ApiTools\Exception\StatusCodeNotMatchedException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testDummyOk2()
    {
        $request = new ApiRequester();
        $request
            ->withMethod('GET')
            ->withPath("/sample/dummy/1")
        ;
        $this->assertRequest($request);
    }

    /**
     * Just test ping
     *
     * @throws \ByJG\ApiTools\Exception\DefinitionNotFoundException
     * @throws \ByJG\ApiTools\Exception\HttpMethodNotFoundException
     * @throws \ByJG\ApiTools\Exception\InvalidDefinitionException
     * @throws \ByJG\ApiTools\Exception\NotMatchedException
     * @throws \ByJG\ApiTools\Exception\PathNotFoundException
     * @throws \ByJG\ApiTools\Exception\StatusCodeNotMatchedException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testDummyNotFound()
    {
        $request = new ApiRequester();
        $request
            ->withMethod('GET')
            ->withPath("/sample/dummy/not")
            ->assertResponseCode(404)
        ;
        $this->assertRequest($request);
    }

    /**
     * @throws \ByJG\ApiTools\Exception\DefinitionNotFoundException
     * @throws \ByJG\ApiTools\Exception\HttpMethodNotFoundException
     * @throws \ByJG\ApiTools\Exception\InvalidDefinitionException
     * @throws \ByJG\ApiTools\Exception\NotMatchedException
     * @throws \ByJG\ApiTools\Exception\PathNotFoundException
     * @throws \ByJG\ApiTools\Exception\StatusCodeNotMatchedException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testDummySaveOk()
    {
        $request = new ApiRequester();
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

    /**
     * Assert that the DummyHex Fail
     *
     * @throws \ByJG\ApiTools\Exception\DefinitionNotFoundException
     * @throws \ByJG\ApiTools\Exception\HttpMethodNotFoundException
     * @throws \ByJG\ApiTools\Exception\InvalidDefinitionException
     * @throws \ByJG\ApiTools\Exception\NotMatchedException
     * @throws \ByJG\ApiTools\Exception\PathNotFoundException
     * @throws \ByJG\ApiTools\Exception\StatusCodeNotMatchedException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testDummyHexFail()
    {
        $request = new ApiRequester();
        $request
            ->withMethod('GET')
            ->withPath("/sample/dummyhex/not")
            ->assertResponseCode(404)
        ;
        $this->assertRequest($request);
    }

    /**
     * Assert that the DummyHex not found
     *
     * @throws \ByJG\ApiTools\Exception\DefinitionNotFoundException
     * @throws \ByJG\ApiTools\Exception\HttpMethodNotFoundException
     * @throws \ByJG\ApiTools\Exception\InvalidDefinitionException
     * @throws \ByJG\ApiTools\Exception\NotMatchedException
     * @throws \ByJG\ApiTools\Exception\PathNotFoundException
     * @throws \ByJG\ApiTools\Exception\StatusCodeNotMatchedException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testDummyHexOK()
    {
        $request = new ApiRequester();
        $request
            ->withMethod('GET')
            ->withPath("/sample/dummyhex/11111111-2222-3333-4444-555555555555")
            ->assertResponseCode(200)
        ;
        $this->assertRequest($request);
    }

    /**
     * Assert that the DummyHex not found
     *
     * @throws \ByJG\ApiTools\Exception\DefinitionNotFoundException
     * @throws \ByJG\ApiTools\Exception\HttpMethodNotFoundException
     * @throws \ByJG\ApiTools\Exception\InvalidDefinitionException
     * @throws \ByJG\ApiTools\Exception\NotMatchedException
     * @throws \ByJG\ApiTools\Exception\PathNotFoundException
     * @throws \ByJG\ApiTools\Exception\StatusCodeNotMatchedException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testDummyHexNotFound()
    {
        $request = new ApiRequester();
        $request
            ->withMethod('GET')
            ->withPath("/sample/dummyhex/00000000-0000-0000-0000-000000000000")
            ->assertResponseCode(404)
        ;
        $this->assertRequest($request);
    }

    /**
     * @throws \ByJG\ApiTools\Exception\DefinitionNotFoundException
     * @throws \ByJG\ApiTools\Exception\HttpMethodNotFoundException
     * @throws \ByJG\ApiTools\Exception\InvalidDefinitionException
     * @throws \ByJG\ApiTools\Exception\NotMatchedException
     * @throws \ByJG\ApiTools\Exception\PathNotFoundException
     * @throws \ByJG\ApiTools\Exception\StatusCodeNotMatchedException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testDummyHexSaveOk()
    {
        $request = new ApiRequester();
        $request
            ->withMethod('POST')
            ->withPath("/sample/dummyhex")
            ->assertResponseCode(200)
            ->withRequestBody([
                'field' => 'new field'
            ])
        ;
        $this->assertRequest($request);
    }
}
