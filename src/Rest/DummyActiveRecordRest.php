<?php

namespace RestReferenceArchitecture\Rest;

use ByJG\RestServer\Attributes\RequireAuthenticated;
use ByJG\RestServer\Exception\Error404Exception;
use ByJG\RestServer\HttpRequest;
use ByJG\RestServer\HttpResponse;
use OpenApi\Attributes as OA;
use RestReferenceArchitecture\Attributes\ValidateRequest;
use RestReferenceArchitecture\Model\DummyActiveRecord;

class DummyActiveRecordRest
{
    /**
     * Get a DummyActiveRecord by ID
     */
    #[OA\Get(
        path: "/dummyactiverecord/{id}",
        description: "Get a DummyActiveRecord by ID",
        security: [["jwt-token" => []]],
        tags: ["DummyActiveRecord"]
    )]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        schema: new OA\Schema(type: "integer", format: "int32")
    )]
    #[OA\Response(
        response: 200,
        description: "The DummyActiveRecord object",
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
     * List all DummyActiveRecord records
     */
    #[OA\Get(
        path: "/dummyactiverecord",
        description: "List all DummyActiveRecord records",
        security: [["jwt-token" => []]],
        tags: ["DummyActiveRecord"]
    )]
    #[OA\Response(
        response: 200,
        description: "List of DummyActiveRecord objects",
        content: new OA\JsonContent(
            type: "array",
            items: new OA\Items(ref: "#/components/schemas/DummyActiveRecord")
        )
    )]
    #[RequireAuthenticated]
    public function getAllDummyActiveRecord(HttpResponse $response, HttpRequest $request): void
    {
        // Get all records with default pagination (page 0, limit 50)
        $models = DummyActiveRecord::all();
        $response->write($models);
    }

    /**
     * Create a new DummyActiveRecord
     */
    #[OA\Post(
        path: "/dummyactiverecord",
        description: "Create a new DummyActiveRecord",
        security: [["jwt-token" => []]],
        tags: ["DummyActiveRecord"]
    )]
    #[OA\RequestBody(
        description: "The DummyActiveRecord object to be created",
        required: true,
        content: new OA\JsonContent(
            required: ["name"],
            properties: [
                new OA\Property(property: "name", type: "string", format: "string"),
                new OA\Property(property: "value", type: "string", format: "string", nullable: true),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "The ID of the created object",
        content: new OA\JsonContent(
            required: ["id"],
            properties: [
                new OA\Property(property: "id", type: "integer", format: "int32")
            ]
        )
    )]
    #[RequireAuthenticated]
    #[ValidateRequest]
    public function postDummyActiveRecord(HttpResponse $response, HttpRequest $request): void
    {
        $payload = ValidateRequest::getPayload();

        // Create new ActiveRecord instance with payload
        $model = DummyActiveRecord::new($payload);
        $model->save();

        $response->write(["id" => $model->getId()]);
    }

    /**
     * Update an existing DummyActiveRecord
     */
    #[OA\Put(
        path: "/dummyactiverecord/{id}",
        description: "Update an existing DummyActiveRecord",
        security: [["jwt-token" => []]],
        tags: ["DummyActiveRecord"]
    )]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        schema: new OA\Schema(type: "integer", format: "int32")
    )]
    #[OA\RequestBody(
        description: "The DummyActiveRecord object to be updated",
        required: true,
        content: new OA\JsonContent(
            required: ["name"],
            properties: [
                new OA\Property(property: "name", type: "string", format: "string"),
                new OA\Property(property: "value", type: "string", format: "string", nullable: true),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "The updated DummyActiveRecord object",
        content: new OA\JsonContent(ref: "#/components/schemas/DummyActiveRecord")
    )]
    #[RequireAuthenticated]
    #[ValidateRequest]
    public function putDummyActiveRecord(HttpResponse $response, HttpRequest $request): void
    {
        $payload = ValidateRequest::getPayload();

        $model = DummyActiveRecord::get($request->param('id'));

        if (is_null($model)) {
            throw new Error404Exception("DummyActiveRecord not found");
        }

        // Update model with payload using fill() method
        $model->fill($payload);
        $model->save();

        $response->write($model);
    }

    /**
     * Delete a DummyActiveRecord
     */
    #[OA\Delete(
        path: "/dummyactiverecord/{id}",
        description: "Delete a DummyActiveRecord",
        security: [["jwt-token" => []]],
        tags: ["DummyActiveRecord"]
    )]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        schema: new OA\Schema(type: "integer", format: "int32")
    )]
    #[OA\Response(
        response: 200,
        description: "Confirmation of deletion",
        content: new OA\JsonContent(
            required: ["result"],
            properties: [
                new OA\Property(property: "result", type: "string")
            ]
        )
    )]
    #[RequireAuthenticated]
    public function deleteDummyActiveRecord(HttpResponse $response, HttpRequest $request): void
    {
        $model = DummyActiveRecord::get($request->param('id'));

        if (is_null($model)) {
            throw new Error404Exception("DummyActiveRecord not found");
        }

        $model->delete();

        $response->write(["result" => "deleted"]);
    }
}
