<?php

namespace RestTemplate\Util;

use ByJG\MicroOrm\Literal;
use InvalidArgumentException;

class HexUuidLiteral extends Literal
{
    public function __construct($value)
    {
        $value = HexUuidLiteral::getFormattedUuid($value);
        parent::__construct("X'" . preg_replace('/[^0-9A-Fa-f]/', '', $value) . "'");
    }

    public static function getUuidFromLiteral($literal)
    {
        return self::getFormattedUuid($literal);
    }

    public static function getFormattedUuid($item, $throwErrorIfInvalid = true)
    {
        if ($item instanceof Literal) {
            $item = preg_replace("/^X'(.*)'$/", "$1", $item->__toString());
        }

        if (preg_match("/^X'(.*)'$/", $item, $matches)) {
            $item = $matches[1];
        }

        if (is_string($item) && !ctype_print($item) && strlen($item) === 16) {
            $item = bin2hex($item);
        }

        if (preg_match("/^\w{8}-?\w{4}-?\w{4}-?\w{4}-?\w{12}$/", $item)) {
            $item = preg_replace("/^(\w{8})-?(\w{4})-?(\w{4})-?(\w{4})-?(\w{12})$/", "$1-$2-$3-$4-$5", $item);
        } else if ($throwErrorIfInvalid) {
            throw new InvalidArgumentException("Invalid UUID format");
        } else {
            return $item;
        }

        return strtoupper($item);
    }
}
