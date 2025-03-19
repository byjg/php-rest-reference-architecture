<?php

namespace RestReferenceArchitecture\Util;

use ByJG\ApiTools\Base\Schema;
use ByJG\Config\Exception\ConfigException;
use ByJG\Config\Exception\ConfigNotFoundException;
use ByJG\Config\Exception\DependencyInjectionException;
use ByJG\Config\Exception\InvalidDateException;
use ByJG\Config\Exception\KeyNotFoundException;
use ByJG\RestServer\Exception\Error400Exception;
use ByJG\RestServer\HttpRequest;
use ByJG\Serializer\Serialize;
use Exception;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;
use RestReferenceArchitecture\Psr11;

class OpenApiContext
{
    /**
     * @throws DependencyInjectionException
     * @throws InvalidDateException
     * @throws ConfigNotFoundException
     * @throws KeyNotFoundException
     * @throws Error400Exception
     * @throws InvalidArgumentException
     * @throws ConfigException
     * @throws ReflectionException
     */
    public static function validateRequest(HttpRequest $request, bool $allowNull = false)
    {
        $schema = Psr11::get(Schema::class);

        $path = $request->getRequestPath();
        $method = $request->server('REQUEST_METHOD');

        // Validate the request body (payload)
        if (str_contains($request->getHeader('Content-Type') ?? "", 'multipart/')) {
            $requestBody = $request->post();
            $files = $request->uploadedFiles()->getKeys();
            $requestBody = array_merge($requestBody, array_combine($files, $files));
        } else {
            $requestBody = json_decode($request->payload(), true);
        }

        try {
            // Validate the request path and query against the OpenAPI schema
            $schema->getPathDefinition($path, $method);
            // Returns a SwaggerRequestBody instance
            $bodyRequestDef = $schema->getRequestParameters($path, $method);
            $bodyRequestDef->match($requestBody);

        } catch (Exception $ex) {
            throw new Error400Exception(explode("\n", $ex->getMessage())[0]);
        }

        $requestBody = empty($requestBody) ? [] : $requestBody;

        if ($allowNull) {
            return $requestBody;
        }

        return Serialize::from($requestBody)->withDoNotParseNullValues()->toArray();
    }
}
