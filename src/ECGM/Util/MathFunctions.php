<?php

namespace ECGM\Util;


use ECGM\Exceptions\InvalidArgumentException;
use ECGM\Exceptions\UndefinedException;
use ECGM\Model\BaseArray;

class MathFunctions
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

    /**
     * @param BaseArray $v1
     * @param BaseArray $v2
     * @return float
     * @throws InvalidArgumentException
     */
    public static function euclideanDistance(BaseArray $v1, BaseArray $v2)
    {
        if ($v1->size() != $v2->size()) {
            throw new InvalidArgumentException("Vector v1 size " . $v1->size() . " is not equal to vector v2 size " . $v2->size());
        }

        $distance = 0;
        for ($n = 0; $n < $v1->size(); $n++) {
            $difference = $v1->getObj($n) - $v2->getObj($n);
            $distance += $difference ^ 2;
        }

        return sqrt($distance);
    }
}