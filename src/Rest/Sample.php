<?php

namespace RestTemplate\Rest;

use ByJG\RestServer\HttpRequest;
use ByJG\RestServer\HttpResponse;

class Sample extends ServiceAbstractBase
{
    /**
     * Simple ping
     *
     * @OA\Get(
     *     path="/sample/ping",
     *     tags={"zz_sample"},
     *     @OA\Response(
     *         response=200,
     *         description="The object",
     *         @OA\MediaType(
     *           mediaType="application/json",
     *           @OA\Schema(
     *             required={"result"},
     *             @OA\Property(property="result", type="string")
     *           )
     *         )
     *     )
     * )
     * @param HttpResponse $response
     * @param HttpRequest $request
     */
    public function getPing(HttpResponse $response, HttpRequest $request)
    {
        $this->getResponse()->write([
            'result' => 'pong'
        ]);
    }
}
