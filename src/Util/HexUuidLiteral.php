<?php

namespace RestTemplate\Util;

use ByJG\MicroOrm\Literal;

class HexUuidLiteral extends Literal
{
    public function __construct($value)
    {
        parent::__construct("X'" . str_replace("-", "", $value) . "'");
    }

    public static function getUuidFromLiteral($literal)
    {
        $value = $literal->__toString();
        return substr($value, 2, 8) . "-" . substr($value, 10, 4) . "-" . substr($value, 14, 4) . "-" . substr($value, 18, 4) . "-" . substr($value, 22, 12);
    }
}
