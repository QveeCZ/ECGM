<?php
namespace ECGM\Int;


use ECGM\Model\BaseArray;

interface GroupingValidationInterface
{
    /**
     * @param BaseArray $groups
     * @return float|int
     */
    public function getGroupingScore(BaseArray $groups);
}