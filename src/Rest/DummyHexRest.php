<?php

namespace RestTemplate\Rest;

use ByJG\RestServer\Exception\Error401Exception;
use ByJG\RestServer\Exception\Error404Exception;
use ByJG\RestServer\HttpRequest;
use ByJG\RestServer\HttpResponse;
use ByJG\Serializer\BinderObject;
use RestTemplate\Model\DummyHex;
use RestTemplate\Psr11;
use RestTemplate\Repository\DummyHexRepository;

class DummyHexRest extends ServiceAbstractBase
{
    /**
     * Get the DummyHex by id
     * @OA\Get(
     *     path="/dummyhex/{id}",
     *     tags={"DummyHex"},
     *     security={{
     *         "jwt-token":{}
     *     }},
     *     @OA\Parameter(
     *         name="id",
     *         description="",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="The object DummyHex",
     *         @OA\JsonContent(ref="#/components/schemas/DummyHex")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Not Authorized",
     *         @OA\JsonContent(ref="#/components/schemas/error")
     *     )
     * )
     *
     * @param HttpResponse $response
     * @param HttpRequest $request
     * @throws Error401Exception
     * @throws InvalidArgumentException
     */
    public function getDummyHex($response, $request)
    {
        $data = $this->requireAuthenticated();

        $dummyhexRepo = Psr11::container()->get(DummyHexRepository::class);
        $id = $request->param('id');

        $result = $dummyhexRepo->get($id);
        if (empty($result)) {
            throw new Error404Exception('Id not found');
        }
        $response->write(
            $result
        );
    }

    /**
     * Create a new DummyHex
     * @OA\Post(
     *     path="/dummyhex",
     *     tags={"DummyHex"},
     *     security={{
     *         "jwt-token":{}
     *     }},
     *     @OA\RequestBody(
     *         description="The object DummyHex to be created",
     *         required=true,
     *         @OA\MediaType(
     *           mediaType="application/json",
     *           @OA\Schema(
     *

     *             @OA\Property(property="field", type="string", nullable=true)
     *           )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="The id of the object created",
     *         @OA\MediaType(
     *           mediaType="application/json",
     *           @OA\Schema(
     *             required={ "id" },

     *             @OA\Property(property="id", type="string")
     *           )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Not Authorized",
     *         @OA\JsonContent(ref="#/components/schemas/error")
     *     )
     * )
     *
     * @param HttpResponse $response
     * @param HttpRequest $request
     * @throws Error401Exception
     * @throws InvalidArgumentException
     */
    public function postDummyHex($response, $request)
    {
        $data = $this->requireAuthenticated();

        $payload = $this->validateRequest($request);

        $model = new DummyHex();
        BinderObject::bind($payload, $model);

        $dummyhexRepo = Psr11::container()->get(DummyHexRepository::class);
        $dummyhexRepo->save($model);

        $response->write([ "id" => $model->getId()]);
    }


    /**
     * Update an existing DummyHex
     * @OA\Put(
     *     path="/dummyhex",
     *     tags={"DummyHex"},
     *     security={{
     *         "jwt-token":{}
     *     }},
     *     @OA\RequestBody(
     *         description="The object DummyHex to be updated",
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/DummyHex")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Nothing to return"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Not Authorized",
     *         @OA\JsonContent(ref="#/components/schemas/error")
     *     )
     * )
     *
     * @param HttpResponse $response
     * @param HttpRequest $request
     * @throws Error401Exception
     * @throws InvalidArgumentException
     */
    public function putDummyHex($response, $request)
    {
        $data = $this->requireAuthenticated();

        $payload = $this->validateRequest($request);

        $dummyhexRepo = Psr11::container()->get(DummyHexRepository::class);
        $model = $dummyhexRepo->get($payload['id']);
        if (empty($model)) {
            throw new Error404Exception('Id not found');
        }
        BinderObject::bind($payload, $model);

        $dummyhexRepo->save($model);
    }

}
