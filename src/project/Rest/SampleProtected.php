<?php

namespace RestTemplate\Rest;

use Builder\Psr11;
use RestTemplate\Model\User;

class SampleProtected extends ServiceAbstractBase
{
    /**
     * Sample Ping Only Authenticated
     *
     * @SWG\Get(
     *     path="/sampleprotected/ping",
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
     *
     * @throws \ByJG\RestServer\Exception\Error401Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getPing()
    {
        $this->requireAuthenticated();

        $this->getResponse()->write([
            'result' => 'pong'
        ]);
    }

    /**
     * Sample Ping Only Admin
     *
     * @SWG\Get(
     *     path="/sampleprotected/pingadm",
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
     *
     * @throws \ByJG\RestServer\Exception\Error401Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getPingAdm()
    {
        $this->requireRole('admin');

        $this->getResponse()->write([
            'result' => 'pongadm'
        ]);
    }

    /**
     * Sample how to add an user;
     *
     * @SWG\Post(
     *     path="/sampleprotected/adduser",
     *     tags={"sampleprotected"},
     *     security={{
     *         "jwt-token":{}
     *     }},
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         description="The login data",
     *         required=true,
     *         @SWG\Schema(
     *              required={"username","password"},
     *              @SWG\Property(property="name", type="string", description="The Name"),
     *              @SWG\Property(property="email", type="string", description="The Email"),
     *              @SWG\Property(property="username", type="string", description="The username"),
     *              @SWG\Property(property="password", type="string", description="The password"),
     *         )
     *     ),
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
     * @throws \ByJG\RestServer\Exception\Error401Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function postAddUser()
    {
        $this->requireRole('admin');

        $data = json_decode($this->getRequest()->payload());
        $user = new User($data->name, $data->email, $data->username, $data->password);
        $users = Psr11::container()->getClosure('LOGIN');
        $users->save($user);

        $savedUser = $users->getByEmail($data->email);

        $updateField = $users->getUserDefinition()->getClosureForUpdate('userid');
        $users->removeUserById($updateField($savedUser->getUserid()));

        $this->getResponse()->write([
            'result' => 'pong'
        ]);
    }
}
