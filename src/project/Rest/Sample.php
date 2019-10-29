<?php

namespace RestTemplate\Rest;

use ByJG\Config\Exception\ConfigNotFoundException;
use ByJG\Config\Exception\EnvironmentException;
use ByJG\Config\Exception\KeyNotFoundException;
use ByJG\MicroOrm\Exception\OrmBeforeInvalidException;
use ByJG\MicroOrm\Exception\OrmInvalidFieldsException;
use ByJG\RestServer\Exception\Error404Exception;
use ByJG\RestServer\HttpRequest;
use ByJG\RestServer\HttpResponse;
use ByJG\Serializer\BinderObject;
use Psr\SimpleCache\InvalidArgumentException;
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
     * @param HttpResponse $response
     * @param HttpRequest $request
     */
    public function getPing($response, $request)
    {
        $response->write([
            'result' => 'pong'
        ]);
    }

    /**
     * Get the rows from the Dummy table (used in the example)
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
     * @param HttpResponse $response
     * @param HttpRequest $request
     * @throws ConfigNotFoundException
     * @throws EnvironmentException
     * @throws KeyNotFoundException
     * @throws Error404Exception
     * @throws InvalidArgumentException
     */
    public function getDummy($response, $request)
    {
        $dummyRepo = new DummyRepository();
        $field = $request->get('field');

        $result = $dummyRepo->getByField($field);
        if (empty($result)) {
            throw new Error404Exception('Pattern not found');
        }
        $response->write(
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
     * @param HttpResponse $response
     * @param HttpRequest $request
     * @throws ConfigNotFoundException
     * @throws EnvironmentException
     * @throws KeyNotFoundException
     * @throws \ByJG\MicroOrm\Exception\InvalidArgumentException
     * @throws OrmBeforeInvalidException
     * @throws OrmInvalidFieldsException
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function postDummy($response, $request)
    {
        $model = new Dummy();
        $payload = json_decode($request->payload());
        BinderObject::bindObject($payload, $model);

        $dummyRepo = new DummyRepository();
        $dummyRepo->save($model);
    }
}
