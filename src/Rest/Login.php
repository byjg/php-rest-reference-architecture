<?php

namespace RestReferenceArchitecture\Rest;

use ByJG\Authenticate\Service\UsersService;
use ByJG\Config\Config;
use ByJG\Mail\Wrapper\MailWrapperInterface;
use ByJG\RestServer\Attributes\RequireAuthenticated;
use ByJG\RestServer\Exception\Error401Exception;
use ByJG\RestServer\Exception\Error422Exception;
use ByJG\RestServer\HttpRequest;
use ByJG\RestServer\HttpResponse;
use ByJG\RestServer\SerializationRuleEnum;
use OpenApi\Attributes as OA;
use RestReferenceArchitecture\Attributes\ValidateRequest;
use RestReferenceArchitecture\Model\User;
use RestReferenceArchitecture\Repository\BaseRepository;
use RestReferenceArchitecture\Util\JwtContext;
use RestReferenceArchitecture\Util\OpenApiContext;

class Login
{
    /**
     * Do login
     *
     */
    #[OA\Post(
        path: "/login",
        tags: ["Login"],
    )]
    #[OA\RequestBody(
        description: "The Login Data",
        required: true,
        content: new OA\JsonContent(
            required: [ "username", "password" ],
            properties: [
                new OA\Property(property: "username", description: "The Username", type: "string", format: "string"),
                new OA\Property(property: "password", description: "The Password",  type: "string", format: "string")
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "The object to be created",
        content: new OA\JsonContent(
            required: [ "token" ],
            properties: [
                new OA\Property(property: "token", type: "string"),
                new OA\Property(property: "data", properties: [
                    new OA\Property(property: "userid", type: "string"),
                    new OA\Property(property: "name", type: "string"),
                    new OA\Property(property: "role", type: "string"),
                ])
            ]
        )
    )]
    #[ValidateRequest]
    public function post(HttpResponse $response, HttpRequest $request)
    {
        // Get the validated payload - returns array for JSON content-type
        $json = ValidateRequest::getPayload();

        $userToken = JwtContext::createUserMetadata($json["username"], $json["password"]);

        $response->getResponseBag()->setSerializationRule(SerializationRuleEnum::SingleObject);
        $response->write(['token' => $userToken->token]);
        $response->write(['data' => $userToken->data]);
    }

    /**
     * Refresh Token
     *
     */
    #[OA\Post(
        path: "/refreshtoken",
        security: [
            ["jwt-token" => []]
        ],
        tags: ["Login"]
    )]
    #[OA\Response(
        response: 200,
        description: "The object rto be created",
        content: new OA\JsonContent(
            required: [ "token" ],
            properties: [
                new OA\Property(property: "token", type: "string"),
                new OA\Property(property: "data", properties: [
                    new OA\Property(property: "role", type: "string"),
                    new OA\Property(property: "userid", type: "string"),
                    new OA\Property(property: "name", type: "string")
                ], type: "object")
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: "Não autorizado",
        content: new OA\JsonContent(ref: "#/components/schemas/error")
    )]
    #[RequireAuthenticated]
    public function refreshToken(HttpResponse $response, HttpRequest $request)
    {
        $diff = ($request->param("jwt.exp") - time()) / 60;

        if ($diff > 5) {
            throw new Error401Exception("You only can refresh the token 5 minutes before expire");
        }

        /** @var UsersService $usersService */
        $usersService = Config::get(UsersService::class);
        $user = $usersService->getById(JwtContext::getUserId());

        $metadata = JwtContext::createUserMetadata($user);

        $response->getResponseBag()->setSerializationRule(SerializationRuleEnum::SingleObject);
        $response->write(['token' => $metadata->token]);
        $response->write(['data' => $metadata->data]);

    }

    /**
     * Initialize the Password Request
     *
     */
    #[OA\Post(
        path: "/login/resetrequest",
        tags: ["Login"]
    )]
    #[OA\RequestBody(
        description: "The email to have the password reset",
        content: new OA\JsonContent(
            required: [ "email" ],
            properties: [
                new OA\Property(property: "email", type: "string"),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "The token for reset",
        content: new OA\JsonContent(
            required: [ "token" ],
            properties: [
                new OA\Property(property: "token", type: "string"),
            ]
        )
    )]
    #[ValidateRequest]
    public function postResetRequest(HttpResponse $response, HttpRequest $request)
    {
        $json = ValidateRequest::getPayload();

        $usersService = Config::get(UsersService::class);
        $user = $usersService->getByEmail($json["email"]);

        $token = BaseRepository::getUuid();
        $code = rand(10000, 99999);

        if (!is_null($user)) {
            $user->set(User::PROP_RESETTOKEN, $token);
            $user->set(User::PROP_RESETTOKENEXPIRE, date('Y-m-d H:i:s', strtotime('+10 minutes')));
            $user->set(User::PROP_RESETCODE, $code);
            $user->set(User::PROP_RESETALLOWED, null);
            $usersService->save($user);

            // Send email using MailWrapper
            $mailWrapper = Config::get(MailWrapperInterface::class);
            $envelope = Config::get('MAIL_ENVELOPE', [$json["email"], "ByJGService - Password Reset", "email_code.html", [
                "code" => trim(chunk_split($code, 1, ' ')),
                "expire" => 10
            ]]);

            $mailWrapper->send($envelope);
        }

        $response->write(['token' => $token]);
    }

    protected function validateResetToken($response, $request): array
    {
        $json = OpenApiContext::validateRequest($request);

        $usersService = Config::get(UsersService::class);
        $user = $usersService->getByEmail($json["email"]);

        if (is_null($user)) {
            throw new Error422Exception("Invalid data");
        }

        if ($user->get("resettoken") !== ($json["token"] ?? null)) {
            throw new Error422Exception("Invalid data");
        }

        if (strtotime($user->get("resettokenexpire")) < time()) {
            throw new Error422Exception("Invalid data");
        }

        return [$usersService, $user, $json];
    }

    /**
     * Initialize the Password Request
     *
     */
    #[OA\Post(
        path: "/login/confirmcode",
        tags: ["Login"]
    )]
    #[OA\RequestBody(
        description: "The email and code to confirm the password reset",
        content: new OA\JsonContent(
            required: [ "email", "token", "code" ],
            properties: [
                new OA\Property(property: "email", type: "string"),
                new OA\Property(property: "token", type: "string"),
                new OA\Property(property: "code", type: "string"),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "The token for reset",
        content: new OA\JsonContent(
            required: [ "token" ],
            properties: [
                new OA\Property(property: "token", type: "string"),
            ]
        )
    )]
    #[OA\Response(
        response: 422,
        description: "Invalid data",
        content: new OA\JsonContent(ref: "#/components/schemas/error")
    )]
    public function postConfirmCode(HttpResponse $response, HttpRequest $request)
    {
        list($usersService, $user, $json) = $this->validateResetToken($response, $request);

        if ($user->get("resetcode") != $json["code"]) {
            throw new Error422Exception("Invalid data");
        }

        $user->set("resetallowed", "yes");
        $usersService->save($user);

        $response->write(['token' => $json["token"]]);
    }

    /**
     * Initialize the Password Request
     *
     */
    #[OA\Post(
        path: "/login/resetpassword",
        tags: ["Login"]
    )]
    #[OA\RequestBody(
        description: "The email and the new password",
        content: new OA\JsonContent(
            required: [ "email", "token", "password" ],
            properties: [
                new OA\Property(property: "email", type: "string"),
                new OA\Property(property: "token", type: "string"),
                new OA\Property(property: "password", type: "string"),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "The token for reset",
        content: new OA\JsonContent(
            required: [ "token" ],
            properties: [
                new OA\Property(property: "token", type: "string"),
            ]
        )
    )]
    #[OA\Response(
        response: 422,
        description: "Invalid data",
        content: new OA\JsonContent(ref: "#/components/schemas/error")
    )]
    public function postResetPassword(HttpResponse $response, HttpRequest $request)
    {
        list($usersService, $user, $json) = $this->validateResetToken($response, $request);

        if ($user->get("resetallowed") != "yes") {
            throw new Error422Exception("Invalid data");
        }

        $user->setPassword($json["password"]);
        $user->set("resettoken", null);
        $user->set("resettokenexpire", null);
        $user->set("resetcode", null);
        $user->set("resetallowed", null);
        $usersService->save($user);

        $response->write(['token' => $json["token"]]);
    }
}
