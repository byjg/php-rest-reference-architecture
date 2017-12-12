<?php

namespace RestTemplate\Rest;

use Builder\Psr11;
use ByJG\RestServer\Exception\Error401Exception;
use ByJG\RestServer\ResponseBag;

class Login extends ServiceAbstractBase
{
    /**
     * Do login
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
     *            required={"token"},
     *            @SWG\Property(property="token", type="string"),
     *            @SWG\Property(property="data",
     *            @SWG\Property(property="role", type="string"),
     *            @SWG\Property(property="userid", type="string"),
     *            @SWG\Property(property="name", type="string"))
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
     *
     * @throws \ByJG\RestServer\Exception\Error401Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function post()
    {
        $json = json_decode($this->getRequest()->payload());

        $users = Psr11::container()->getClosure('LOGIN');
        $user = $users->isValidUser($json->username, $json->password);
        if (is_null($user)) {
            throw new Error401Exception('Username or password is invalid');
        }


        $metadata = [
            'role' => ($user->getAdmin() === 'yes' ? 'admin' : 'user'),
            'userid' => $user->getUserid(),
            'name' => $user->getName()
        ];
        $token = $this->createToken($metadata);

        $this->getResponse()->getResponseBag()->serializationRule(ResponseBag::SINGLE_OBJECT);
        $this->getResponse()->write(['token' => $token]);
        $this->getResponse()->write(['data' => $metadata]);
    }
}
