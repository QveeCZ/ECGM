<?php
namespace ECGM\Int;


use ECGM\Model\BaseArray;

interface DealerStrategyInterface
{

    /**
     * @param BaseArray $products
     * @return array
     */
    public function getDealerStrategy(BaseArray $products);
}