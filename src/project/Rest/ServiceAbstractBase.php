<?php
/**
 * User: jg
 * Date: 30/09/17
 * Time: 18:10
 */

namespace RestTemplate\Rest;

use Builder\Psr11;
use ByJG\RestServer\Exception\Error401Exception;
use ByJG\RestServer\ServiceAbstract;
use ByJG\Util\JwtWrapper;

class ServiceAbstractBase extends ServiceAbstract
{

    /**
     * @param array $properties
     * @return mixed
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function createToken($properties = [])
    {
        $jwt = new JwtWrapper(Psr11::container()->get('JWT_SERVER'), Psr11::container()->get('JWT_SECRET'));
        $jwtData = $jwt->createJwtData($properties, 1800);
        return $jwt->generateToken($jwtData);
    }

    /**
     * @param null $token
     * @return mixed
     * @throws \ByJG\RestServer\Exception\Error401Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function requireAuthenticated($token = null)
    {
        try {
            $jwt = new JwtWrapper(Psr11::container()->get('JWT_SERVER'), Psr11::container()->get('JWT_SECRET'));
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
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
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
