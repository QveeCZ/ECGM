<?php

namespace ECGM\Util;


use ECGM\Exceptions\InvalidArgumentException;
use ECGM\Int\DistanceFuncInterface;

class DistanceFunctions implements DistanceFuncInterface
{
    /**
     * @param array $v1
     * @param array $v2
     * @return float|int
     * @throws InvalidArgumentException
     */
    public function distanceQuick($v1, $v2)
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

    /**
     * @param array $v1
     * @param array $v2
     * @return float
     * @throws InvalidArgumentException
     */
    public function distancePrecise($v1, $v2)
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

}