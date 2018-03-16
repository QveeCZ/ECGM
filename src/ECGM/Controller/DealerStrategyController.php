<?php

namespace ECGM\Controller;


use ECGM\Int\DealerStrategyInterface;
use ECGM\Model\BaseArray;
use ECGM\Model\CurrentProduct;

/**
 * Class DealerStrategyController
 * @package ECGM\Controller
 */
class DealerStrategyController implements DealerStrategyInterface
{

    /**
     * @param BaseArray $products
     * @return array
     * @throws \ECGM\Exceptions\InvalidArgumentException
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
     * @param BaseArray $products
     * @return int|mixed
     * @throws \ECGM\Exceptions\InvalidArgumentException
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

    /**
     * @param CurrentProduct $product
     * @param int $ppcSum
     * @return float
     */
    protected function getStrategy(CurrentProduct $product, $ppcSum)
    {
        return round($product->getPpc() / $ppcSum, 3);
    }

}