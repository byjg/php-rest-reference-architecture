<?php

namespace RestTemplate\Rest;

use ByJG\RestServer\Exception\Error404Exception;
use ByJG\Serializer\BinderObject;
use RestTemplate\Model\Dummy;
use RestTemplate\Repository\DummyRepository;

class Sample extends ServiceAbstractBase
{
    /**
     * Simple ping
     *
     * @SWG\Get(
     *     path="/sample/ping",
     *     tags={"sample"},
     *     @SWG\Response(
     *         response=200,
     *         description="The object",
     *         @SWG\Schema(
     *            required={"result"},
     *            @SWG\Property(property="result", type="string")
     *         )
     *     )
     * )
     */
    public function getPing()
    {
        $this->getResponse()->write([
            'result' => 'pong'
        ]);
    }

    /**
     * Get the rows from the Dummy table (used in the example)
     *
     * @SWG\Get(
     *     path="/sample/dummy/{field}",
     *     tags={"sample"},
     *     @SWG\Parameter(
     *         name="field",
     *         in="path",
     *         description="The field content to be returned",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="The object",
     *         @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/Dummy"))
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Not found",
     *         @SWG\Schema(ref="#/definitions/error")
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="Erro Geral",
     *         @SWG\Schema(ref="#/definitions/error")
     *     )
     * )
     *
     * @param \ByJG\RestServer\HttpResponse $response
     * @param \ByJG\RestServer\HttpRequest $request
     * @throws \ByJG\Config\Exception\ConfigNotFoundException
     * @throws \ByJG\Config\Exception\EnvironmentException
     * @throws \ByJG\Config\Exception\KeyNotFoundException
     * @throws \ByJG\RestServer\Exception\Error404Exception
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getDummy()
    {
        $dummyRepo = new DummyRepository();
        $field = $this->getRequest()->get('field');

        $result = $dummyRepo->getByField($field);
        if (empty($result)) {
            throw new Error404Exception('Pattern not found');
        }
        $this->getResponse()->write(
            $result
        );
    }

    /**
     * Save data content in the table Dummy
     * @SWG\Post(
     *     path="/sample/dummy",
     *     tags={"sample"},
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         description="The dummy data",
     *         required=true,
     *         @SWG\Schema(ref="#/definitions/Dummy")
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="The object",
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="Erro Geral",
     *         @SWG\Schema(ref="#/definitions/error")
     *     )
     * )
     *
     * @param \ByJG\RestServer\HttpResponse $response
     * @param \ByJG\RestServer\HttpRequest $request
     * @throws \ByJG\Config\Exception\ConfigNotFoundException
     * @throws \ByJG\Config\Exception\EnvironmentException
     * @throws \ByJG\Config\Exception\KeyNotFoundException
     * @throws \ByJG\MicroOrm\Exception\InvalidArgumentException
     * @throws \ByJG\MicroOrm\Exception\OrmBeforeInvalidException
     * @throws \ByJG\MicroOrm\Exception\OrmInvalidFieldsException
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function postDummy()
    {
        $model = new Dummy();
        $payload = json_decode($this->getRequest()->payload());
        BinderObject::bindObject($payload, $model);

        $dummyRepo = new DummyRepository();
        $dummyRepo->save($model);
    }
}
