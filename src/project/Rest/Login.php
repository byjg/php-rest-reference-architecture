<?php

namespace RestTemplate\Rest;

use ByJG\RestServer\Exception\Error401Exception;

class Login extends ServiceAbstractBase
{
    /**
     * Do login
     *
     * @SWG\Post(
     *     path="/login",
     *     operationId="post",
     *     tags={"login"},
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         description="The login data",
     *         required=true,
     *         @SWG\Schema(
     *              required={"username","password"},
     *              @SWG\Property(property="username", type="string", description="The username"),
     *              @SWG\Property(property="password", type="string", description="The password"),
     *         )
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="The object",
     *         @SWG\Schema(
     *            required={"token","properties"},
     *            @SWG\Property(property="token", type="string"),
     *            @SWG\Property(property="properties", type="array")
     *         )
     *     ),
     *     @SWG\Response(
     *         response=401,
     *         description="Não autorizado",
     *         @SWG\Schema(ref="#/definitions/error")
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="Erro Geral",
     *         @SWG\Schema(ref="#/definitions/error")
     *     )
     * )
     */
    public function post()
    {
        $json = json_decode($this->getRequest()->payload());

        if ($json->username != 'admin' || $json->password != 'pwd') {
            throw new Error401Exception('Username or password is invalid');
        }

        $metadata = [
            'role' => 'admin',
            'userid' => '1234567890ABCDEF0123456789',
            'name' => 'João Gilberto'
        ];

        $token = $this->createToken($metadata);

        $this->getResponse()->write(['token' => $token]);
    }
}
