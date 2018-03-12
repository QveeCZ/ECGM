<?php

namespace ECGM\Int;


interface DistanceFuncInterface
{

    /**
     * @param array $v1
     * @param array $v2
     * @return float
     */
    public function distanceQuick($v1, $v2);

    /**
     * @param array $v1
     * @param array $v2
     * @return float
     */
    public function distancePrecise($v1, $v2);

}