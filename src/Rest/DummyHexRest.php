<?php

namespace RestReferenceArchitecture\Rest;

use ByJG\Config\Config;
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
use OpenApi\Attributes as OA;
use ReflectionException;
use RestReferenceArchitecture\Attributes\RequireAuthenticated;
use RestReferenceArchitecture\Attributes\RequireRole;
use RestReferenceArchitecture\Attributes\ValidateRequest;
use RestReferenceArchitecture\Model\User;
use RestReferenceArchitecture\Service\DummyHexService;

class DummyHexRest
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
        path: "/dummy/hex/{id}",
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
            type: "string",
            format: "string"
        )
    )]
    #[OA\Response(
        response: 200,
        description: "The object DummyHex",
        content: new OA\JsonContent(ref: "#/components/schemas/DummyHex")
    )]
    #[RequireAuthenticated]
    public function getDummyHex(HttpResponse $response, HttpRequest $request): void
    {
        $dummyHexService = Config::get(DummyHexService::class);
        $result = $dummyHexService->getOrFail($request->param('id'));
        $response->write($result);
    }

    /**
     * List DummyHex
     *
     * @param HttpResponse $response
     * @param HttpRequest $request
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
        path: "/dummy/hex",
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
        description: "The object DummyHex",
        content: new OA\JsonContent(type: "array", items: new OA\Items(ref: "#/components/schemas/DummyHex"))
    )]
    #[OA\Response(
        response: 401,
        description: "Not Authorized",
        content: new OA\JsonContent(ref: "#/components/schemas/error")
    )]
    #[RequireAuthenticated]
    public function listDummyHex(HttpResponse $response, HttpRequest $request): void
    {
        $dummyHexService = Config::get(DummyHexService::class);
        $result = $dummyHexService->list($request->get('page'), $request->get('size'));
        $response->write($result);
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
        path: "/dummy/hex",
        security: [
            ["jwt-token" => []]
        ],
        tags: ["Dummy"]
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
    #[RequireRole(User::ROLE_ADMIN)]
    #[ValidateRequest]
    public function postDummyHex(HttpResponse $response, HttpRequest $request): void
    {
        $dummyHexService = Config::get(DummyHexService::class);
        $model = $dummyHexService->create(ValidateRequest::getPayload());
        $response->write(["id" => $model->getId()]);
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
        path: "/dummy/hex",
        security: [
            ["jwt-token" => []]
        ],
        tags: ["Dummy"]
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
    #[RequireRole(User::ROLE_ADMIN)]
    #[ValidateRequest]
    public function putDummyHex(HttpResponse $response, HttpRequest $request): void
    {
        $dummyHexService = Config::get(DummyHexService::class);
        $dummyHexService->update(ValidateRequest::getPayload());
    }

}
