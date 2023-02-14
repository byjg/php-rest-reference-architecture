<?php

namespace RestTemplate\Rest;

use ByJG\RestServer\Exception\Error401Exception;
use ByJG\RestServer\HttpRequest;
use ByJG\RestServer\HttpResponse;
use Psr\SimpleCache\InvalidArgumentException;
use Swagger\Annotations as SWG;

class SampleProtected extends ServiceAbstractBase
{
    /**
     * Sample Ping Only Authenticated
     * @SWG\Get(
     *     path="/sampleprotected/ping",
     *     tags={"zz_sampleprotected"},
     *     security={{
     *         "jwt-token":{}
     *     }},
     *     @SWG\Response(
     *         response=200,
     *         description="The object",
     *         @SWG\Schema(
     *            required={"result"},
     *            @SWG\Property(property="result", type="string")
     *         )
     *     ),
     *     @SWG\Response(
     *         response=401,
     *         description="Não autorizado",
     *         @SWG\Schema(ref="#/definitions/error")
     *     )
     * )
     *
     * @param HttpResponse $response
     * @param HttpRequest $request
     * @throws Error401Exception
     * @throws InvalidArgumentException
     */
    public function getPing($response, $request)
    {
        $this->requireAuthenticated();

        $response->write([
            'result' => 'pong'
        ]);
    }

    /**
     * Sample Ping Only Admin
     * @SWG\Get(
     *     path="/sampleprotected/pingadm",
     *     tags={"zz_sampleprotected"},
     *     security={{
     *         "jwt-token":{}
     *     }},
     *     @SWG\Response(
     *         response=200,
     *         description="The object",
     *         @SWG\Schema(
     *            required={"result"},
     *            @SWG\Property(property="result", type="string")
     *         )
     *     ),
     *     @SWG\Response(
     *         response=401,
     *         description="Não autorizado",
     *         @SWG\Schema(ref="#/definitions/error")
     *     )
     * )
     *
     * @param HttpResponse $response
     * @param HttpRequest $request
     * @throws Error401Exception
     * @throws InvalidArgumentException
     */
    public function getPingAdm($response, $request)
    {
        $this->requireRole('admin');

        $response->write([
            'result' => 'pongadm'
        ]);
    }
}
