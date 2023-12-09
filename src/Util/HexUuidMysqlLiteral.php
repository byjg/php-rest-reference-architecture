<?php

namespace RestReferenceArchitecture\Util;

use ByJG\MicroOrm\Literal;

class HexUuidMysqlLiteral extends Literal
{
    public function __construct($value = null)
    {
        parent::__construct("unhex(replace(uuid(),'-',''))");
    }
}
