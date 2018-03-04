<?php
namespace ECGM\Int;

use ECGM\Model\BaseArray;
use ECGM\Model\Customer;

interface CustomerStrategyInterface
{

    /**
     * @param Customer $customer
     * @param BaseArray $currentProducts
     * @return array
     */
    public function getCustomerStrategy(Customer $customer, BaseArray $currentProducts);
}