<?php

namespace Test\Functional\Rest;

use ByJG\ApiTools\ApiRequester;
use ByJG\ApiTools\ApiTestCase;

/**
 * Create a TestCase inherited from SwaggerTestCase
 */
class LoginTest extends ApiTestCase
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
    public function testLoginOk()
    {
        $request = new ApiRequester();
        $request
            ->withMethod('POST')
            ->withPath("/login")
            ->assertResponseCode(200)
            ->withRequestBody(Credentials::getAdminUser())
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
    public function testLoginOk2()
    {
        $request = new ApiRequester();
        $request
            ->withMethod('POST')
            ->withPath("/login")
            ->assertResponseCode(200)
            ->withRequestBody(Credentials::getRegularUser())
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
    public function testLoginFail()
    {
        $request = new ApiRequester();
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
