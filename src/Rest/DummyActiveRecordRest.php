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
use ByJG\RestServer\Attributes\RequireAuthenticated;
use ByJG\RestServer\Exception\Error400Exception;
use ByJG\RestServer\Exception\Error401Exception;
use ByJG\RestServer\Exception\Error403Exception;
use ByJG\RestServer\Exception\Error404Exception;
use ByJG\RestServer\HttpRequest;
use ByJG\RestServer\HttpResponse;
use OpenApi\Attributes as OA;
use ReflectionException;
use RestReferenceArchitecture\Attributes\RequireRole;
use RestReferenceArchitecture\Attributes\ValidateRequest;
use RestReferenceArchitecture\Model\DummyActiveRecord;
use RestReferenceArchitecture\Model\User;

class DummyActiveRecordRest
{
    /**
     * Get the DummyActiveRecord by id
     *
     * @param HttpResponse $response
     * @param HttpRequest $request
     * @throws Error404Exception
     */
    #[OA\Get(
        path: "/dummy/active/record/{id}",
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
        description: "The object DummyActiveRecord",
        content: new OA\JsonContent(ref: "#/components/schemas/DummyActiveRecord")
    )]
    #[RequireAuthenticated]
    public function getDummyActiveRecord(HttpResponse $response, HttpRequest $request): void
    {
        $model = DummyActiveRecord::get($request->param('id'));

        if (is_null($model)) {
            throw new Error404Exception("DummyActiveRecord not found");
        }

        $response->write($model);
    }

    /**
     * List DummyActiveRecord
     *
     * @param mixed $response
     * @param mixed $request
     * @return void
     */
    #[OA\Get(
        path: "/dummy/active/record",
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
        description: "The object DummyActiveRecord",
        content: new OA\JsonContent(type: "array", items: new OA\Items(ref: "#/components/schemas/DummyActiveRecord"))
    )]
    #[OA\Response(
        response: 401,
        description: "Not Authorized",
        content: new OA\JsonContent(ref: "#/components/schemas/error")
    )]
    #[RequireAuthenticated]
    public function listDummyActiveRecord(HttpResponse $response, HttpRequest $request): void
    {
        // Get all records with pagination (default is page 0, limit 50)
        $models = DummyActiveRecord::all($request->get('page') ?? 0, $request->get('size') ?? 50);
        $response->write($models);
    }


    /**
     * Create a new DummyActiveRecord
     *
     * @param HttpResponse $response
     * @param HttpRequest $request
     * @return void
     */
    #[OA\Post(
        path: "/dummy/active/record",
        security: [
            ["jwt-token" => []]
        ],
        tags: ["Dummy"]
    )]
    #[OA\RequestBody(
        description: "The object DummyActiveRecord to be created",
        required: true,
        content: new OA\JsonContent(
            required: [ "name" ],
            properties: [

                new OA\Property(property: "name", type: "string", format: "string"),
                new OA\Property(property: "value", type: "string", format: "string", nullable: true),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "The object to be created",
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
    #[RequireRole(User::ROLE_ADMIN)]
    #[ValidateRequest]
    public function postDummyActiveRecord(HttpResponse $response, HttpRequest $request): void
    {
        $payload = ValidateRequest::getPayload();

        // Create a new ActiveRecord instance with payload
        $model = DummyActiveRecord::new($payload);
        $model->save();

        $response->write(["id" => $model->getId()]);
    }


    /**
     * Update an existing DummyActiveRecord
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
        path: "/dummy/active/record",
        security: [
            ["jwt-token" => []]
        ],
        tags: ["Dummy"]
    )]
    #[OA\RequestBody(
        description: "The object DummyActiveRecord to be updated",
        required: true,
        content: new OA\JsonContent(ref: "#/components/schemas/DummyActiveRecord")
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
    public function putDummyActiveRecord(HttpResponse $response, HttpRequest $request): void
    {
        $payload = ValidateRequest::getPayload();

        $model = DummyActiveRecord::get($payload['id'] ?? null);

        if (is_null($model)) {
            throw new Error404Exception("DummyActiveRecord not found");
        }

        // Update model with payload using fill() method
        $model->fill($payload);
        $model->save();
    }

}
