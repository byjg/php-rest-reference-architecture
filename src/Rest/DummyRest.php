<?php

namespace RestReferenceArchitecture\Rest;

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
use ByJG\Serializer\ObjectCopy;
use OpenApi\Attributes as OA;
use ReflectionException;
use RestReferenceArchitecture\Model\Dummy;
use RestReferenceArchitecture\Model\User;
use RestReferenceArchitecture\Psr11;
use RestReferenceArchitecture\Repository\DummyRepository;
use RestReferenceArchitecture\Util\JwtContext;
use RestReferenceArchitecture\Util\OpenApiContext;

class DummyRest
{
    /**
     * Get the Dummy by id
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
        path: "/dummy/{id}",
        security: [
            ["jwt-token" => []]
        ],
        tags: ["Dummy"],
    )]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        schema: new OA\Schema(
            type: "integer",
            format: "int32"
        )
    )]
    #[OA\Response(
        response: 200,
        description: "The object Dummy",
        content: new OA\JsonContent(ref: "#/components/schemas/Dummy")
    )]
    public function getDummy(HttpResponse $response, HttpRequest $request): void
    {
        JwtContext::requireAuthenticated($request);

        $dummyRepo = Psr11::get(DummyRepository::class);
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
        path: "/dummy",
        security: [
            ["jwt-token" => []]
        ],
        tags: ["Dummy"]
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
        description: "The object Dummy",
        content: new OA\JsonContent(type: "array", items: new OA\Items(ref: "#/components/schemas/Dummy"))
    )]
    #[OA\Response(
        response: 401,
        description: "Not Authorized",
        content: new OA\JsonContent(ref: "#/components/schemas/error")
    )]
    public function listDummy(HttpResponse $response, HttpRequest $request): void
    {
        JwtContext::requireAuthenticated($request);

        $repo = Psr11::get(DummyRepository::class);

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
        path: "/dummy",
        security: [
            ["jwt-token" => []]
        ],
        tags: ["Dummy"]
    )]
    #[OA\RequestBody(
        description: "The object Dummy to be created",
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

                new OA\Property(property: "id", type: "integer", format: "int32")
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: "Not Authorized",
        content: new OA\JsonContent(ref: "#/components/schemas/error")
    )]
    public function postDummy(HttpResponse $response, HttpRequest $request): void
    {
        JwtContext::requireRole($request, User::ROLE_ADMIN);

        $payload = OpenApiContext::validateRequest($request);

        $model = new Dummy();
        ObjectCopy::copy($payload, $model);

        $dummyRepo = Psr11::get(DummyRepository::class);
        $dummyRepo->save($model);

        $response->write([ "id" => $model->getId()]);
    }


    /**
     * Update an existing Dummy
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
        path: "/dummy",
        security: [
            ["jwt-token" => []]
        ],
        tags: ["Dummy"]
    )]
    #[OA\RequestBody(
        description: "The object Dummy to be updated",
        required: true,
        content: new OA\JsonContent(ref: "#/components/schemas/Dummy")
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
    public function putDummy(HttpResponse $response, HttpRequest $request): void
    {
        JwtContext::requireRole($request, User::ROLE_ADMIN);

        $payload = OpenApiContext::validateRequest($request);

        $dummyRepo = Psr11::get(DummyRepository::class);
        $model = $dummyRepo->get($payload['id']);
        if (empty($model)) {
            throw new Error404Exception('Id not found');
        }
        ObjectCopy::copy($payload, $model);

        $dummyRepo->save($model);
    }

}
