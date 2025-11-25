<?php

namespace RestReferenceArchitecture\Trait;

use ByJG\MicroOrm\Attributes\FieldAttribute;
use ByJG\MicroOrm\Trait\UpdatedAt;
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
 * Unexpected @OA\Property(), expected to be inside @OA\AdditionalProperties, @OA\Schema, @OA\JsonContent, @OA\XmlContent, @OA\Property, @OA\Items in /workdir/src/Trait/OaUpdatedAt.php on line 10
 *
 *
 * run-script [--timeout TIMEOUT] [--dev] [--no-dev] [-l|--list] [--] [<script> [<args>...]]
 */
trait OaUpdatedAt
{
    use UpdatedAt;

    #[OA\Property(type: "string", format: "date-time", nullable: true)]
    #[FieldAttribute(fieldName: "updated_at", syncWithDb: false)]
    protected string|null $updatedAt = null;
}