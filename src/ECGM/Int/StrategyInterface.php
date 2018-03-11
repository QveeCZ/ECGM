<?php
namespace ECGM\Int;


use ECGM\Exceptions\InvalidArgumentException;
use ECGM\Model\BaseArray;
use ECGM\Model\Customer;
use ECGM\Model\Order;

interface StrategyInterface
{
    /**
     * @param Customer $customer
     * @param BaseArray $currentProducts
     * @param Order|null $currentOrder
     * @return array|bool
     * @throws InvalidArgumentException
     */
    public function getIdealStrategy(Customer $customer, BaseArray $currentProducts, Order $currentOrder = null);
}