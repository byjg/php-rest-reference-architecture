<?php

namespace RestReferenceArchitecture\Util;

use ByJG\Authenticate\Enum\UserField;
use ByJG\Authenticate\Model\UserToken;
use ByJG\Authenticate\Service\UsersService;
use ByJG\Config\Config;
use ByJG\Config\Exception\ConfigException;
use ByJG\Config\Exception\ConfigNotFoundException;
use ByJG\Config\Exception\DependencyInjectionException;
use ByJG\Config\Exception\InvalidDateException;
use ByJG\Config\Exception\KeyNotFoundException;
use ByJG\Config\Exception\RunTimeException;
use ByJG\JwtWrapper\JwtWrapper;
use ByJG\RestServer\Exception\Error401Exception;
use ByJG\RestServer\HttpRequest;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;
use RestReferenceArchitecture\Model\User;

class JwtContext
{
    protected static ?HttpRequest $request;

    /**
     * @param User|string $user
     * @param string $password
     * @return UserToken
     * @throws ConfigException
     * @throws DependencyInjectionException
     * @throws Error401Exception
     * @throws InvalidArgumentException
     * @throws KeyNotFoundException
     * @throws ReflectionException
     * @throws RunTimeException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function createUserMetadata(User|string $user, string $password = ""): UserToken
    {
        /** @var UsersService $usersService */
        $usersService = Config::get(UsersService::class);

        try {
            $jwtWrapper = Config::get(JwtWrapper::class);
            $expires = 3600;
            $tokenFields = [
                UserField::Userid,
                UserField::Name,
                UserField::Role,
            ];

            if (empty($password)) {
                $userToken = $usersService->createInsecureAuthToken(
                    login: $user,
                    jwtWrapper: $jwtWrapper,
                    expires: $expires,
                    tokenUserFields: $tokenFields
                );
            } else {
                $userToken = $usersService->createAuthToken(
                    login: $user,
                    password: $password,
                    jwtWrapper: $jwtWrapper,
                    expires: $expires,
                    tokenUserFields: $tokenFields
                );
            }
        } catch (Exception $ex) {
            throw new Error401Exception($ex->getMessage());
        }

        return $userToken;
    }

    /**
     * @param array $properties
     * @return mixed
     * @throws ConfigException
     * @throws ConfigNotFoundException
     * @throws DependencyInjectionException
     * @throws InvalidArgumentException
     * @throws InvalidDateException
     * @throws KeyNotFoundException
     * @throws ReflectionException
     */
    public static function createToken(array $properties = []): mixed
    {
        $jwt = Config::get(JwtWrapper::class);
        $jwtData = $jwt->createJwtData($properties, 60 * 60 * 24 * 7); // 7 Dias
        return $jwt->generateToken($jwtData);
    }

    public static function setRequest(HttpRequest $request): void
    {
        self::$request = $request;
    }

    protected static function getRequestParam(string $value): ?string
    {
        if (isset(self::$request)) {
            $data = (array)self::$request->param("jwt.data");
            if (isset($data[$value])) {
                return $data[$value];
            }
        }
        return null;
    }

    public static function getUserId(): ?string
    {
        return self::getRequestParam("userid");
    }

    public static function getRole(): ?string
    {
        return self::getRequestParam("role");
    }

    public static function getName(): ?string
    {
        return self::getRequestParam("name");
    }

}
