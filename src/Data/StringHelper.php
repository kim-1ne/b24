<?php

namespace B24\Devtools\Data;

class StringHelper
{
    public static function stringToCamelCase(string $str): string
    {
        if (intval($str)) {
            return $str;
        }
        return lcfirst(str_replace('_', '', ucwords(mb_strtolower($str), '_')));
    }

    public static function stringToUnderscore(string $str): string
    {
        return strtoupper(preg_replace('/(?<!^)[A-Z]/', '_$0', $str));
    }
}