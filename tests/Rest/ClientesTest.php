<?php

namespace Test\Rest;

use ByJG\RestServer\Exception\Error401Exception;
use ByJG\RestServer\Exception\Error403Exception;
use ByJG\Serializer\ObjectCopy;
use RestReferenceArchitecture\Psr11;
use RestReferenceArchitecture\Repository\ClientesRepository;
use RestReferenceArchitecture\Util\FakeApiRequester;
use RestReferenceArchitecture\Model\Clientes;
use RestReferenceArchitecture\Repository\BaseRepository;

class ClientesTest extends BaseApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @return Clientes|array
     */
    protected function getSampleData($array = false)
    {
        $sample = [
            'nome' => 'João Silva ' . uniqid(),
            'email' => 'joao.silva.' . uniqid() . '@email.com',
            'telefone' => '(11) 9' . rand(1000, 9999) . '-' . rand(1000, 9999),
            'cpf' => rand(100, 999) . '.' . rand(100, 999) . '.' . rand(100, 999) . '-' . rand(10, 99),
            'status' => 'ativo',
            'dataCadastro' => '2025-08-26 00:00:00',
        ];

        if ($array) {
            return $sample;
        }

        ObjectCopy::copy($sample, $model = new Clientes());
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
            ->withPath("/clientes/1")
            ->assertResponseCode(401);
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
            ->withPath("/clientes")
            ->assertResponseCode(401);
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
            ->withPath("/clientes")
            ->withRequestBody(json_encode($this->getSampleData(true)))
            ->assertResponseCode(401);
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
            ->withPath("/clientes")
            ->withRequestBody(json_encode($this->getSampleData(true) + ['id' => 1]))
            ->assertResponseCode(401);
        $this->assertRequest($request);
    }

    public function testPostInsufficientPrivileges()
    {
        $this->expectException(Error403Exception::class);
        $this->expectExceptionMessage('Insufficient privileges');

        $result = json_decode($this->assertRequest(Credentials::requestLogin(Credentials::getRegularUser()))->getBody()->getContents(), true);

        $request = new FakeApiRequester();
        $request
            ->withPsr7Request($this->getPsr7Request())
            ->withMethod('POST')
            ->withPath("/clientes")
            ->withRequestBody(json_encode($this->getSampleData(true)))
            ->assertResponseCode(403)
            ->withRequestHeader([
                "Authorization" => "Bearer " . $result['token']
            ]);
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
            ->withPath("/clientes")
            ->withRequestBody(json_encode($this->getSampleData(true) + ['id' => 1]))
            ->assertResponseCode(403)
            ->withRequestHeader([
                "Authorization" => "Bearer " . $result['token']
            ]);
        $this->assertRequest($request);
    }

    public function testFullCrud()
    {
        $result = json_decode($this->assertRequest(Credentials::requestLogin(Credentials::getAdminUser()))->getBody()->getContents(), true);

        $request = new FakeApiRequester();
        $request
            ->withPsr7Request($this->getPsr7Request())
            ->withMethod('POST')
            ->withPath("/clientes")
            ->withRequestBody(json_encode($this->getSampleData(true)))
            ->assertResponseCode(200)
            ->withRequestHeader([
                "Authorization" => "Bearer " . $result['token']
            ]);
        $body = $this->assertRequest($request);
        $bodyAr = json_decode($body->getBody()->getContents(), true);

        $request = new FakeApiRequester();
        $request
            ->withPsr7Request($this->getPsr7Request())
            ->withMethod('GET')
            ->withPath("/clientes/" . $bodyAr['id'])
            ->assertResponseCode(200)
            ->withRequestHeader([
                "Authorization" => "Bearer " . $result['token']
            ]);
        $body = $this->assertRequest($request);

        $request = new FakeApiRequester();
        $request
            ->withPsr7Request($this->getPsr7Request())
            ->withMethod('PUT')
            ->withPath("/clientes")
            ->withRequestBody($body->getBody()->getContents())
            ->assertResponseCode(200)
            ->withRequestHeader([
                "Authorization" => "Bearer " . $result['token']
            ]);
        $this->assertRequest($request);
    }

    public function testList()
    {
        $result = json_decode($this->assertRequest(Credentials::requestLogin(Credentials::getRegularUser()))->getBody()->getContents(), true);

        $request = new FakeApiRequester();
        $request
            ->withPsr7Request($this->getPsr7Request())
            ->withMethod('GET')
            ->withPath("/clientes")
            ->assertResponseCode(200)
            ->withRequestHeader([
                "Authorization" => "Bearer " . $result['token']
            ]);
        $this->assertRequest($request);
    }

    /**
     * Test updating cliente status
     */
    public function testPutStatusSuccess()
    {
        // Authenticate to get a valid token
        $authResult = json_decode(
            $this->assertRequest(Credentials::requestLogin(Credentials::getAdminUser()))
                ->getBody()
                ->getContents(),
            true
        );

        // Prepare test data
        $recordId = 1;
        $newStatus = 'ativo';

        // Create mock API request
        $request = new FakeApiRequester();
        $request
            ->withPsr7Request($this->getPsr7Request())
            ->withMethod('PUT')
            ->withPath("/clientes/status")
            ->withRequestBody(json_encode([
                'id' => $recordId,
                'status' => $newStatus
            ]))
            ->withRequestHeader([
                "Authorization" => "Bearer " . $authResult['token'],
                "Content-Type" => "application/json"
            ])
            ->assertResponseCode(200);

        // Execute the request and get response
        $response = $this->assertRequest($request);
        $responseData = json_decode($response->getBody()->getContents(), true);

        // There is no necessary to Assert expected response format and data
        // because the assertRequest will do it for you.
        // $this->assertIsArray($responseData);
        // $this->assertArrayHasKey('result', $responseData);
        // $this->assertEquals('ok', $responseData['result']);

        // Verify the database was updated correctly
        $repository = Psr11::get(ClientesRepository::class);
        $updatedRecord = $repository->get($recordId);
        $this->assertEquals($newStatus, $updatedRecord->getStatus());
    }


    public function testPutStatusUnauthorized()
    {
        $this->expectException(Error401Exception::class);
        $this->expectExceptionMessage('Absent authorization token');

        $request = new FakeApiRequester();
        $request
            ->withPsr7Request($this->getPsr7Request())
            ->withMethod('PUT')
            ->withPath("/clientes/status")
            ->withRequestBody(json_encode(['id' => 1, 'status' => 'ativo']))
            ->assertResponseCode(401);
        $this->assertRequest($request);
    }

    public function testPutStatusInsufficientPrivileges()
    {
        $this->expectException(Error403Exception::class);
        $this->expectExceptionMessage('Insufficient privileges');

        $result = json_decode($this->assertRequest(Credentials::requestLogin(Credentials::getRegularUser()))->getBody()->getContents(), true);

        $request = new FakeApiRequester();
        $request
            ->withPsr7Request($this->getPsr7Request())
            ->withMethod('PUT')
            ->withPath("/clientes/status")
            ->withRequestBody(json_encode(['id' => 1, 'status' => 'ativo']))
            ->withRequestHeader([
                "Authorization" => "Bearer " . $result['token']
            ])
            ->assertResponseCode(403);
        $this->assertRequest($request);
    }

}
