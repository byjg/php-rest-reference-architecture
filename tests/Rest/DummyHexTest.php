<?php

namespace Test\Rest;

use ByJG\RestServer\Exception\Error401Exception;
use ByJG\RestServer\Exception\Error403Exception;
use ByJG\Serializer\ObjectCopy;
use RestReferenceArchitecture\Model\DummyHex;
use RestReferenceArchitecture\Repository\BaseRepository;
use RestReferenceArchitecture\Util\FakeApiRequester;

class DummyHexTest extends BaseApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @return DummyHex|array
     */
    protected function getSampleData($array = false)
    {
        $sample = [

            'field' => 'field',
        ];

        if ($array) {
            return $sample;
        }

        ObjectCopy::copy($sample, $model = new DummyHex());
        return $model;
    }



    public function testGetUnauthorized()
    {
        $this->expectException(Error401Exception::class);
        $this->expectExceptionMessage('Absent authorization token');

        $request = new FakeApiRequester();
        $request
            ->withPsr7Request($this->getPsr7Request())
            ->withMethod('GET')
            ->withPath("/dummyhex/" . BaseRepository::getUuid())
            ->assertResponseCode(401)
        ;
        $this->assertRequest($request);
    }

    public function testListUnauthorized()
    {
        $this->expectException(Error401Exception::class);
        $this->expectExceptionMessage('Absent authorization token');

        $request = new FakeApiRequester();
        $request
            ->withPsr7Request($this->getPsr7Request())
            ->withMethod('GET')
            ->withPath("/dummyhex/" . BaseRepository::getUuid())
            ->assertResponseCode(401)
        ;
        $this->assertRequest($request);
    }

    public function testPostUnauthorized()
    {
        $this->expectException(Error401Exception::class);
        $this->expectExceptionMessage('Absent authorization token');

        $request = new FakeApiRequester();
        $request
            ->withPsr7Request($this->getPsr7Request())
            ->withMethod('POST')
            ->withPath("/dummyhex")
            ->withRequestBody(json_encode($this->getSampleData(true)))
            ->assertResponseCode(401)
        ;
        $this->assertRequest($request);
    }

    public function testPutUnauthorized()
    {
        $this->expectException(Error401Exception::class);
        $this->expectExceptionMessage('Absent authorization token');

        $request = new FakeApiRequester();
        $request
            ->withPsr7Request($this->getPsr7Request())
            ->withMethod('PUT')
            ->withPath("/dummyhex")
            ->withRequestBody(json_encode($this->getSampleData(true) + ['id' => BaseRepository::getUuid()]))
            ->assertResponseCode(401)
        ;
        $this->assertRequest($request);
    }

    public function testPostInsufficientPrivileges()
    {
        $result = json_decode($this->assertRequest(Credentials::requestLogin(Credentials::getRegularUser()))->getBody()->getContents(), true);

        $this->expectException(Error403Exception::class);
        $this->expectExceptionMessage('Insufficient privileges');

        $request = new FakeApiRequester();
        $request
            ->withPsr7Request($this->getPsr7Request())
            ->withMethod('POST')
            ->withPath("/dummyhex")
            ->withRequestBody(json_encode($this->getSampleData(true)))
            ->assertResponseCode(403)
            ->withRequestHeader([
                "Authorization" => "Bearer " . $result['token']
            ])
        ;
        $this->assertRequest($request);
    }

    public function testPutInsufficientPrivileges()
    {
        $this->expectException(Error403Exception::class);
        $this->expectExceptionMessage('Insufficient privileges');

        $result = json_decode($this->assertRequest(Credentials::requestLogin(Credentials::getRegularUser()))->getBody()->getContents(), true);

        $request = new FakeApiRequester();
        $request
            ->withPsr7Request($this->getPsr7Request())
            ->withMethod('PUT')
            ->withPath("/dummyhex")
            ->withRequestBody(json_encode($this->getSampleData(true) + ['id' => BaseRepository::getUuid()]))
            ->assertResponseCode(403)
            ->withRequestHeader([
                "Authorization" => "Bearer " . $result['token']
            ])
        ;
        $this->assertRequest($request);
    }

    public function testFullCrud()
    {
        $result = json_decode($this->assertRequest(Credentials::requestLogin(Credentials::getAdminUser()))->getBody()->getContents(), true);

        $request = new FakeApiRequester();
        $request
            ->withPsr7Request($this->getPsr7Request())
            ->withMethod('POST')
            ->withPath("/dummyhex")
            ->withRequestBody(json_encode($this->getSampleData(true)))
            ->assertResponseCode(200)
            ->withRequestHeader([
                "Authorization" => "Bearer " . $result['token']
            ])
        ;
        $body = $this->assertRequest($request);
        $bodyAr = json_decode($body->getBody()->getContents(), true);

        $request = new FakeApiRequester();
        $request
            ->withPsr7Request($this->getPsr7Request())
            ->withMethod('GET')
            ->withPath("/dummyhex/" . $bodyAr['id'])
            ->assertResponseCode(200)
            ->withRequestHeader([
                "Authorization" => "Bearer " . $result['token']
            ])
        ;
        $body = $this->assertRequest($request);

        $request = new FakeApiRequester();
        $request
            ->withPsr7Request($this->getPsr7Request())
            ->withMethod('PUT')
            ->withPath("/dummyhex")
            ->withRequestBody($body->getBody()->getContents())
            ->assertResponseCode(200)
            ->withRequestHeader([
                "Authorization" => "Bearer " . $result['token']
            ])
        ;
        $this->assertRequest($request);
    }

    public function testList()
    {
        $result = json_decode($this->assertRequest(Credentials::requestLogin(Credentials::getRegularUser()))->getBody()->getContents(), true);

        $request = new FakeApiRequester();
        $request
            ->withPsr7Request($this->getPsr7Request())
            ->withMethod('GET')
            ->withPath("/dummyhex")
            ->assertResponseCode(200)
            ->withRequestHeader([
                "Authorization" => "Bearer " . $result['token']
            ])
        ;
        $this->assertRequest($request);
    }
}
