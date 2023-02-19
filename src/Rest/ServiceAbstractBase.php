<?php

namespace RestTemplate\Rest;

use ByJG\ApiTools\Base\Schema;
use ByJG\Config\Exception\ConfigNotFoundException;
use ByJG\Config\Exception\EnvironmentException;
use ByJG\Config\Exception\KeyNotFoundException;
use ByJG\RestServer\Exception\Error400Exception;
use ByJG\RestServer\Exception\Error401Exception;
use ByJG\RestServer\HttpRequest;
use ByJG\Util\JwtWrapper;
use Exception;
use Exception;
use Psr\SimpleCache\InvalidArgumentException;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;
use ReflectionException;

class ServiceAbstractBase extends ServiceAbstract
{

    /**
     * @param array $properties
     * @return mixed
     * @throws ConfigNotFoundException
     * @throws EnvironmentException
     * @throws InvalidArgumentException
     * @throws KeyNotFoundException
     * @throws ReflectionException
     */
    public function createToken($properties = [])
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
    public function requireAuthenticated($token = null, $fullToken = false)
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
     * @throws InvalidArgumentException
     */
    public function requireRole($role, $token = null)
    {
        $data = $this->requireAuthenticated($token);
        if ($data['role'] !== $role) {
            throw new Error401Exception('Insufficient privileges - ' . print_r($data, true));
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
