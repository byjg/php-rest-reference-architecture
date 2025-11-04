<?php

namespace RestReferenceArchitecture\Util;

use ByJG\Config\Config;
use ByJG\Config\Exception\ConfigException;
use ByJG\Config\Exception\ConfigNotFoundException;
use ByJG\Config\Exception\DependencyInjectionException;
use ByJG\Config\Exception\InvalidDateException;
use ByJG\Config\Exception\KeyNotFoundException;
use ByJG\JwtWrapper\JwtWrapper;
use ByJG\MicroOrm\Literal\HexUuidLiteral;
use ByJG\RestServer\Exception\Error401Exception;
use ByJG\RestServer\HttpRequest;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;
use RestReferenceArchitecture\Model\User;

class JwtContext
{
    protected static ?HttpRequest $request;

    /**
     * @param ?User $user
     * @return array
     * @throws Error401Exception
     * @throws \ByJG\MicroOrm\Exception\InvalidArgumentException
     */
    public static function createUserMetadata(?User $user): array
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

    public static function parseJwt(HttpRequest $request): void
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
