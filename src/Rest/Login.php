<?php

namespace RestTemplate\Rest;

use ByJG\Authenticate\Exception\UserExistsException;
use ByJG\Authenticate\Model\UserModel;
use ByJG\Authenticate\UsersDBDataset;
use ByJG\Config\Exception\ConfigException;
use ByJG\Config\Exception\ConfigNotFoundException;
use ByJG\Config\Exception\DependencyInjectionException;
use ByJG\Config\Exception\InvalidDateException;
use ByJG\Config\Exception\KeyNotFoundException;
use ByJG\Mail\Wrapper\MailWrapperInterface;
use ByJG\MicroOrm\Exception\OrmBeforeInvalidException;
use ByJG\MicroOrm\Exception\OrmInvalidFieldsException;
use ByJG\RestServer\Exception\Error400Exception;
use ByJG\RestServer\Exception\Error401Exception;
use ByJG\RestServer\Exception\Error422Exception;
use ByJG\RestServer\HttpRequest;
use ByJG\RestServer\HttpResponse;
use ByJG\RestServer\ResponseBag;
use ByJG\Serializer\Exception\InvalidArgumentException;
use ReflectionException;
use RestTemplate\Psr11;
use RestTemplate\Repository\BaseRepository;
use RestTemplate\Util\HexUuidLiteral;

class Login extends ServiceAbstractBase
{
    /**
     * Do login
     * @OA\Post(
     *     path="/login",
     *     tags={"Login"},
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
     * @throws ConfigException
     * @throws ConfigNotFoundException
     * @throws DependencyInjectionException
     * @throws Error400Exception
     * @throws Error401Exception
     * @throws InvalidArgumentException
     * @throws InvalidDateException
     * @throws KeyNotFoundException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws ReflectionException
     */
    public function post(HttpResponse $response, HttpRequest $request)
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
     *     tags={"Login"},
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
     * @throws ConfigException
     * @throws ConfigNotFoundException
     * @throws DependencyInjectionException
     * @throws Error401Exception
     * @throws InvalidArgumentException
     * @throws InvalidDateException
     * @throws KeyNotFoundException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws ReflectionException
     */
    public function refreshToken(HttpResponse $response, HttpRequest $request)
    {
        $result = $this->requireAuthenticated(null, true);

        $diff = ($result["exp"] - time()) / 60;

        if ($diff > 5) {
            throw new Error401Exception("You only can refresh the token 5 minutes before expire");
        }

        $users = Psr11::container()->get(UsersDBDataset::class);
        $user = $users->getById(new HexUuidLiteral($result["data"]["userid"]));

        $metadata = $this->createUserMetadata($user);
        
        $response->getResponseBag()->setSerializationRule(ResponseBag::SINGLE_OBJECT);
        $response->write(['token' => $this->createToken($metadata)]);
        $response->write(['data' => $metadata]);
       
    }

    /**
     * Initialize the Password Request
     *
     * @OA\Post(
     *     path="/login/resetrequest",
     *     tags={"Login"},
     *     @OA\RequestBody(
     *         description="The email to have the password reset",
     *         required=true,
     *         @OA\MediaType(
     *            mediaType="application/json",
     *            @OA\Schema(
     *              required={"email"},
     *              @OA\Property(property="email", type="string", description="Email"),
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
     *          )
     *       )
     *     ),
     * )
     *
     * @param HttpResponse $response
     * @param HttpRequest $request
     * @throws ConfigException
     * @throws ConfigNotFoundException
     * @throws DependencyInjectionException
     * @throws Error400Exception
     * @throws InvalidArgumentException
     * @throws InvalidDateException
     * @throws KeyNotFoundException
     * @throws OrmBeforeInvalidException
     * @throws OrmInvalidFieldsException
     * @throws UserExistsException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws ReflectionException
     */
    public function postResetRequest(HttpResponse $response, HttpRequest $request)
    {
        $this->validateRequest($request);

        $json = json_decode($request->payload());

        $users = Psr11::container()->get(UsersDBDataset::class);
        $user = $users->getByEmail($json->email);

        $token = BaseRepository::getUuid();
        $code = rand(10000, 99999);

        if (!is_null($user)) {
            $user->set('resettoken', $token);
            $user->set('resettokenexpire', date('Y-m-d H:i:s', strtotime('+10 minutes')));
            $user->set("resetcode", $code);
            $user->set("resetallowed", null);
            $users->save($user);

            // Send email using MailWrapper
            $mailWrapper = Psr11::container()->get(MailWrapperInterface::class);
            $envelope = Psr11::container()->get('MAIL_ENVELOPE', [$json->email, "RestTemplate - Password Reset", "email_code.html", [
                "code" => trim(chunk_split($code, 1, ' ')),
                "expire" => 10
            ]]);

            $mailWrapper->send($envelope);
        }

        $response->write(['token' => $token]);
    }

    protected function validateResetToken($response, $request)
    {
        $this->validateRequest($request);

        $json = json_decode($request->payload());

        $users = Psr11::container()->get(UsersDBDataset::class);
        $user = $users->getByEmail($json->email);

        if (is_null($user)) {
            throw new Error422Exception("Invalid data");
        }

        if ($user->get("resettoken") != $json->token) {
            throw new Error422Exception("Invalid data");
        }

        if (strtotime($user->get("resettokenexpire")) < time()) {
            throw new Error422Exception("Invalid data");
        }

        return [$users, $user, $json];
    }

    /**
     * Initialize the Password Request
     *
     * @OA\Post(
     *     path="/login/confirmcode",
     *     tags={"Login"},
     *     @OA\RequestBody(
     *         description="The email and code to confirm the password reset",
     *         required=true,
     *         @OA\MediaType(
     *            mediaType="application/json",
     *            @OA\Schema(
     *              required={"email", "token", "code"},
     *              @OA\Property(property="email", type="string", description="Email"),
     *              @OA\Property(property="token", type="string", description="password reset token"),
     *              @OA\Property(property="code", type="string", description="password reset code"),
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
     *          )
     *       )
     *     ),
     *     @OA\Response(
     *        response=422,
     *        description="Invalid data",
     *        @OA\JsonContent(ref="#/components/schemas/error")
     *     ),
     * )
     *
     * @param HttpResponse $response
     * @param HttpRequest $request
     * @throws Error422Exception
     */
    public function postConfirmCode(HttpResponse $response, HttpRequest $request)
    {
        list($users, $user, $json) = $this->validateResetToken($response, $request);

        if ($user->get("resetcode") != $json->code) {
            throw new Error422Exception("Invalid data");
        }

        $user->set("resetallowed", "yes");
        $users->save($user);

        $response->write(['token' => $json->token]);
    }

    /**
     * Initialize the Password Request
     *
     * @OA\Post(
     *     path="/login/resetpassword",
     *     tags={"Login"},
     *     @OA\RequestBody(
     *         description="The email and the new password",
     *         required=true,
     *         @OA\MediaType(
     *            mediaType="application/json",
     *            @OA\Schema(
     *              required={"email", "token", "password"},
     *              @OA\Property(property="email", type="string", description="Email"),
     *              @OA\Property(property="token", type="string", description="password reset token"),
     *              @OA\Property(property="password", type="string", description="new password"),
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
     *          )
     *       )
     *     ),
     *     @OA\Response(
     *        response=422,
     *        description="Invalid data",
     *        @OA\JsonContent(ref="#/components/schemas/error")
     *     ),
     * )
     *
     * @param HttpResponse $response
     * @param HttpRequest $request
     * @throws Error422Exception
     */
    public function postResetPassword(HttpResponse $response, HttpRequest $request)
    {
        list($users, $user, $json) = $this->validateResetToken($response, $request);

        if ($user->get("resetallowed") != "yes") {
            throw new Error422Exception("Invalid data");
        }

        $user->setPassword($json->password);
        $user->set("resettoken", null);
        $user->set("resettokenexpire", null);
        $user->set("resetcode", null);
        $user->set("resetallowed", null);
        $users->save($user);

        $response->write(['token' => $json->token]);
    }



    /**
     * @param ?UserModel $user
     * @return array
     * @throws Error401Exception
     */
    private function createUserMetadata(?UserModel $user)
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
