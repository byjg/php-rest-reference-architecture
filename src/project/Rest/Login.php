<?php

namespace RestTemplate\Rest;

use Builder\Psr11;
use ByJG\Config\Exception\ConfigNotFoundException;
use ByJG\Config\Exception\EnvironmentException;
use ByJG\Config\Exception\KeyNotFoundException;
use ByJG\MicroOrm\Literal;
use ByJG\RestServer\Exception\Error401Exception;
use ByJG\RestServer\HttpRequest;
use ByJG\RestServer\HttpResponse;
use ByJG\RestServer\ResponseBag;
use Psr\SimpleCache\InvalidArgumentException;
use RestTemplate\Model\User;

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
    public function post($response, $request)
    {
        $json = json_decode($request->payload());

        $users = Psr11::container()->get('LOGIN');
        $user = $users->isValidUser($json->username, $json->password);
        $metadata = $this->createcreateUserMetadata($user);

        $response->getResponseBag()->serializationRule(ResponseBag::SINGLE_OBJECT);
        $response->write(['token' => $this->createToken($metadata)]);
        $response->write(['data' => $metadata]);
    }

    /**
     * Refresh Token
     * @SWG\Post(
     *     path="/refreshtoken",
     *     tags={"login"},
     *     security={{
     *         "jwt-token":{}
     *     }},
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
     *     )
     * )
     *
     * @param HttpResponse $response
     * @param HttpRequest $request
     * @throws ConfigNotFoundException
     * @throws EnvironmentException
     * @throws Error401Exception
     * @throws InvalidArgumentException
     * @throws KeyNotFoundException
     */
    public function refreshToken($response, $request)
    {
        $result = $this->requireAuthenticated(null, true);

        $diff = ($result["exp"] - time()) / 60;

        if ($diff > 5) {
            throw new Error401Exception("You only can refresh the token 5 minutes before expire");
        }

        $users = Psr11::container()->get('LOGIN');
        $user = $users->getById(new Literal("X'" . str_replace("-", "", $result["data"]["userid"]) . "'"));

        $response->write([ "token" => $this->createToken($this->createcreateUserMetadata($user))]);
    }

    /**
     * @param User $user
     * @return mixed
     * @throws Error401Exception
     */
    private function createcreateUserMetadata($user)
    {
        if (is_null($user)) {
            throw new Error401Exception('Username or password is invalid');
        }

        $metadata = [
            'role' => ($user->getAdmin() === 'yes' ? 'admin' : 'user'),
            'userid' => $user->getUserid(),
            'name' => $user->getName()
        ];

        return $metadata;
    }
}
