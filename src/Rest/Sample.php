<?php

namespace RestTemplate\Rest;

use RestTemplate\Psr11;
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
use ReflectionException;
use RestTemplate\Model\Dummy;
use RestTemplate\Model\DummyHex;
use RestTemplate\Repository\DummyHexRepository;
use RestTemplate\Repository\DummyRepository;
use OpenApi\Annotations as OA;

class Sample extends ServiceAbstractBase
{
    /**
     * Simple ping
     *
     * @OA\Get(
     *     path="/sample/ping",
     *     tags={"zz_sample"},
     *     @OA\Response(
     *         response=200,
     *         description="The object",
     *         @OA\MediaType(
     *           mediaType="application/json",
     *           @OA\Schema(
     *             required={"result"},
     *             @OA\Property(property="result", type="string")
     *           )
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
}
