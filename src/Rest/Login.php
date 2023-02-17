<?php

namespace RestTemplate\Rest;

use ByJG\Authenticate\Model\UserModel;
use ByJG\Authenticate\UsersDBDataset;
use ByJG\Config\Exception\ConfigNotFoundException;
use ByJG\Config\Exception\EnvironmentException;
use ByJG\Config\Exception\KeyNotFoundException;
use ByJG\MicroOrm\Literal;
use ByJG\RestServer\Exception\Error401Exception;
use ByJG\RestServer\HttpRequest;
use ByJG\RestServer\HttpResponse;
use ByJG\RestServer\ResponseBag;
use RestTemplate\Psr11;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;
use RestTemplate\Model\User;
use OpenApi\Annotations as OA;

class Login extends ServiceAbstractBase
{
    /**
     * Do login
     * @OA\Post(
     *     path="/login",
     *     tags={"login"},
     *     @OA\RequestBody(
     *         description="The login data",
     *         required=true,
     *         @OA\MediaType(
     *            mediaType="application/json",
     *            @OA\Schema(
     *              required={"username","password"},
     *              @OA\Property(property="username", type="string", description="The username"),
     *              @OA\Property(property="password", type="string", description="The password"),
     *           )
     *        )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="The object",
     *         @OA\MediaType(
     *           mediaType="application/json",
     *           @OA\Schema(
     *             required={"token"},
     *             @OA\Property(property="token", type="string"),
     *             @OA\Property(property="data",
     *             @OA\Property(property="role", type="string"),
     *             @OA\Property(property="userid", type="string"),
     *             @OA\Property(property="name",type="string"))
     *          )
     *       )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autorizado",
     *         @OA\JsonContent(ref="#/components/schemas/error")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro Geral",
     *         @OA\JsonContent(ref="#/components/schemas/error")
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
     * @throws ReflectionException
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     */
    public function post($response, $request)
    {
        $this->validateRequest($request);

        $json = json_decode($request->payload());

        $users = Psr11::container()->get(UsersDBDataset::class);
        $user = $users->isValidUser($json->username, $json->password);
        $metadata = $this->createUserMetadata($user);

        $response->getResponseBag()->setSerializationRule(ResponseBag::SINGLE_OBJECT);
        $response->write(['token' => $this->createToken($metadata)]);
        $response->write(['data' => $metadata]);
    }

    /**
     * Refresh Token
     * @OA\Post(
     *     path="/refreshtoken",
     *     tags={"login"},
     *     security={{
     *         "jwt-token":{}
     *     }},
     *     @OA\Response(
     *         response=200,
     *         description="The object",
     *         @OA\MediaType(
     *           mediaType="application/json",
     *           @OA\Schema(
     *              required={"token"},
     *              @OA\Property(property="token", type="string"),
     *              @OA\Property(property="data",
     *              @OA\Property(property="role", type="string"),
     *              @OA\Property(property="userid", type="string"),
     *              @OA\Property(property="name", type="string"))
     *          )
     *       )
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
     * @throws ConfigNotFoundException
     * @throws EnvironmentException
     * @throws Error401Exception
     * @throws InvalidArgumentException
     * @throws KeyNotFoundException
     * @throws ReflectionException
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     */
    public function refreshToken($response, $request)
    {
        $result = $this->requireAuthenticated(null, true);

        $diff = ($result["exp"] - time()) / 60;

        if ($diff > 5) {
            throw new Error401Exception("You only can refresh the token 5 minutes before expire");
        }

        $users = Psr11::container()->get(UsersDBDataset::class);
        $user = $users->getById(new Literal("X'" . str_replace("-", "", $result["data"]["userid"]) . "'"));

        $metadata = $this->createUserMetadata($user);
        
        $response->getResponseBag()->setSerializationRule(ResponseBag::SINGLE_OBJECT);
        $response->write(['token' => $this->createToken($metadata)]);
        $response->write(['data' => $metadata]);
       
    }

    /**
     * @param UserModel $user
     * @return mixed
     * @throws Error401Exception
     */
    private function createUserMetadata($user)
    {
        if (is_null($user)) {
            throw new Error401Exception('Username or password is invalid');
        }

        return [
            'role' => ($user->getAdmin() === 'yes' ? 'admin' : 'user'),
            'userid' => $user->getUserid(),
            'name' => $user->getName()
        ];
    }
}
