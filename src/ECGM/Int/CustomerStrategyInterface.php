<?php

namespace ECGM\Int;

use ECGM\Exceptions\InvalidArgumentException;
use ECGM\Model\BaseArray;
use ECGM\Model\CurrentProduct;
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

    /**
     * @param CurrentProduct $currentProduct
     * @param $productPurchasesList
     * @return float|int
     * @throws InvalidArgumentException
     */
    public function getProductStrategy(CurrentProduct $currentProduct, $productPurchasesList);

    /**
     * @param Customer $customer
     * @param BaseArray $currentProducts
     * @param array $currentOrderProducts
     * @return array
     * @throws InvalidArgumentException
     */
    public function getPurchasedProducts(Customer $customer, BaseArray $currentProducts, $currentOrderProducts = array());

}