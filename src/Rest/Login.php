<?php

namespace RestReferenceArchitecture\Rest;

use ByJG\Authenticate\UsersDBDataset;
use ByJG\Mail\Wrapper\MailWrapperInterface;
use ByJG\RestServer\Exception\Error401Exception;
use ByJG\RestServer\Exception\Error422Exception;
use ByJG\RestServer\HttpRequest;
use ByJG\RestServer\HttpResponse;
use ByJG\RestServer\ResponseBag;
use OpenApi\Attributes as OA;
use RestReferenceArchitecture\Model\User;
use RestReferenceArchitecture\Psr11;
use RestReferenceArchitecture\Repository\BaseRepository;
use RestReferenceArchitecture\Util\HexUuidLiteral;
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
    public function post(HttpResponse $response, HttpRequest $request)
    {
        OpenApiContext::validateRequest($request);

        $json = json_decode($request->payload());

        $users = Psr11::container()->get(UsersDBDataset::class);
        $user = $users->isValidUser($json->username, $json->password);
        $metadata = JwtContext::createUserMetadata($user);

        $response->getResponseBag()->setSerializationRule(ResponseBag::SINGLE_OBJECT);
        $response->write(['token' => JwtContext::createToken($metadata)]);
        $response->write(['data' => $metadata]);
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
    public function refreshToken(HttpResponse $response, HttpRequest $request)
    {
        $result = JwtContext::requireAuthenticated(null, true);

        $diff = ($result["exp"] - time()) / 60;

        if ($diff > 5) {
            throw new Error401Exception("You only can refresh the token 5 minutes before expire");
        }

        $users = Psr11::container()->get(UsersDBDataset::class);
        $user = $users->getById(new HexUuidLiteral($result["data"]["userid"]));

        $metadata = JwtContext::createUserMetadata($user);

        $response->getResponseBag()->setSerializationRule(ResponseBag::SINGLE_OBJECT);
        $response->write(['token' => JwtContext::createToken($metadata)]);
        $response->write(['data' => $metadata]);

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
    public function postResetRequest(HttpResponse $response, HttpRequest $request)
    {
        OpenApiContext::validateRequest($request);

        $json = json_decode($request->payload());

        $users = Psr11::container()->get(UsersDBDataset::class);
        $user = $users->getByEmail($json->email);

        $token = BaseRepository::getUuid();
        $code = rand(10000, 99999);

        if (!is_null($user)) {
            $user->set(User::PROP_RESETTOKEN, $token);
            $user->set(User::PROP_RESETTOKENEXPIRE, date('Y-m-d H:i:s', strtotime('+10 minutes')));
            $user->set(User::PROP_RESETCODE, $code);
            $user->set(User::PROP_RESETALLOWED, null);
            $users->save($user);

            // Send email using MailWrapper
            $mailWrapper = Psr11::container()->get(MailWrapperInterface::class);
            $envelope = Psr11::container()->get('MAIL_ENVELOPE', [$json->email, "RestReferenceArchitecture - Password Reset", "email_code.html", [
                "code" => trim(chunk_split($code, 1, ' ')),
                "expire" => 10
            ]]);

            $mailWrapper->send($envelope);
        }

        $response->write(['token' => $token]);
    }

    protected function validateResetToken($response, $request): array
    {
        OpenApiContext::validateRequest($request);

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
}
