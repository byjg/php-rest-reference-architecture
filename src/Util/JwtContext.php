<?php

namespace RestReferenceArchitecture\Util;

use ByJG\Authenticate\Model\UserModel;
use ByJG\Config\Exception\ConfigException;
use ByJG\Config\Exception\ConfigNotFoundException;
use ByJG\Config\Exception\DependencyInjectionException;
use ByJG\Config\Exception\InvalidDateException;
use ByJG\Config\Exception\KeyNotFoundException;
use ByJG\RestServer\Exception\Error401Exception;
use ByJG\RestServer\Exception\Error403Exception;
use ByJG\Util\JwtWrapper;
use Exception;
use Psr\SimpleCache\InvalidArgumentException;
use RestReferenceArchitecture\Model\User;
use RestReferenceArchitecture\Psr11;

class JwtContext
{
    /**
     * @param ?UserModel $user
     * @return array
     * @throws Error401Exception
     * @throws Error403Exception
     */
    public static function createUserMetadata(?UserModel $user): array
    {
        if (is_null($user)) {
            throw new Error401Exception('Username or password is invalid');
        }

        return [
            'role' => ($user->getAdmin() === User::VALUE_YES ? User::ROLE_ADMIN : User::ROLE_USER),
            'userid' => HexUuidLiteral::getFormattedUuid($user->getUserid()),
            'name' => $user->getName(),
        ];
    }

    /**
     * @param array $properties
     * @return mixed
     * @throws ConfigNotFoundException
     * @throws DependencyInjectionException
     * @throws InvalidArgumentException
     * @throws KeyNotFoundException
     * @throws ConfigException
     * @throws InvalidDateException
     */
    public static function createToken($properties = [])
    {
        $jwt = Psr11::container()->get(JwtWrapper::class);
        $jwtData = $jwt->createJwtData($properties, 60 * 60 * 24 * 7); // 7 Dias
        return $jwt->generateToken($jwtData);
    }

    public static function extractToken($token = null, $fullToken = false, $throwException = false)
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
    public static function requireAuthenticated($token = null, $fullToken = false)
    {
        return self::extractToken($token, $fullToken, true);
    }

    /**
     * @param $role
     * @param string|null $token
     * @return mixed
     * @throws Error401Exception
     * @throws Error403Exception
     * @throws InvalidArgumentException
     */
    public static function requireRole($role, $token = null)
    {
        $data = self::requireAuthenticated($token);
        if ($data['role'] !== $role) {
            throw new Error403Exception('Insufficient privileges');
        }
        return $data;
    }
}
