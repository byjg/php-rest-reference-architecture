<?php
/**
 * User: jg
 * Date: 09/08/17
 * Time: 23:03
 */

namespace Test;

use ByJG\Swagger\SwaggerTestCase;

/**
 * Create a TestCase inherited from SwaggerTestCase
 */
class SampleTest extends SwaggerTestCase
{
    protected $filePath = __DIR__ . '/../../../web/docs/swagger.json';

    /**
     * Just test ping
     */
    public function testPing()
    {
        $this->makeRequest('GET', "/sample/ping");
    }

    /**
     * Test Dummy
     */
    public function testDummyOk()
    {
        $this->makeRequest('GET', "/sample/dummy/e");
    }

    /**
     * Test Dummy
     */
    public function testDummyOk2()
    {
        $this->makeRequest('GET', "/sample/dummy/1");
    }

    /**
     * Just test ping
     */
    public function testDummyNotFound()
    {
        $this->makeRequest('GET', "/sample/dummy/not", 404);
    }

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
