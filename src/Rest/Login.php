<?php

namespace RestTemplate\Rest;

use Builder\Psr11;
use ByJG\Config\Exception\ConfigNotFoundException;
use ByJG\Config\Exception\EnvironmentException;
use ByJG\Config\Exception\KeyNotFoundException;
use ByJG\RestServer\Exception\Error401Exception;
use ByJG\RestServer\HttpRequest;
use ByJG\RestServer\HttpResponse;
use ByJG\RestServer\ResponseBag;
use Psr\SimpleCache\InvalidArgumentException;

class Login extends ServiceAbstractBase
{
    /**
     * Do login
     * @SWG\Post(
     *     path="/login",
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
     * @param HttpResponse $response
     * @param HttpRequest $request
     * @throws ConfigNotFoundException
     * @throws EnvironmentException
     * @throws KeyNotFoundException
     * @throws Error401Exception
     * @throws InvalidArgumentException
     */
    public function post()
    {
        $json = json_decode($this->getRequest()->payload());

        $users = Psr11::container()->get('LOGIN');
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
