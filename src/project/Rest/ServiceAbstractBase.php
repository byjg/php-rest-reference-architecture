<?php

namespace RestTemplate\Rest;

use Builder\Psr11;
use ByJG\Config\Exception\ConfigNotFoundException;
use ByJG\Config\Exception\EnvironmentException;
use ByJG\Config\Exception\KeyNotFoundException;
use ByJG\RestServer\Exception\Error401Exception;
use ByJG\Util\JwtWrapper;
use Exception;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;

class ServiceAbstractBase
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
}
