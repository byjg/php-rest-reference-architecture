<?php

namespace RestTemplate\Rest;

use ByJG\RestServer\Exception\Error401Exception;
use ByJG\RestServer\HttpRequest;
use ByJG\RestServer\HttpResponse;
use Psr\SimpleCache\InvalidArgumentException;

class SampleProtected extends ServiceAbstractBase
{
    /**
     * Sample Ping Only Authenticated
     * @OA\Get(
     *     path="/sampleprotected/ping",
     *     tags={"zz_sampleprotected"},
     *     security={{
     *         "jwt-token":{}
     *     }},
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
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autorizado",
     *         @OA\JsonContent(ref="#/components/schemas/error")
     *     )
     * )
     *
     * @param HttpResponse $response
     * @param HttpRequest $request
     * @throws Error401Exception
     * @throws InvalidArgumentException
     */
    public function getPing()
    {
        $this->requireAuthenticated();

        $this->getResponse()->write([
            'result' => 'pong'
        ]);
    }

    /**
     * Sample Ping Only Admin
     * @OA\Get(
     *     path="/sampleprotected/pingadm",
     *     tags={"zz_sampleprotected"},
     *     security={{
     *         "jwt-token":{}
     *     }},
     *     @OA\Response(
     *         response=200,
     *         description="The object",
     *         @OA\MediaType(
     *           mediaType="application/json",
     *           @OA\Schema(
     *             required={"result"},
     *             @OA\Property(property="result", type="string")
     *           )
     *        )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autorizado",
     *         @OA\JsonContent(ref="#/components/schemas/error")
     *     )
     * )
     *
     * @param HttpResponse $response
     * @param HttpRequest $request
     * @throws Error401Exception
     * @throws InvalidArgumentException
     */
    public function getPingAdm()
    {
        $this->requireRole('admin');

        $this->getResponse()->write([
            'result' => 'pongadm'
        ]);
    }
}
