<?php

namespace RestReferenceArchitecture\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class RequireRole extends \ByJG\RestServer\Attributes\RequireRole
{
    public function __construct(string $role)
    {
        parent::__construct($role, 'jwt.data', "role");
    }
}
