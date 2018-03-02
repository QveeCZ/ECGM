<?php

namespace ECGM\Util;


use ECGM\Exceptions\InvalidArgumentException;
use ECGM\Exceptions\UndefinedException;

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
     * @param array $v1
     * @param array $v2
     * @return float
     * @throws InvalidArgumentException
     */
    public static function euclideanDistance($v1, $v2)
    {

        $dimension = count($v1);

        if ($dimension != count($v2)) {
            throw new InvalidArgumentException("Vector v1 size " . $dimension . " is not equal to vector v2 size " . count($v2));
        }

        $distance = 0;
        for ($n = 0; $n < $dimension; $n++) {
            $distance += pow($v1[$n] - $v2[$n], 2);
        }

        return $distance;
    }

    /**
     * @param array $v1
     * @param array $v2
     * @return float
     * @throws InvalidArgumentException
     */
    public static function euclideanDistancePrecise($v1, $v2)
    {

        $dimension = count($v1);

        if ($dimension != count($v2)) {
            throw new InvalidArgumentException("Vector v1 size " . $dimension . " is not equal to vector v2 size " . count($v2));
        }

        $distance = 0;
        for ($n = 0; $n < $dimension; $n++) {
            $distance += pow($v1[$n] - $v2[$n], 2);
        }

        return sqrt($distance);
    }

    public static function manhattanDistance($v1, $v2)
    {
        $dimension = count($v1);

        if ($dimension != count($v2)) {
            throw new InvalidArgumentException("Vector v1 size " . $dimension . " is not equal to vector v2 size " . count($v2));
        }

        $sum = 0;
        for ($i = 0; $i < $dimension; $i++) {
            $sum += abs($v1[$i] - $v2[$i]);
        }

        return $sum;
    }
}