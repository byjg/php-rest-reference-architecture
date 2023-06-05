<?php

namespace RestTemplate\Rest;

use ByJG\ApiTools\Base\Schema;
use ByJG\Config\Exception\ConfigException;
use ByJG\Config\Exception\ConfigNotFoundException;
use ByJG\Config\Exception\DependencyInjectionException;
use ByJG\Config\Exception\InvalidDateException;
use ByJG\Config\Exception\KeyNotFoundException;
use ByJG\RestServer\Exception\Error400Exception;
use ByJG\RestServer\Exception\Error401Exception;
use ByJG\RestServer\Exception\Error403Exception;
use ByJG\RestServer\HttpRequest;
use ByJG\Util\JwtWrapper;
use Exception;
use Psr\SimpleCache\InvalidArgumentException;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;
use ReflectionException;
use RestTemplate\Psr11;

class ServiceAbstractBase extends ServiceAbstract
{

    /**
     * @param array $properties
     * @return mixed
     * @throws ConfigNotFoundException
     * @throws InvalidArgumentException
     * @throws KeyNotFoundException
     * @throws ReflectionException
     * @throws ConfigException
     * @throws DependencyInjectionException
     * @throws InvalidDateException
     */
    public function createToken(array $properties = [])
    {
        $jwt = Psr11::container()->get(JwtWrapper::class);
        $jwtData = $jwt->createJwtData($properties, 1800);
        return $jwt->generateToken($jwtData);
    }

    /**
     * @param null $token
     * @param bool $fullToken
     * @return mixed
     * @throws Error401Exception
     * @throws InvalidArgumentException
     */
    public function requireAuthenticated($token = null, bool $fullToken = false)
    {
        try {
            $jwt = Psr11::container()->get(JwtWrapper::class);
            $tokenInfo = json_decode(json_encode($jwt->extractData($token)), true);
            if ($fullToken) {
                return $tokenInfo;
            } else {
                return $tokenInfo['data'];
            }
        } catch (Exception $ex) {
            throw new Error401Exception($ex->getMessage());
        }
    }

    /**
     * @param $role
     * @param null $token
     * @return mixed
     * @throws Error401Exception
     * @throws Error403Exception
     * @throws InvalidArgumentException
     */
    public function requireRole($role, $token = null)
    {
        $data = $this->requireAuthenticated($token);
        if ($data['role'] !== $role) {
            throw new Error403Exception('Insufficient privileges');
        }
        return $data;
    }

    public function validateRequest(HttpRequest $request)
    {
        $schema = Psr11::container()->get(Schema::class);

        $path = $request->getRequestPath();
        $method = $request->server('REQUEST_METHOD');

        // Returns a SwaggerRequestBody instance
        $bodyRequestDef = $schema->getRequestParameters($path, $method);

        // Validate the request body (payload)
        $requestBody = json_decode($request->payload(), true);
        try {
            $bodyRequestDef->match($requestBody);
        } catch (Exception $ex) {
            throw new Error400Exception(explode("\n", $ex->getMessage())[0]);
        }

        return $requestBody;
    }
}
