<?php

namespace RestTemplate\Rest;

class SampleProtected extends ServiceAbstractBase
{
    /**
     * Gets an blog by Id.
     *
     * @SWG\Get(
     *     path="/sampleprotected/ping",
     *     operationId="get",
     *     tags={"sampleprotected"},
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
     */
    public function getPing()
    {
        $this->requireAuthenticated();

        $this->getResponse()->write([
            'result' => 'pong'
        ]);
    }

    /**
     * Gets an blog by Id.
     *
     * @SWG\Get(
     *     path="/sampleprotected/pingadm",
     *     operationId="get",
     *     tags={"sampleprotected"},
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
     */
    public function getPingAdm()
    {
        $this->requireRole('admin');

        $this->getResponse()->write([
            'result' => 'pongadm'
        ]);
    }
}
