<?php

namespace ECGM\Util;


use ECGM\Exceptions\InvalidArgumentException;

/**
 * Class MiscFunctions
 * @package ECGM\Util
 */
class MiscFunctions
{
    /**
     * Merges two associative arrays by adding values with same key
     *
     * @param array $arr1
     * @param array $arr2
     * @throws InvalidArgumentException
     * @return array
     */
    public static function mergeAssociativeArrays($arr1, $arr2)
    {
        if (!self::isAssoc($arr1)) {
            throw new InvalidArgumentException("First array is not associative.");
        }
        if (!self::isAssoc($arr2)) {
            throw new InvalidArgumentException("Second array is not associative.");
        }


        $merged = array();

        foreach ($arr1 as $key => $value) {
            $merged[$key] = array_merge($arr1[$key], $arr2[$key]);
        }

        foreach ($arr2 as $key => $value) {
            $merged[$key] = array_merge($arr1[$key], $arr2[$key]);
        }

        return $merged;
    }

    /**
     * @param array $arr
     * @return bool
     */
    public static function isAssoc(array $arr)
    {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}