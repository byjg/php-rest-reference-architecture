<?php

namespace RestTemplate\Rest;

use ByJG\ApiTools\Base\Schema;
use ByJG\ApiTools\Exception\HttpMethodNotFoundException;
use ByJG\ApiTools\Exception\PathNotFoundException;
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
use ReflectionException;
use RestTemplate\Psr11;

class ServiceAbstractBase
{

    /**
     * @param array $properties
     * @return mixed
     * @throws ConfigNotFoundException
     * @throws DependencyInjectionException
     * @throws InvalidArgumentException
     * @throws KeyNotFoundException
     * @throws ReflectionException
     * @throws ConfigException
     * @throws InvalidDateException
     */
    public function createToken($properties = [])
    {
        $jwt = Psr11::container()->get(JwtWrapper::class);
        $jwtData = $jwt->createJwtData($properties, 60 * 60 * 10); // 10 hours
        return $jwt->generateToken($jwtData);
    }

    public function extractToken($token = null, $fullToken = false, $throwException = false)
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
            if ($throwException) {
                throw new Error401Exception($ex->getMessage());
            } else {
                return false;
            }
        }
    }

    /**
     * @param string|null $token
     * @param bool $fullToken
     * @return mixed
     * @throws Error401Exception
     * @throws InvalidArgumentException
     */
    public function requireAuthenticated($token = null, $fullToken = false)
    {
        return $this->extractToken($token, $fullToken, true);
    }

    /**
     * @param $role
     * @param string|null $token
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

    /**
     * @throws DependencyInjectionException
     * @throws InvalidDateException
     * @throws ConfigNotFoundException
     * @throws KeyNotFoundException
     * @throws Error400Exception
     * @throws InvalidArgumentException
     * @throws ConfigException
     * @throws PathNotFoundException
     * @throws ReflectionException
     * @throws HttpMethodNotFoundException
     */
    public function validateRequest(HttpRequest $request)
    {
        $schema = Psr11::container()->get(Schema::class);

        $path = $request->getRequestPath();
        $method = $request->server('REQUEST_METHOD');
        
        // Returns a SwaggerRequestBody instance
        $bodyRequestDef = $schema->getRequestParameters($path, $method);
        
        // Validate the request body (payload)
        if (str_contains($request->getHeader('Content-Type'), 'multipart/')) {
            $requestBody = $request->post();
            $files = $request->uploadedFiles()->getKeys();
            $requestBody = array_merge($requestBody, array_combine($files, $files));
        } else {
            $requestBody = json_decode($request->payload(), true);
        }

        try {
            $bodyRequestDef->match($requestBody);
        } catch (Exception $ex) {
            throw new Error400Exception(explode("\n", $ex->getMessage())[0]);
        }

        return $requestBody;
    }
}
