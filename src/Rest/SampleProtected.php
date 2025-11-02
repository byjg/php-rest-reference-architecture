<?php

namespace RestReferenceArchitecture\Rest;

use ByJG\RestServer\Attributes\RequireAuthenticated;
use ByJG\RestServer\Exception\Error401Exception;
use ByJG\RestServer\Exception\Error403Exception;
use ByJG\RestServer\HttpRequest;
use ByJG\RestServer\HttpResponse;
use OpenApi\Attributes as OA;
use Psr\SimpleCache\InvalidArgumentException;
use RestReferenceArchitecture\Attributes\RequireRole;

class SampleProtected
{
    /**
     * Sample Ping Only Authenticated
     *
     * @param HttpResponse $response
     * @param HttpRequest $request
     * @throws Error401Exception
     * @throws InvalidArgumentException
     */
    #[OA\Get(
        path: "/sampleprotected/ping",
        security: [
            ["jwt-token" => []]
        ],
        tags: ["zz_sampleprotected"],
    )]
    #[OA\Response(
        response: 200,
        description: "The object",
        content: new OA\JsonContent(
            required: [ "result" ],
            properties: [
                new OA\Property(property: "result", type: "string", format: "string")
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: "Não autorizado",
        content: new OA\JsonContent(ref: "#/components/schemas/error")
    )]
    #[RequireAuthenticated]
    public function getPing(HttpResponse $response, HttpRequest $request)
    {
        // No longer need: JwtContext::requireAuthenticated($request);
        // The attribute handles authentication automatically

        $response->write([
            'result' => 'pong'
        ]);
    }

    /**
     * Sample Ping Only Admin
     *
     * @param HttpResponse $response
     * @param HttpRequest $request
     * @throws Error401Exception
     * @throws InvalidArgumentException
     * @throws Error403Exception
     */
    #[OA\Get(
        path: "/sampleprotected/pingadm",
        security: [
            ["jwt-token" => []]
        ],
        tags: ["zz_sampleprotected"],
    )]
    #[OA\Response(
        response: 200,
        description: "The object",
        content: new OA\JsonContent(
            required: [ "result" ],
            properties: [
                new OA\Property(property: "result", type: "string", format: "string")
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: "Não autorizado",
        content: new OA\JsonContent(ref: "#/components/schemas/error")
    )]
    #[RequireRole('admin')]
    public function getPingAdm(HttpResponse $response, HttpRequest $request)
    {
        // No longer need: JwtContext::requireRole($request, 'admin');
        // The attribute handles role checking automatically

        $response->write([
            'result' => 'pongadm'
        ]);
    }
}
