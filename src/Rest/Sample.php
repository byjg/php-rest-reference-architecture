<?php

namespace RestTemplate\Rest;

use ByJG\Config\Exception\ConfigNotFoundException;
use ByJG\Config\Exception\EnvironmentException;
use ByJG\Config\Exception\KeyNotFoundException;
use ByJG\MicroOrm\Exception\OrmBeforeInvalidException;
use ByJG\MicroOrm\Exception\OrmInvalidFieldsException;
use ByJG\RestServer\Exception\Error404Exception;
use ByJG\RestServer\HttpRequest;
use ByJG\RestServer\HttpResponse;
use ByJG\Serializer\BinderObject;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;
use RestTemplate\Model\Dummy;
use RestTemplate\Model\DummyHex;
use RestTemplate\Psr11;
use RestTemplate\Repository\DummyHexRepository;
use RestTemplate\Repository\DummyRepository;

class Sample extends ServiceAbstractBase
{
    /**
     * Simple ping
     *
     * @OA\Get(
     *     path="/sample/ping",
     *     tags={"zz_sample"},
     *     @OA\Response(
     *         response=200,
     *         description="The object",
     *         @OA\MediaType(
     *           mediaType="application/json",
     *           @OA\Schema(
     *             required={"result"},
     *             @OA\Property(property="result", type="string")
     *           )
     *         )
     *     )
     * )
     * @param HttpResponse $response
     * @param HttpRequest $request
     */
    public function getPing()
    {
        $this->getResponse()->write([
            'result' => 'pong'
        ]);
    }

    /**
     * Get the rows from the Dummy table by ID
     *
     * @OA\Get(
     *     path="/sample/dummy/{field}",
     *     tags={"zz_sample"},
     *     @OA\Parameter(
     *         name="field",
     *         in="path",
     *         description="The field to search",
     *         required=true,
     *         @OA\Schema(
     *           type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="The object",
     *         @OA\MediaType(
     *           mediaType="application/json",
     *           @OA\Schema(
     *             type="array",
     *             @OA\Items(@OA\Schema(ref="#/components/schemas/Dummy"))
     *           )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found",
     *         @OA\JsonContent(ref="#/components/schemas/error")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro Geral",
     *         @OA\JsonContent(ref="#/components/schemas/error")
     *     )
     * )
     *
     * @param HttpResponse $response
     * @param HttpRequest $request
     * @throws ConfigNotFoundException
     * @throws EnvironmentException
     * @throws Error404Exception
     * @throws InvalidArgumentException
     * @throws KeyNotFoundException
     * @throws OrmBeforeInvalidException
     * @throws OrmInvalidFieldsException
     * @throws ReflectionException
     * @throws \ByJG\MicroOrm\Exception\InvalidArgumentException
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     */

    public function getDummy()
    {
        $dummyRepo = Psr11::container()->get(DummyRepository::class);
        $field = $request->param('field');

        $result = $dummyRepo->getByField($field);
        if (empty($result)) {
            throw new Error404Exception('Id not found');
        }
        $this->getResponse()->write(
            $result
        );
    }

    /**
     * Insert a new row in the Dummy table
     * @OA\Post(
     *     path="/sample/dummy",
     *     tags={"zz_sample"},
     *     @OA\RequestBody(
     *         description="Dummy object that needs to be added to the store",
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Dummy")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="The object",
     *         @OA\JsonContent(ref="#/components/schemas/Dummy")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found",
     *         @OA\JsonContent(ref="#/components/schemas/error")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro Geral",
     *         @OA\JsonContent(ref="#/components/schemas/error")
     *     )
     * )
     *
     * @param HttpResponse $response
     * @param HttpRequest $request
     * @throws ConfigNotFoundException
     * @throws EnvironmentException
     * @throws Error404Exception
     * @throws InvalidArgumentException
     * @throws KeyNotFoundException
     * @throws OrmBeforeInvalidException
     * @throws OrmInvalidFieldsException
     * @throws ReflectionException
     * @throws \ByJG\MicroOrm\Exception\InvalidArgumentException
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     */
    public function postDummy()
    {
        $this->validateRequest($request);

        $model = new Dummy();
        $payload = json_decode($this->getRequest()->payload());
        BinderObject::bindObject($payload, $model);

        $dummyRepo = Psr11::container()->get(DummyRepository::class);
        $dummyRepo->save($model);
    }

    /**
     * Get the rows from the DummyHex table by ID
     * @SWG\Get(
     *     path="/sample/dummyhex/{id}",
     *     tags={"zz_sample"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="The field content to be returned",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="The object",
     *         @SWG\Schema(ref="#/definitions/DummyHex")
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Not found",
     *         @SWG\Schema(ref="#/definitions/error")
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="Erro Geral",
     *         @SWG\Schema(ref="#/definitions/error")
     *     )
     * )
     *
     * @param HttpResponse $response
     * @param HttpRequest $request
     * @throws ConfigNotFoundException
     * @throws EnvironmentException
     * @throws Error404Exception
     * @throws InvalidArgumentException
     * @throws KeyNotFoundException
     * @throws \ByJG\MicroOrm\Exception\InvalidArgumentException
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     * @throws ReflectionException
     */
    public function getDummyHex($response, $request)
    {
        $dummyRepo = Psr11::container()->get(DummyHexRepository::class);
        $id = $request->param('id');

        $result = $dummyRepo->get($id);
        if (empty($result)) {
            throw new Error404Exception('Id not found');
        }
        $response->write(
            $result
        );
    }

    /**
     * Save data content in the table Dummy Hex
     * @SWG\Post(
     *     path="/sample/dummyhex",
     *     tags={"zz_sample"},
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         description="The dummy data",
     *         required=true,
     *         @SWG\Schema(ref="#/definitions/DummyHex")
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="The object",
     *         @SWG\Schema(ref="#/definitions/DummyHex")
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="Erro Geral",
     *         @SWG\Schema(ref="#/definitions/error")
     *     )
     * )
     *
     * @param HttpResponse $response
     * @param HttpRequest $request
     * @throws ConfigNotFoundException
     * @throws EnvironmentException
     * @throws InvalidArgumentException
     * @throws KeyNotFoundException
     * @throws OrmBeforeInvalidException
     * @throws OrmInvalidFieldsException
     * @throws \ByJG\MicroOrm\Exception\InvalidArgumentException
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     * @throws ReflectionException
     */
    public function postDummyHex($response, $request)
    {
        $model = new DummyHex();
        $payload = json_decode($request->payload());
        BinderObject::bind($payload, $model);

        $dummyRepo = Psr11::container()->get(DummyRepository::class);
        $response->write($dummyRepo->save($model));
    }

    /**
     * Get the rows from the DummyHex table by ID
     * @OA\Get(
     *     path="/sample/dummyhex/{id}",
     *     tags={"zz_sample"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="The field content to be returned",
     *         required=true,
     *         @OA\Schema(
     *           type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="The object",
     *         @OA\JsonContent(ref="#/components/schemas/DummyHex")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found",
     *         @OA\JsonContent(ref="#/components/schemas/error")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro Geral",
     *         @OA\JsonContent(ref="#/components/schemas/error")
     *     )
     * )
     *
     * @param HttpResponse $response
     * @param HttpRequest $request
     * @throws ConfigNotFoundException
     * @throws EnvironmentException
     * @throws Error404Exception
     * @throws InvalidArgumentException
     * @throws KeyNotFoundException
     * @throws \ByJG\MicroOrm\Exception\InvalidArgumentException
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     * @throws ReflectionException
     */
    public function getDummyHex($response, $request)
    {
        $dummyRepo = Psr11::container()->get(DummyHexRepository::class);
        $id = $request->param('id');

        $result = $dummyRepo->get($id);
        if (empty($result)) {
            throw new Error404Exception('Id not found');
        }
        $response->write(
            $result
        );
    }

    /**
     * Save data content in the table Dummy Hex
     * @OA\Post(
     *     path="/sample/dummyhex",
     *     tags={"zz_sample"},
     *     @OA\RequestBody(
     *         description="The dummy data",
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/DummyHex")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="The object",
     *         @OA\JsonContent(ref="#/components/schemas/DummyHex")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro Geral",
     *         @OA\JsonContent(ref="#/components/schemas/error")
     *     )
     * )
     *
     * @param HttpResponse $response
     * @param HttpRequest $request
     * @throws ConfigNotFoundException
     * @throws EnvironmentException
     * @throws InvalidArgumentException
     * @throws KeyNotFoundException
     * @throws OrmBeforeInvalidException
     * @throws OrmInvalidFieldsException
     * @throws \ByJG\MicroOrm\Exception\InvalidArgumentException
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     * @throws ReflectionException
     */
    public function postDummyHex($response, $request)
    {
        $payload = $this->validateRequest($request);
        
        $model = new DummyHex();
        BinderObject::bind($payload, $model);

        $dummyRepo = Psr11::container()->get(DummyHexRepository::class);
        $dummyRepo->save($model);

        $model = $dummyRepo->get($model->getId());

        $response->write($model);
    }
}
