<?php

namespace RestTemplate\Rest;

use ByJG\RestServer\Exception\Error401Exception;
use ByJG\Util\JwtWrapper;
use Builder\Psr11;

class ServiceAbstractBase
{

    /**
     * @param array $properties
     * @return mixed
     * @throws \ByJG\Config\Exception\ConfigNotFoundException
     * @throws \ByJG\Config\Exception\EnvironmentException
     * @throws \ByJG\Config\Exception\KeyNotFoundException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function createToken($properties = [])
    {
        $jwt = new JwtWrapper(Psr11::container()->get('API_SERVER'), Psr11::container()->get('JWT_SECRET'));
        $jwtData = $jwt->createJwtData($properties, 1800);
        return $jwt->generateToken($jwtData);
    }

    /**
     * @param null $token
     * @return mixed
     * @throws \ByJG\RestServer\Exception\Error401Exception
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function requireAuthenticated($token = null)
    {
        try {
            $jwt = new JwtWrapper(Psr11::container()->get('API_SERVER'), Psr11::container()->get('JWT_SECRET'));
            $tokenInfo = json_decode(json_encode($jwt->extractData($token)), true);
            return $tokenInfo['data'];
        } catch (\Exception $ex) {
            throw new Error401Exception($ex->getMessage());
        }
    }

    /**
     * @param $role
     * @param null $token
     * @return mixed
     * @throws \ByJG\RestServer\Exception\Error401Exception
     * @throws \Psr\SimpleCache\InvalidArgumentException
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
