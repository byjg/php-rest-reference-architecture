<?php

namespace RestTemplate\Rest;

use ByJG\MicroOrm\Literal;
use ByJG\RestServer\Exception\Error401Exception;
use ByJG\RestServer\Exception\Error404Exception;
use ByJG\RestServer\HttpRequest;
use ByJG\RestServer\HttpResponse;
use ByJG\Serializer\BinderObject;
use RestTemplate\Psr11;
use RestTemplate\Model\DummyHex;
use RestTemplate\Repository\DummyHexRepository;
use OpenApi\Annotations as OA;
use RestTemplate\Util\HexUuidLiteral;

class DummyHexRest extends ServiceAbstractBase
{
    /**
     * Get the DummyHex by id
     * @OA\Get(
     *     path="/dummyhex/{id}",
     *     tags={"Dummyhex"},
     *     security={{
     *         "jwt-token":{}
     *     }},
     *     @OA\Parameter(
     *         name="id",
     *         description="",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             format="string"
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

        $dummyHexRepo = Psr11::container()->get(DummyHexRepository::class);
        $id = $request->param('id');

        $result = $dummyHexRepo->get($id);
        if (empty($result)) {
            throw new Error404Exception('Id not found');
        }
        $response->write(
            $result
        );
    }

    /**
     * List DummyHex
     * @OA\Get(
     *    path="/dummyhex",
     *    tags={"Dummyhex"},
     *    security={{
     *       "jwt-token":{}
     *    }},
     *    @OA\Parameter(
     *       name="page",
     *       description="Page number",
     *       in="query",
     *       required=false,
     *       @OA\Schema(
     *          type="integer"
     *       )
     *    ),
     *    @OA\Parameter(
     *       name="size",
     *       description="Page size",
     *       in="query",
     *       required=false,
     *       @OA\Schema(
     *          type="integer"
     *       )
     *    ),
     *    @OA\Parameter(
     *       name="orderBy",
     *       description="Order by",
     *       in="query",
     *       required=false,
     *       @OA\Schema(
     *          type="string"
     *       )
     *    ),
     *    @OA\Parameter(
     *       name="filter",
     *       description="Filter",
     *       in="query",
     *       required=false,
     *       @OA\Schema(
     *          type="string"
     *       )
     *    ),
     *    @OA\Response(
     *      response=200,
     *      description="The list of DummyHex",
     *      @OA\JsonContent(
     *         type="array",
     *         @OA\Items(ref="#/components/schemas/DummyHex")
     *      )
     *    ),
     *    @OA\Response(
     *      response=401,
     *      description="Not Authorized",
     *      @OA\JsonContent(ref="#/components/schemas/error")
     *    )
     * )
     * 
     * @param mixed $response
     * @param mixed $request
     * @return void
     */
    public function listDummyHex($response, $request)
    {
        $data = $this->requireAuthenticated();

        $repo = Psr11::container()->get(DummyHexRepository::class);

        $page = $request->get('page');
        $size = $request->get('size');
        // $orderBy = $request->get('orderBy');
        // $filter = $request->get('filter');

        $result = $repo->list($page, $size);
        $response->write(
            $result
        );
    }


    /**
     * Create a new DummyHex 
     * @OA\Post(
     *     path="/dummyhex",
     *     tags={"Dummyhex"},
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

     *             @OA\Property(property="field", type="string", format="string", nullable=true)
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

     *             @OA\Property(property="id", type="string", format="string")
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
        $data = $this->requireRole("admin");

        $payload = $this->validateRequest($request);
        
        $model = new DummyHex();
        BinderObject::bind($payload, $model);

        $dummyHexRepo = Psr11::container()->get(DummyHexRepository::class);
        $dummyHexRepo->save($model);

        $response->write([ "id" => $model->getId()]);
    }


    /**
     * Update an existing DummyHex 
     * @OA\Put(
     *     path="/dummyhex",
     *     tags={"Dummyhex"},
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
        $data = $this->requireRole("admin");

        $payload = $this->validateRequest($request);

        $dummyHexRepo = Psr11::container()->get(DummyHexRepository::class);
        $model = $dummyHexRepo->get($payload['id']);
        if (empty($model)) {
            throw new Error404Exception('Id not found');
        }
        BinderObject::bind($payload, $model);

        $dummyHexRepo->save($model);
    }

}
