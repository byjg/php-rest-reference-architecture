<?php

namespace RestReferenceArchitecture\Util;

use ByJG\ApiTools\Base\Schema;
use ByJG\Config\Exception\ConfigException;
use ByJG\Config\Exception\ConfigNotFoundException;
use ByJG\Config\Exception\DependencyInjectionException;
use ByJG\Config\Exception\KeyNotFoundException;
use ByJG\RestServer\Exception\Error400Exception;
use ByJG\RestServer\HttpRequest;
use ByJG\Serializer\Serialize;
use ByJG\XmlUtil\Exception\FileException;
use ByJG\XmlUtil\Exception\XmlUtilException;
use ByJG\XmlUtil\XmlDocument;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;
use RestReferenceArchitecture\Psr11;

class OpenApiContext
{
    /**
     * @param HttpRequest $request
     * @param bool $allowNull
     * @return array|bool|XmlDocument|mixed|string
     * @throws ConfigException
     * @throws ConfigNotFoundException
     * @throws DependencyInjectionException
     * @throws Error400Exception
     * @throws InvalidArgumentException
     * @throws KeyNotFoundException
     * @throws ReflectionException
     * @throws XmlUtilException
     * @throws FileException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function validateRequest(HttpRequest $request, bool $allowNull = false)
    {
        $schema = Psr11::get(Schema::class);

        $path = $request->getRequestPath();
        $method = $request->server('REQUEST_METHOD');
        $contentType = strtolower($request->getHeader('Content-Type') ?? '');

        // Validate the request body (payload)
        if (str_contains($contentType, 'multipart/')) {
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

        if (!$allowNull) {
            $requestBody = Serialize::from($requestBody)->withDoNotParseNullValues()->toArray();
        }

        // Convert payload according to content-type
        if (str_contains($contentType, 'xml')) {
            // Return XmlDocument for XML content
            return new XmlDocument($request->payload());
        } else {
            // For JSON and other content types, return the array
            return $requestBody;
        }
    }
}
