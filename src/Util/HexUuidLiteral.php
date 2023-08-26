<?php

namespace RestTemplate\Util;

use ByJG\MicroOrm\Literal;
use InvalidArgumentException;

class HexUuidLiteral extends Literal
{
    public function __construct($value)
    {
        if (strlen($value) === 16) {
            $value = bin2hex($value);
        }
        parent::__construct("X'" . preg_replace('/[^0-9A-Fa-f]/', '', $value) . "'");
    }

    public static function getUuidFromLiteral($literal)
    {
        return self::getFormattedUuid($literal);
    }

    public static function getFormattedUuid($item)
    {
        if ($item instanceof Literal) {
            $item = preg_replace("/^X'(.*)'$/", "$1", $item->__toString());
        }

        if (preg_match("/^X'(.*)'$/", $item, $matches)) {
            $item = $matches[1];
        }

        if (preg_match("/^\w{8}-?\w{4}-?\w{4}-?\w{4}-?\w{12}$/", $item)) {
            $item = preg_replace("/^(\w{8})-?(\w{4})-?(\w{4})-?(\w{4})-?(\w{12})$/", "$1-$2-$3-$4-$5", $item);
        } else {
            throw new InvalidArgumentException("Invalid UUID format");
        }

        return $item;
    }
}
