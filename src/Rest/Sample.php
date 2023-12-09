<?php

namespace RestTemplate\Rest;

use ByJG\RestServer\HttpRequest;
use ByJG\RestServer\HttpResponse;
use OpenApi\Attributes as OA;

class Sample extends ServiceAbstractBase
{
    /**
     * Simple ping
     *
     * @param HttpResponse $response
     * @param HttpRequest $request
     */
    #[OA\Get(
        path: "/sample/ping",
        tags: ["zz_sample"],
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
    public function getPing(HttpResponse $response, HttpRequest $request)
    {
        $response->write([
            'result' => 'pong'
        ]);
    }
}
