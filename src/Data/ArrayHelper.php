<?php

namespace B24\Devtools\Data;

class ArrayHelper
{
    public static function arrayToCamelCase(array $array): array
    {
        $arResult = [];
        foreach ($array as $key => $value) {
            $key = StringHelper::stringToCamelCase($key);
            if (is_array($value)) {
                $value = self::arrayToCamelCase($value);
            }
            $arResult[$key] = $value;
        }
        return $arResult;
    }

    public static function arrayToUnderScore(array $array): array
    {
        $arResult = [];
        foreach ($array as $key => $value) {
            $key = StringHelper::stringToUnderscore($key);
            if (is_array($value)) {
                $value = self::arrayToUnderScore($value);
            }
            $arResult[$key] = $value;
        }
        return $arResult;
    }
}