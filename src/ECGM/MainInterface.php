<?php

namespace ECGM;


use ECGM\Model\AssociativeBaseArray;
use ECGM\Model\BaseArray;
use ECGM\Model\CurrentProduct;

interface MainInterface
{

    /**
     * Return all customers, that should be included in strategy.
     * Has to return BaseArray with ECGM\Model\Customer requiredClass
     *
     * @return BaseArray
     */
    public function getCustomers();

    /**
     * Return all customers, that have no group and should be included in strategy.
     * Has to return BaseArray with ECGM\Model\Customer requiredClass
     *
     * @return BaseArray
     */
    public function getUngroupedCustomers();

    /**
     * Return all customer groups, that should be included in strategy.
     * Has to return BaseArray with ECGM\Model\CustomerGroup requiredClass
     *
     * @return BaseArray
     */
    public function getCustomerGroups();

    /**
     * Return all products that are currently in sale and should be included in strategy.
     * Has to return BaseArray ECGM\Model\CurrentProduct requiredClass
     *
     * @return AssociativeBaseArray
     */
    public function getProducts();

    /**
     * Set desired Product Payoff Coefficient to product
     *
     * @param CurrentProduct $product
     * @return CurrentProduct
     */
    public function setProductPPC(CurrentProduct $product);
}