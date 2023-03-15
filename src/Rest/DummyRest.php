<?php

namespace RestTemplate\Rest;

use ByJG\RestServer\Exception\Error401Exception;
use ByJG\RestServer\Exception\Error404Exception;
use ByJG\RestServer\HttpRequest;
use ByJG\RestServer\HttpResponse;
use ByJG\Serializer\BinderObject;
use RestTemplate\Psr11;
use RestTemplate\Model\Dummy;
use RestTemplate\Repository\DummyRepository;
use OpenApi\Annotations as OA;

class DummyRest extends ServiceAbstractBase
{
    /**
     * Get the Dummy by id
     * @OA\Get(
     *     path="/dummy/{id}",
     *     tags={"Dummy"},
     *     security={{
     *         "jwt-token":{}
     *     }},
     *     @OA\Parameter(
     *         name="id",
     *         description="",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         ) 
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="The object Dummy",
     *         @OA\JsonContent(ref="#/components/schemas/Dummy")
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
    public function getDummy($response, $request)
    {
        $data = $this->requireAuthenticated();

        $dummyRepo = Psr11::container()->get(DummyRepository::class);
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
     * Create a new Dummy 
     * @OA\Post(
     *     path="/dummy",
     *     tags={"Dummy"},
     *     security={{
     *         "jwt-token":{}
     *     }},
     *     @OA\RequestBody(
     *         description="The object Dummy to be created",
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

     *             @OA\Property(property="id", type="int")
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
    public function postDummy($response, $request)
    {
        $data = $this->requireAuthenticated();

        $payload = $this->validateRequest($request);
        
        $model = new Dummy();
        BinderObject::bind($payload, $model);

        $DummyRepo = Psr11::container()->get(DummyRepository::class);
        $DummyRepo->save($model);

        $response->write([ "id" => $model->getId()]);
    }


    /**
     * Update an existing Dummy 
     * @OA\Put(
     *     path="/dummy",
     *     tags={"Dummy"},
     *     security={{
     *         "jwt-token":{}
     *     }},
     *     @OA\RequestBody(
     *         description="The object Dummy to be updated",
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Dummy")
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
    public function putDummy($response, $request)
    {
        $data = $this->requireAuthenticated();

        $payload = $this->validateRequest($request);

        $dummyRepo = Psr11::container()->get(DummyRepository::class);
        $model = $dummyRepo->get($payload['id']);
        if (empty($model)) {
            throw new Error404Exception('Id not found');
        }
        BinderObject::bind($payload, $model);

        $dummyRepo->save($model);
    }

}
