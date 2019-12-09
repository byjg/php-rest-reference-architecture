<?php

namespace Test\Functional\Rest;

use ByJG\ApiTools\ApiRequester;
use ByJG\ApiTools\ApiTestCase;

/**
 * Create a TestCase inherited from SwaggerTestCase
 */
class SampleProtectedTest extends ApiTestCase
{
    protected $filePath = __DIR__ . '/../../../web/docs/swagger.json';

    /**
     * @throws \ByJG\ApiTools\Exception\DefinitionNotFoundException
     * @throws \ByJG\ApiTools\Exception\HttpMethodNotFoundException
     * @throws \ByJG\ApiTools\Exception\InvalidDefinitionException
     * @throws \ByJG\ApiTools\Exception\NotMatchedException
     * @throws \ByJG\ApiTools\Exception\PathNotFoundException
     * @throws \ByJG\ApiTools\Exception\StatusCodeNotMatchedException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testGetUnauthorized()
    {
        $request = new ApiRequester();
        $request
            ->withMethod('GET')
            ->withPath("/sampleprotected/ping")
            ->assertResponseCode(401)
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
    public function testGetAuthorized()
    {
        $request = new ApiRequester();
        $request
            ->withMethod('POST')
            ->withPath("/login")
            ->assertResponseCode(200)
            ->withRequestBody(Credentials::getAdminUser())
        ;
        $result = $this->assertRequest($request);

        $request = new ApiRequester();
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
     * @throws \ByJG\ApiTools\Exception\DefinitionNotFoundException
     * @throws \ByJG\ApiTools\Exception\HttpMethodNotFoundException
     * @throws \ByJG\ApiTools\Exception\InvalidDefinitionException
     * @throws \ByJG\ApiTools\Exception\NotMatchedException
     * @throws \ByJG\ApiTools\Exception\PathNotFoundException
     * @throws \ByJG\ApiTools\Exception\StatusCodeNotMatchedException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testGetAuthorizedRole1()
    {
        $request = new ApiRequester();
        $request
            ->withMethod('POST')
            ->withPath("/login")
            ->assertResponseCode(200)
            ->withRequestBody(Credentials::getAdminUser())
        ;
        $result = $this->assertRequest($request);

        $request = new ApiRequester();
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
     * @throws \ByJG\ApiTools\Exception\DefinitionNotFoundException
     * @throws \ByJG\ApiTools\Exception\HttpMethodNotFoundException
     * @throws \ByJG\ApiTools\Exception\InvalidDefinitionException
     * @throws \ByJG\ApiTools\Exception\NotMatchedException
     * @throws \ByJG\ApiTools\Exception\PathNotFoundException
     * @throws \ByJG\ApiTools\Exception\StatusCodeNotMatchedException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testGetAuthorizedRole2()
    {
        $request = new ApiRequester();
        $request
            ->withMethod('POST')
            ->withPath("/login")
            ->assertResponseCode(200)
            ->withRequestBody(Credentials::getRegularUser())
        ;
        $result = $this->assertRequest($request);

        $request = new ApiRequester();
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
     * @throws \ByJG\ApiTools\Exception\DefinitionNotFoundException
     * @throws \ByJG\ApiTools\Exception\HttpMethodNotFoundException
     * @throws \ByJG\ApiTools\Exception\InvalidDefinitionException
     * @throws \ByJG\ApiTools\Exception\NotMatchedException
     * @throws \ByJG\ApiTools\Exception\PathNotFoundException
     * @throws \ByJG\ApiTools\Exception\StatusCodeNotMatchedException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testAddUser()
    {
        $request = new ApiRequester();
        $request
            ->withMethod('POST')
            ->withPath("/login")
            ->assertResponseCode(200)
            ->withRequestBody(Credentials::getAdminUser())
        ;
        $result = $this->assertRequest($request);

        $request = new ApiRequester();
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
