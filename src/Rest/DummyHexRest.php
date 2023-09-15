<?php

namespace RestTemplate\Rest;

use ByJG\Config\Exception\ConfigException;
use ByJG\Config\Exception\ConfigNotFoundException;
use ByJG\Config\Exception\DependencyInjectionException;
use ByJG\Config\Exception\InvalidDateException;
use ByJG\Config\Exception\KeyNotFoundException;
use ByJG\MicroOrm\Exception\InvalidArgumentException;
use ByJG\MicroOrm\Exception\OrmBeforeInvalidException;
use ByJG\MicroOrm\Exception\OrmInvalidFieldsException;
use ByJG\RestServer\Exception\Error400Exception;
use ByJG\RestServer\Exception\Error401Exception;
use ByJG\RestServer\Exception\Error403Exception;
use ByJG\RestServer\Exception\Error404Exception;
use ByJG\RestServer\HttpRequest;
use ByJG\RestServer\HttpResponse;
use ByJG\Serializer\BinderObject;
use OpenApi\Attributes as OA;
use ReflectionException;
use RestTemplate\Model\DummyHex;
use RestTemplate\Model\User;
use RestTemplate\Psr11;
use RestTemplate\Repository\DummyHexRepository;

class DummyHexRest extends ServiceAbstractBase
{
    /**
     * Get the DummyHex by id
     *
     * @param HttpResponse $response
     * @param HttpRequest $request
     * @throws ConfigException
     * @throws ConfigNotFoundException
     * @throws DependencyInjectionException
     * @throws Error401Exception
     * @throws Error404Exception
     * @throws InvalidArgumentException
     * @throws InvalidDateException
     * @throws KeyNotFoundException
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws ReflectionException
     */
    #[OA\Get(
        path: "/dummyhex/{id}",
        security: [
            ["jwt-token" => []]
        ],
        tags: ["Dummyhex"],
    )]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        schema: new OA\Schema(
            type: "string",
            format: "string"
        )
    )]
    #[OA\Response(
        response: 200,
        description: "The object DummyHex",
        content: new OA\JsonContent(ref: "#/components/schemas/DummyHex")
    )]
    public function getDummyHex(HttpResponse $response, HttpRequest $request): void
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
     *
     * @param mixed $response
     * @param mixed $request
     * @return void
     * @throws ConfigException
     * @throws ConfigNotFoundException
     * @throws DependencyInjectionException
     * @throws Error401Exception
     * @throws InvalidArgumentException
     * @throws InvalidDateException
     * @throws KeyNotFoundException
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws ReflectionException
     */
    #[OA\Get(
        path: "/dummyhex",
        security: [
            ["jwt-token" => []]
        ],
        tags: ["Dummyhex"]
    )]
    #[OA\Parameter(
        name: "page",
        description: "Page number",
        in: "query",
        required: false,
        schema: new OA\Schema(
            type: "integer",
        )
    )]
    #[OA\Parameter(
        name: "size",
        description: "Page size",
        in: "query",
        required: false,
        schema: new OA\Schema(
            type: "integer",
        )
    )]
    #[OA\Parameter(
        name: "orderBy",
        description: "Order by",
        in: "query",
        required: false,
        schema: new OA\Schema(
            type: "string",
        )
    )]
    #[OA\Parameter(
        name: "filter",
        description: "Filter",
        in: "query",
        required: false,
        schema: new OA\Schema(
            type: "string",
        )
    )]
    #[OA\Response(
        response: 200,
        description: "The object DummyHex",
        content: new OA\JsonContent(type: "array", items: new OA\Items(ref: "#/components/schemas/DummyHex"))
    )]
    #[OA\Response(
        response: 401,
        description: "Not Authorized",
        content: new OA\JsonContent(ref: "#/components/schemas/error")
    )]
    public function listDummyHex(HttpResponse $response, HttpRequest $request): void
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
     *
     * @param HttpResponse $response
     * @param HttpRequest $request
     * @return void
     * @throws ConfigException
     * @throws ConfigNotFoundException
     * @throws DependencyInjectionException
     * @throws Error400Exception
     * @throws Error401Exception
     * @throws Error403Exception
     * @throws InvalidArgumentException
     * @throws InvalidDateException
     * @throws KeyNotFoundException
     * @throws OrmBeforeInvalidException
     * @throws OrmInvalidFieldsException
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws ReflectionException
     */
    #[OA\Post(
        path: "/dummyhex",
        security: [
            ["jwt-token" => []]
        ],
        tags: ["Dummyhex"]
    )]
    #[OA\RequestBody(
        description: "The object DummyHex to be created",
        required: true,
        content: new OA\JsonContent(
            required: [ "field" ],
            properties: [

                new OA\Property(property: "field", type: "string", format: "string")
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "The object rto be created",
        content: new OA\JsonContent(
            required: [ "id" ],
            properties: [

                new OA\Property(property: "id", type: "string", format: "string")
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: "Not Authorized",
        content: new OA\JsonContent(ref: "#/components/schemas/error")
    )]
    public function postDummyHex(HttpResponse $response, HttpRequest $request): void
    {
        $data = $this->requireRole(User::ROLE_ADMIN);

        $payload = $this->validateRequest($request);

        $model = new DummyHex();
        BinderObject::bind($payload, $model);

        $dummyHexRepo = Psr11::container()->get(DummyHexRepository::class);
        $dummyHexRepo->save($model);

        $response->write([ "id" => $model->getId()]);
    }


    /**
     * Update an existing DummyHex
     *
     * @param HttpResponse $response
     * @param HttpRequest $request
     * @return void
     * @throws Error401Exception
     * @throws Error404Exception
     * @throws ConfigException
     * @throws ConfigNotFoundException
     * @throws DependencyInjectionException
     * @throws InvalidDateException
     * @throws KeyNotFoundException
     * @throws InvalidArgumentException
     * @throws OrmBeforeInvalidException
     * @throws OrmInvalidFieldsException
     * @throws Error400Exception
     * @throws Error403Exception
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws ReflectionException
     */
    #[OA\Put(
        path: "/dummyhex",
        security: [
            ["jwt-token" => []]
        ],
        tags: ["Dummyhex"]
    )]
    #[OA\RequestBody(
        description: "The object DummyHex to be updated",
        required: true,
        content: new OA\JsonContent(ref: "#/components/schemas/DummyHex")
    )]
    #[OA\Response(
        response: 200,
        description: "Nothing to return"
    )]
    #[OA\Response(
        response: 401,
        description: "Not Authorized",
        content: new OA\JsonContent(ref: "#/components/schemas/error")
    )]
    public function putDummyHex(HttpResponse $response, HttpRequest $request): void
    {
        $data = $this->requireRole(User::ROLE_ADMIN);

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
