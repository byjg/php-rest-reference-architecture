<?php

namespace RestReferenceArchitecture\Util;

use ByJG\Authenticate\Model\UserModel;
use ByJG\Config\Exception\ConfigException;
use ByJG\Config\Exception\ConfigNotFoundException;
use ByJG\Config\Exception\DependencyInjectionException;
use ByJG\Config\Exception\InvalidDateException;
use ByJG\Config\Exception\KeyNotFoundException;
use ByJG\JwtWrapper\JwtWrapper;
use ByJG\MicroOrm\Literal\HexUuidLiteral;
use ByJG\RestServer\Exception\Error401Exception;
use ByJG\RestServer\Exception\Error403Exception;
use ByJG\RestServer\HttpRequest;
use ByJG\RestServer\Middleware\JwtMiddleware;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;
use RestReferenceArchitecture\Model\User;
use RestReferenceArchitecture\Psr11;

class JwtContext
{
    protected static ?HttpRequest $request;

    /**
     * @param ?UserModel $user
     * @return array
     * @throws Error401Exception
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
     * @throws ConfigException
     * @throws ConfigNotFoundException
     * @throws DependencyInjectionException
     * @throws InvalidArgumentException
     * @throws InvalidDateException
     * @throws KeyNotFoundException
     * @throws ReflectionException
     */
    public static function createToken($properties = [])
    {
        $jwt = Psr11::container()->get(JwtWrapper::class);
        $jwtData = $jwt->createJwtData($properties, 60 * 60 * 24 * 7); // 7 Dias
        return $jwt->generateToken($jwtData);
    }

    /**
     * @param HttpRequest $request
     * @return void
     * @throws Error401Exception
     */
    public static function requireAuthenticated(HttpRequest $request): void
    {
        self::$request = $request;
        if ($request->param(JwtMiddleware::JWT_PARAM_PARSE_STATUS) !== JwtMiddleware::JWT_SUCCESS) {
            throw new Error401Exception($request->param(JwtMiddleware::JWT_PARAM_PARSE_MESSAGE));
        }
    }

    public static function parseJwt(HttpRequest $request): void
    {
        self::$request = $request;
    }

    /**
     * @param HttpRequest $request
     * @param string $role
     * @return void
     * @throws Error401Exception
     * @throws Error403Exception
     * @throws InvalidArgumentException
     */
    public static function requireRole(HttpRequest $request, string $role): void
    {
        self::requireAuthenticated($request);
        if (JwtContext::getRole() !== $role) {
            throw new Error403Exception('Insufficient privileges');
        }
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
