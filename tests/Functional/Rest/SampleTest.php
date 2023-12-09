<?php

namespace Test\Functional\Rest;


use RestTemplate\Util\FakeApiRequester;

/**
 * Create a TestCase inherited from SwaggerTestCase
 */
class SampleTest extends BaseApiTestCase
{

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
