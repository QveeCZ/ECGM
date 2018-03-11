<?php

namespace ECGM;


use ECGM\Model\BaseArray;
use ECGM\Model\CurrentProduct;

abstract class MainInterface
{
    /**
     * Return all customers, that should be included in strategy.
     * Has to return BaseArray with ECGM\Model\Customer requiredClass
     *
     * @return BaseArray
     */
    public abstract function getCustomers();

    /**
     * Return all products that are currently in sale and should be included in strategy.
     * Has to return BaseArray ECGM\Model\CurrentProduct requiredClass
     *
     * @return BaseArray
     */
    public abstract function getProducts();

    /**
     * Set desired Product Payoff Coefficient to product
     *
     * @param CurrentProduct $product
     * @return CurrentProduct
     */
    public abstract function setProductPPC(CurrentProduct $product);
}