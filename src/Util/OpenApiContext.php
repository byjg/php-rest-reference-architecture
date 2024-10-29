<?php

namespace RestReferenceArchitecture\Util;

use ByJG\ApiTools\Base\Schema;
use ByJG\RestServer\Exception\Error400Exception;
use ByJG\RestServer\HttpRequest;
use ByJG\Serializer\Serialize;
use Exception;
use RestReferenceArchitecture\Psr11;

class OpenApiContext
{
    public static function validateRequest(HttpRequest $request)
    {
        $schema = Psr11::container()->get(Schema::class);

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

            if (!empty($requestBody)) {
                // Returns a SwaggerRequestBody instance
                $bodyRequestDef = $schema->getRequestParameters($path, $method);
                $bodyRequestDef->match($requestBody);
            } else {
                return [];
            }
        } catch (Exception $ex) {
            throw new Error400Exception(explode("\n", $ex->getMessage())[0]);
        }

        return Serialize::from($requestBody)->withDoNotParseNullValues()->toArray();
    }
}
