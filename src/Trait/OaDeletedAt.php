<?php

namespace RestReferenceArchitecture\Trait;

use ByJG\MicroOrm\Trait\DeletedAt;

/**
 * Wrapper trait for DeletedAt that includes OpenAPI documentation
 */
trait OaDeletedAt
{
    use DeletedAt;
}
