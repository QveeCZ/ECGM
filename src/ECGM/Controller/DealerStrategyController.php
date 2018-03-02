<?php

namespace ECGM\Controller;


use ECGM\Model\BaseArray;
use ECGM\Model\CurrentProduct;

class DealerStrategyController
{

    /**
     * @param BaseArray $products
     * @return array
     */
    public function getDealerStrategy(BaseArray $products)
    {
        $products = new BaseArray($products, CurrentProduct::class);
        $strategy = array();
        $ppcSum = $this->getPPCSum($products);

        /**
         * @var CurrentProduct $product
         */
        foreach ($products as $product) {
            $strategy[$product->getId()] = $this->getStrategy($product, $ppcSum);
        }

        return $strategy;
    }

    /**
     * @param CurrentProduct $product
     * @param integer $ppcSum
     * @return float
     */
    protected function getStrategy(CurrentProduct $product, $ppcSum)
    {
        return round($product->getPpc() / $ppcSum, 3);
    }

    /**
     * @param BaseArray $products
     * @return int|mixed
     */
    protected function getPPCSum(BaseArray $products)
    {
        $products = new BaseArray($products, CurrentProduct::class);

        $ppcSum = 0;

        /**
         * @var CurrentProduct $product
         */
        foreach ($products as $product) {
            $ppcSum += $product->getPpc();
        }

        return $ppcSum;
    }

}