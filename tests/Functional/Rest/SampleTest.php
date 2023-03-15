<?php

namespace Test\Functional\Rest;


use ByJG\ApiTools\Base\Schema;
use RestTemplate\Util\FakeApiRequester;

/**
 * Create a TestCase inherited from SwaggerTestCase
 */
class SampleTest extends BaseApiTestCase
{
    protected $filePath = __DIR__ . '/../../../public/docs/openapi.json';

    protected function setUp(): void
    {
        $schema = Schema::getInstance(file_get_contents($this->filePath));
        $this->setSchema($schema);

        parent::setUp();
    }

    /**
     * Just test ping
     */
    public function testPing()
    {
        $request = new FakeApiRequester();
        $request
            ->withPsr7Request($this->getPsr7Request())
            ->withMethod('GET')
            ->withPath("/sample/ping")
        ;
        $this->assertRequest($request);
    }
}
