<?php

namespace RestTemplate\Rest;

use Builder\Psr11;
use ByJG\Config\Exception\ConfigNotFoundException;
use ByJG\Config\Exception\EnvironmentException;
use ByJG\Config\Exception\KeyNotFoundException;
use ByJG\RestServer\Exception\Error401Exception;
use Exception;
use Psr\SimpleCache\InvalidArgumentException;

class ServiceAbstractBase extends ServiceAbstract
{

    /**
     * @param array $properties
     * @return mixed
     * @throws ConfigNotFoundException
     * @throws EnvironmentException
     * @throws KeyNotFoundException
     * @throws InvalidArgumentException
     */
    public function createToken($properties = [])
    {
        $jwt = Psr11::container()->get('JWT_WRAPPER');
        $jwtData = $jwt->createJwtData($properties, 1800);
        return $jwt->generateToken($jwtData);
    }

    /**
     * @param null $token
     * @return mixed
     * @throws Error401Exception
     * @throws InvalidArgumentException
     */
    public function requireAuthenticated($token = null)
    {
        try {
            $jwt = Psr11::container()->get('JWT_WRAPPER');
            $tokenInfo = json_decode(json_encode($jwt->extractData($token)), true);
            return $tokenInfo['data'];
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
