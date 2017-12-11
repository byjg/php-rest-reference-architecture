<?php

namespace Test\Functional\Rest;

/**
 * Create a TestCase inherited from SwaggerTestCase
 */
class SampleProtectedTest extends \ByJG\Swagger\SwaggerTestCase
{
    protected $filePath = __DIR__ . '/../../../web/docs/swagger.json';

    /**
     * @throws \ByJG\Swagger\Exception\InvalidDefinitionException
     * @throws \ByJG\Swagger\Exception\NotMatchedException
     * @throws \ByJG\Swagger\Exception\RequiredArgumentNotFound
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testGetUnauthorized()
    {
        $this->makeRequest('GET', "/sampleprotected/ping", 401);
    }

    /**
     * @throws \ByJG\Swagger\Exception\InvalidDefinitionException
     * @throws \ByJG\Swagger\Exception\NotMatchedException
     * @throws \ByJG\Swagger\Exception\RequiredArgumentNotFound
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testGetAuthorized()
    {
        $result = $this->makeRequest(
            'POST',             // The method
            "/login",             // The path defined in the swagger.json
            200,           // The expected status code
            null,                 // The parameters 'in path'
            Credentials::getAdminUser()
        );

        $this->makeRequest(
            'GET',
            "/sampleprotected/ping",
            200,
            null,
            null,
            [
                "Authorization" => "Bearer " . $result['token']
            ]
        );
    }

    /**
     * @throws \ByJG\Swagger\Exception\InvalidDefinitionException
     * @throws \ByJG\Swagger\Exception\NotMatchedException
     * @throws \ByJG\Swagger\Exception\RequiredArgumentNotFound
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testGetAuthorizedRole1()
    {
        $result = $this->makeRequest(
            'POST',             // The method
            "/login",             // The path defined in the swagger.json
            200,           // The expected status code
            null,                 // The parameters 'in path'
            Credentials::getAdminUser()
        );
        $this->makeRequest(
            'GET',
            "/sampleprotected/pingadm",
            200,
            null,
            null,
            [
                "Authorization" => "Bearer " . $result['token']
            ]
        );
    }

    /**
     * @throws \ByJG\Swagger\Exception\InvalidDefinitionException
     * @throws \ByJG\Swagger\Exception\NotMatchedException
     * @throws \ByJG\Swagger\Exception\RequiredArgumentNotFound
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testGetAuthorizedRole2()
    {
        $result = $this->makeRequest(
            'POST',             // The method
            "/login",             // The path defined in the swagger.json
            200,           // The expected status code
            null,                 // The parameters 'in path'
            Credentials::getRegularUser()
        );
        $this->makeRequest(
            'GET',
            "/sampleprotected/pingadm",
            401,
            null,
            null,
            [
                "Authorization" => "Bearer " . $result['token']
            ]
        );
    }
}
