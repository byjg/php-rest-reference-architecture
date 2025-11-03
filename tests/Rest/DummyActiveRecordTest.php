<?php

namespace Test\Rest;

use ByJG\RestServer\Exception\Error401Exception;
use ByJG\Serializer\ObjectCopy;
use RestReferenceArchitecture\Model\DummyActiveRecord;
use RestReferenceArchitecture\Util\FakeApiRequester;

class DummyActiveRecordTest extends BaseApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @return DummyActiveRecord|array
     */
    protected function getSampleData($array = false)
    {
        $sample = [
            'name' => 'Test ActiveRecord',
            'value' => 'Test Value',
        ];

        if ($array) {
            return $sample;
        }

        ObjectCopy::copy($sample, $model = new DummyActiveRecord());
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
            ->withPath("/dummyactiverecord/1")
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
            ->withPath("/dummyactiverecord")
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
            ->withPath("/dummyactiverecord")
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
            ->withPath("/dummyactiverecord/1")
            ->withRequestBody(json_encode($this->getSampleData(true)))
            ->assertResponseCode(401)
        ;
        $this->assertRequest($request);
    }

    public function testDeleteUnauthorized()
    {
        $this->expectException(Error401Exception::class);
        $this->expectExceptionMessage('Absent authorization token');

        $request = new FakeApiRequester();
        $request
            ->withPsr7Request($this->getPsr7Request())
            ->withMethod('DELETE')
            ->withPath("/dummyactiverecord/1")
            ->assertResponseCode(401)
        ;
        $this->assertRequest($request);
    }

    public function testFullCrud()
    {
        // Authenticate as admin
        $result = json_decode($this->assertRequest(Credentials::requestLogin(Credentials::getAdminUser()))->getBody()->getContents(), true);

        // POST - Create
        $request = new FakeApiRequester();
        $request
            ->withPsr7Request($this->getPsr7Request())
            ->withMethod('POST')
            ->withPath("/dummyactiverecord")
            ->withRequestBody(json_encode($this->getSampleData(true)))
            ->assertResponseCode(200)
            ->withRequestHeader([
                "Authorization" => "Bearer " . $result['token']
            ])
        ;
        $body = $this->assertRequest($request);
        $bodyAr = json_decode($body->getBody()->getContents(), true);
        $this->assertArrayHasKey('id', $bodyAr);
        $createdId = $bodyAr['id'];

        // GET - Read
        $request = new FakeApiRequester();
        $request
            ->withPsr7Request($this->getPsr7Request())
            ->withMethod('GET')
            ->withPath("/dummyactiverecord/" . $createdId)
            ->assertResponseCode(200)
            ->withRequestHeader([
                "Authorization" => "Bearer " . $result['token']
            ])
        ;
        $body = $this->assertRequest($request);
        $getResult = json_decode($body->getBody()->getContents(), true);
        $this->assertEquals('Test ActiveRecord', $getResult['name']);
        $this->assertEquals('Test Value', $getResult['value']);

        // PUT - Update
        $updateData = $this->getSampleData(true);
        $updateData['name'] = 'Updated ActiveRecord';
        $request = new FakeApiRequester();
        $request
            ->withPsr7Request($this->getPsr7Request())
            ->withMethod('PUT')
            ->withPath("/dummyactiverecord/" . $createdId)
            ->withRequestBody(json_encode($updateData))
            ->assertResponseCode(200)
            ->withRequestHeader([
                "Authorization" => "Bearer " . $result['token']
            ])
        ;
        $body = $this->assertRequest($request);
        $updateResult = json_decode($body->getBody()->getContents(), true);
        $this->assertEquals('Updated ActiveRecord', $updateResult['name']);

        // DELETE
        $request = new FakeApiRequester();
        $request
            ->withPsr7Request($this->getPsr7Request())
            ->withMethod('DELETE')
            ->withPath("/dummyactiverecord/" . $createdId)
            ->assertResponseCode(200)
            ->withRequestHeader([
                "Authorization" => "Bearer " . $result['token']
            ])
        ;
        $body = $this->assertRequest($request);
        $deleteResult = json_decode($body->getBody()->getContents(), true);
        $this->assertEquals('deleted', $deleteResult['result']);
    }

    public function testList()
    {
        // Authenticate as regular user (list only requires authentication, not admin)
        $result = json_decode($this->assertRequest(Credentials::requestLogin(Credentials::getRegularUser()))->getBody()->getContents(), true);

        $request = new FakeApiRequester();
        $request
            ->withPsr7Request($this->getPsr7Request())
            ->withMethod('GET')
            ->withPath("/dummyactiverecord")
            ->assertResponseCode(200)
            ->withRequestHeader([
                "Authorization" => "Bearer " . $result['token']
            ])
        ;
        $body = $this->assertRequest($request);
        $listResult = json_decode($body->getBody()->getContents(), true);
        $this->assertIsArray($listResult);
    }
}
