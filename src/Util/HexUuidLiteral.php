<?php

namespace RestTemplate\Util;

use ByJG\MicroOrm\Literal;

class HexUuidLiteral extends Literal
{
    public function __construct($value)
    {
        parent::__construct("X'" . str_replace("-", "", $value) . "'");
    }
}
