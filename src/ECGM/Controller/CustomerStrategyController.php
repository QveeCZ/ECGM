<?php

namespace ECGM\Controller;


use ECGM\Exceptions\InvalidArgumentException;
use ECGM\Int\CustomerStrategyInterface;
use ECGM\Model\BaseArray;
use ECGM\Model\CurrentProduct;
use ECGM\Model\Customer;
use ECGM\Model\CustomerGroup;
use ECGM\Model\Order;
use ECGM\Model\OrderProduct;
use ECGM\Model\ProductComplement;
use ECGM\Model\StrategyProduct;

class CustomerStrategyController implements CustomerStrategyInterface
{

    protected $coefficient;
    protected $customerPurchasedProducts;

    /**
     * CustomerStrategyController constructor.
     * @param $coefficient
     * @throws InvalidArgumentException
     */
    public function __construct($coefficient)
    {
        if (!is_numeric($coefficient) || $coefficient < 1) {
            throw  new InvalidArgumentException("Multiplication coefficient has to be numeric and > 1, bud is $coefficient.");
        }

        $this->coefficient = $coefficient;
    }

    /**
     * @param Customer $customer
     * @param BaseArray $currentProducts
     * @param Order|null $currentOrder
     * @return array
     * @throws InvalidArgumentException
     */
    public function getCustomerStrategy(Customer $customer, BaseArray $currentProducts, Order $currentOrder = null)
    {
        $currentProducts = new BaseArray($currentProducts, CurrentProduct::class);

        return $this->guessStrategy($customer, $currentProducts, $currentOrder);
    }

    /**
     * @param Customer $customer
     * @param BaseArray $currentProducts
     * @param Order|null $currentOrder
     * @return array
     * @throws InvalidArgumentException
     */
    protected function guessStrategy(Customer $customer, BaseArray $currentProducts, Order $currentOrder = null)
    {
        $currentProducts = new BaseArray($currentProducts, CurrentProduct::class);

        $currentOrderProducts = array();

        if ($currentOrder && $currentOrder->getProducts()->size() > 0) {
            /**
             * @var OrderProduct $product
             */
            foreach ($currentOrder->getProducts() as $product) {
                $currentOrderProducts[$product->getId()] = $product->getAmount();
            }
        }

        $purchasedProducts = $this->getPurchasedProducts($customer, $currentProducts, $currentOrderProducts);


        $preStrategy = array();
        $preStrategySum = 0;

        /**
         * @var CurrentProduct $currentProduct
         */
        foreach ($currentProducts as $currentProduct) {
            $preStrategy[$currentProduct->getId()] = $this->getProductStrategy($currentProduct, $purchasedProducts[$currentProduct->getId()]);
            $preStrategySum += $preStrategy[$currentProduct->getId()];
        }

        $strategy = array();

        /**
         * @var CurrentProduct $currentProduct
         */
        foreach ($currentProducts as $currentProduct) {
            $strategy[$currentProduct->getId()] = round($preStrategy[$currentProduct->getId()] / $preStrategySum, 3);
        }

        return $strategy;
    }

    /**
     * @param Customer $customer
     * @param BaseArray $currentProducts
     * @param array $currentOrderProducts
     * @return array
     * @throws InvalidArgumentException
     */
    public function getPurchasedProducts(Customer $customer, BaseArray $currentProducts, $currentOrderProducts = array())
    {
        $currentProducts = new BaseArray($currentProducts, CurrentProduct::class);

        if (!$this->customerPurchasedProducts) {
            $this->customerPurchasedProducts = $this->getCustomerOrderProductAmounts($customer->getHistory(), $currentOrderProducts);
        }

        $customerPurchasedProducts = $this->customerPurchasedProducts;

        $groupPurchasedProducts = $this->getCustomerGroupOrdersProductAmounts($this->getGroupHistory($customer->getGroup()), $currentOrderProducts);

        $currentProductAmounts = array();

        $currentOrderProducts = array();

        /**
         * @var CurrentProduct $currentProduct
         */
        foreach ($currentProducts as $currentProduct) {

            //if product is already in cart skip
            if (array_key_exists($currentProduct->getId(), $currentOrderProducts)) {
                continue;
            }

            $currId = $currentProduct->getId();

            $customerPurchasedProduct = (array_key_exists($currId, $customerPurchasedProducts)) ? $customerPurchasedProducts[$currId] : array();
            $groupPurchasedProduct = (array_key_exists($currId, $groupPurchasedProducts)) ? $groupPurchasedProducts[$currId] : array();


            $currentProductAmounts[$currId] = array_merge($customerPurchasedProduct, $groupPurchasedProduct);
        }

        return $currentProductAmounts;
    }

    /**
     * @param BaseArray $history
     * @param array $currentOrderProducts
     * @return array
     * @throws InvalidArgumentException
     */
    protected function getCustomerOrderProductAmounts(BaseArray $history, $currentOrderProducts = array())
    {
        $history = new BaseArray($history, Order::class);

        $orderProducts = array();

        /**
         * @var Order $order
         */
        foreach ($history as $order) {

            $adjustedProducts = $this->adjustCustomerOrder($order, $currentOrderProducts);

            foreach ($adjustedProducts as $key => $val) {

                if (!array_key_exists($key, $orderProducts)) {
                    $orderProducts[$key] = array();
                }

                $orderProducts[$key][$order->getId()] = $val;
            }
        }


        return $orderProducts;
    }

    /**
     * @param Order $order
     * @param array $currentOrderProducts
     * @return StrategyProduct[]
     */
    protected function adjustCustomerOrder(Order $order, $currentOrderProducts = array())
    {
        $now = time();
        $orderDate = $order->getOrderDate()->getTimestamp();
        $dateDiff = ($now - $orderDate);

        /**
         * @var StrategyProduct[] $orderProducts
         */
        $orderProducts = array();

        $ps = 0;

        /**
         * @var OrderProduct $product
         */
        foreach ($order->getProducts() as $product) {

            if (!array_key_exists($product->getId(), $orderProducts)) {
                $orderProducts[$product->getId()] = new StrategyProduct($product->getId(), $order->getId());
            }

            if (($dateDiff - ($product->getExpiration() * $product->getExpirationDateType())) < 0) {

                $orderProducts[$product->getId()]->setAmount(($product->getAmount() * $this->coefficient) + $orderProducts[$product->getId()]->getAmount());
                $orderProducts[$product->getId()]->setPrice($product->getPrice());

            } else {

                $orderProducts[$product->getId()]->setAmount($product->getAmount() + $orderProducts[$product->getId()]->getAmount());
                $orderProducts[$product->getId()]->setPrice($product->getPrice());

                foreach ($this->getAdjustedComplements($product) as $key => $complement) {

                    if (!array_key_exists($key, $orderProducts)) {
                        $orderProducts[$key] = new StrategyProduct($key, $order->getId());
                    }

                    $orderProducts[$key]->setAmount($complement->getAmount() + $orderProducts[$key]->getAmount());

                    if (!$orderProducts[$key]->getPrice()) {
                        $orderProducts[$key]->setPrice($complement->getPrice());
                    }
                }

            }

            if (array_key_exists($product->getId(), $currentOrderProducts)) {
                $ps += ($currentOrderProducts[$product->getId()] < $product->getAmount()) ? $currentOrderProducts[$product->getId()] : $product->getAmount();
            }

        }

        $oc = max(1, $ps * $this->coefficient);

        foreach ($orderProducts as $key => $orderProduct) {
            $orderProduct->setAmount($orderProduct->getAmount() * $oc);
        }


        return $orderProducts;
    }

    /**
     * @param OrderProduct $product
     * @return StrategyProduct[]
     */
    protected function getAdjustedComplements(OrderProduct $product)
    {

        $ret = array();

        /**
         * @var ProductComplement $complement
         */
        foreach ($product->getComplements() as $complement) {

            $adjustedAmount = $product->getAmount() * max(1, $this->coefficient / 2);
            $ret[$complement->getId()] = new StrategyProduct($complement->getId(), $product->getOrderId(), $complement->getPrice(), $adjustedAmount);
        }

        return $ret;
    }

    /**
     * @param BaseArray $history
     * @param array $currentOrderProducts
     * @return array
     * @throws InvalidArgumentException
     */
    protected function getCustomerGroupOrdersProductAmounts(BaseArray $history, $currentOrderProducts = array())
    {

        $history = new BaseArray($history, Order::class);

        $orderProducts = array();

        /**
         * @var Order $order
         */
        foreach ($history as $order) {
            $orderProducts = array_replace_recursive($orderProducts, $this->getCustomerGroupOrderProductAmounts($order, $currentOrderProducts));
        }

        return $orderProducts;
    }

    /**
     * @param Order $order
     * @param array $currentOrderProducts
     * @return array
     */
    protected function getCustomerGroupOrderProductAmounts(Order $order, $currentOrderProducts = array())
    {

        $orderProducts = array();
        $ps = 0;


        /**
         * @var OrderProduct $product
         */
        foreach ($order->getProducts() as $product) {

            if (!array_key_exists($product->getId(), $orderProducts)) {
                $orderProducts[$product->getId()] = array();
            }

            $orderProducts[$product->getId()][$order->getId()] = new StrategyProduct($product->getId(), $order->getId(), $product->getPrice(), $product->getAmount());


            if (array_key_exists($product->getId(), $currentOrderProducts)) {
                $ps += ($currentOrderProducts[$product->getId()] < $product->getAmount()) ? $currentOrderProducts[$product->getId()] : $product->getAmount();
            }

        }

        $oc = max(1, $ps * $this->coefficient);


        /**
         * @var StrategyProduct[] $product
         */
        foreach ($orderProducts as $key => $product) {
            $product[$order->getId()]->setAmount($product[$order->getId()]->getAmount() * $oc);
        }

        return $orderProducts;
    }

    /**
     * @param CustomerGroup|null $group
     * @return BaseArray
     * @throws InvalidArgumentException
     */
    protected function getGroupHistory(CustomerGroup $group = null)
    {
        $history = new BaseArray(null, Order::class);

        if (is_null($group)) {
            return $history;
        }

        /**
         * @var Customer $customer
         */
        foreach ($group->getCustomers() as $customer) {
            $history->merge($customer->getHistory());
        }

        return $history;
    }

    /**
     * @param CurrentProduct $currentProduct
     * @param $productPurchasesList
     * @return float|int
     * @throws InvalidArgumentException
     */
    public function getProductStrategy(CurrentProduct $currentProduct, $productPurchasesList)
    {

        $productPurchases = new BaseArray(null, StrategyProduct::class);
        $productPurchases->setList($productPurchasesList);

        $weights = $this->getOrderProductWeights($currentProduct, $productPurchases);
        $strategy = 0;

        /**
         * @var StrategyProduct $productPurchase
         */
        foreach ($productPurchases as $productPurchase) {
            $strategy += $productPurchase->getAmount() * $weights[$productPurchase->getOrderId()];
        }


        return $strategy;
    }

    /**
     * @param CurrentProduct $currProduct
     * @param BaseArray $products
     * @return array
     * @throws InvalidArgumentException
     */
    protected function getOrderProductWeights(CurrentProduct $currProduct, BaseArray $products)
    {

        $products = new BaseArray($products, StrategyProduct::class);

        $norm = $this->getProductNorm($currProduct, $products);

        $weights = array();

        $currProductNormalized = $currProduct->getDiscountedPrice() / $norm;

        /**
         * @var StrategyProduct $strategyProduct
         */
        foreach ($products as $strategyProduct) {

            if ($currProduct->getId() != $strategyProduct->getId()) {
                throw new InvalidArgumentException("Current product id " . $currProduct->getId() . " is different from order product id " . $strategyProduct->getId() . ".");
            }

            $orderProductNormalized = $strategyProduct->getDiscountedPrice() / $norm;

            $weights[$strategyProduct->getOrderId()] = 1 / (1 + ($currProductNormalized - $orderProductNormalized));

        }

        return $weights;
    }


    /**
     * @param CurrentProduct $currProduct
     * @param BaseArray $products
     * @return float
     * @throws InvalidArgumentException
     */
    protected function getProductNorm(CurrentProduct $currProduct, BaseArray $products)
    {
        $products = new BaseArray($products, StrategyProduct::class);

        $normPow = pow($currProduct->getPrice(), 2);

        /**
         * @var OrderProduct $orderProduct
         */
        foreach ($products as $orderProduct) {
            if ($currProduct->getId() != $orderProduct->getId()) {
                throw new InvalidArgumentException("Current product id " . $currProduct->getId() . " is different from order product id " . $orderProduct->getId() . ".");
            }

            $normPow += pow($orderProduct->getPrice(), 2);

        }

        $norm = sqrt($normPow);

        return $norm;
    }

}