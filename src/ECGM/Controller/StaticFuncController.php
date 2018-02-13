<?php

namespace ECGM\Controller;


use ECGM\Exceptions\UndefinedException;

class StaticFuncController
{
    public static function arrayMedian($array)
    {
        $count = count($array);
        if ($count == 0) {
            throw new UndefinedException('Median of an empty array cannot be defined.');
        }
        sort($array, SORT_NUMERIC);

        $middle_index = floor(($count - 1) / 2);

        if ($count % 2 == 0) {
            $median = ($array[$middle_index] + $array[$middle_index + 1]) / 2;
        } else {
            $median = $array[$middle_index];
        }

        return $median;
    }
}