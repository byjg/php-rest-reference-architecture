<?php

namespace RestReferenceArchitecture\Trait;

use ByJG\MicroOrm\Attributes\FieldAttribute;
use ByJG\MicroOrm\Trait\CreatedAt;
use OpenApi\Attributes as OA;

/**
 * `composer run openapi` will throw this error if you are NOT USING IT
 *
 * ❯ composer run openapi
 * > Builder\Scripts::genOpenApiDocs
 * Script Builder\Scripts::genOpenApiDocs handling the openapi event terminated with an exception
 *
 * In DefaultLogger.php line 31:
 *
 * Unexpected @OA\Property(), expected to be inside @OA\AdditionalProperties, @OA\Schema, @OA\JsonContent, @OA\XmlContent, @OA\Property, @OA\Items in /workdir/src/Trait/OaCreatedAt.php on line 10
 *
 *
 * run-script [--timeout TIMEOUT] [--dev] [--no-dev] [-l|--list] [--] [<script> [<args>...]]
 */
trait OaCreatedAt
{
    use CreatedAt;

    #[OA\Property(type: "string", format: "date-time", nullable: true)]
    #[FieldAttribute(fieldName: "created_at", syncWithDb: false)]
    protected string|null $createdAt = null;
}