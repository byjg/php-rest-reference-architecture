<?php

namespace RestTemplate\Rest;

use ByJG\RestServer\Exception\Error401Exception;
use ByJG\RestServer\Exception\Error404Exception;
use ByJG\RestServer\HttpRequest;
use ByJG\RestServer\HttpResponse;
use ByJG\Serializer\BinderObject;
use RestTemplate\Model\Dummy;
use RestTemplate\Psr11;
use RestTemplate\Repository\DummyRepository;

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
     *             type="integer",
     *             format="int32"
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
     * List Dummy
     * @OA\Get(
     *    path="/dummy",
     *    tags={"Dummy"},
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
     *      description="The list of Dummy",
     *      @OA\JsonContent(
     *         type="array",
     *         @OA\Items(ref="#/components/schemas/Dummy")
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
    public function listDummy($response, $request)
    {
        $data = $this->requireAuthenticated();

        $repo = Psr11::container()->get(DummyRepository::class);

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

     *             @OA\Property(property="id", type="integer", format="int32")
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
        $data = $this->requireRole("admin");

        $payload = $this->validateRequest($request);

        $model = new Dummy();
        BinderObject::bind($payload, $model);

        $dummyRepo = Psr11::container()->get(DummyRepository::class);
        $dummyRepo->save($model);

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
        $data = $this->requireRole("admin");

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
