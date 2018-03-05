<?php
namespace ECGM\Int;

use ECGM\Model\BaseArray;
use ECGM\Model\Customer;
use ECGM\Model\Order;

interface CustomerStrategyInterface
{

    /**
     * @param Customer $customer
     * @param BaseArray $currentProducts
     * @param Order|null $currentOrder
     * @return array
     */
    public function getCustomerStrategy(Customer $customer, BaseArray $currentProducts, Order $currentOrder = null);
}