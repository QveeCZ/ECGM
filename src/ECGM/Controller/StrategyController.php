<?php

namespace ECGM\Controller;


use ECGM\Enum\StrategyType;
use ECGM\Exceptions\InvalidArgumentException;
use ECGM\Exceptions\LogicalException;
use ECGM\Int\CustomerStrategyInterface;
use ECGM\Int\DealerStrategyInterface;
use ECGM\Int\StrategyInterface;
use ECGM\MainInterface;
use ECGM\Model\AssociativeBaseArray;
use ECGM\Model\CurrentProduct;
use ECGM\Model\Customer;
use ECGM\Model\Order;

class StrategyController implements StrategyInterface
{

    private $customerStrategyController;
    private $dealerStrategyController;
    private $strategyType;
    private $mainInterface;

    /**
     * StrategyController constructor.
     * @param $coefficient
     * @param MainInterface $mainInterface
     * @param int $strategyType
     * @throws InvalidArgumentException
     * @throws \ReflectionException
     */
    public function __construct($coefficient, MainInterface $mainInterface, $strategyType = StrategyType::CONSERVATIVE)
    {
        if (!StrategyType::isValidValue($strategyType)) {
            throw new InvalidArgumentException("Strategy type is $strategyType, but available values are " . json_encode(StrategyType::getConstants()) . ".");
        }

        $this->mainInterface = $mainInterface;
        $this->customerStrategyController = new CustomerStrategyController($coefficient);
        $this->dealerStrategyController = new DealerStrategyController();
        $this->strategyType = $strategyType;
    }

    /**
     * @return CustomerStrategyController
     */
    public function getCustomerStrategyController()
    {
        return $this->customerStrategyController;
    }

    /**
     * @param CustomerStrategyInterface $customerStrategyController
     */
    public function setCustomerStrategyController(CustomerStrategyInterface $customerStrategyController)
    {
        $this->customerStrategyController = $customerStrategyController;
    }

    /**
     * @return DealerStrategyController
     */
    public function getDealerStrategyController()
    {
        return $this->dealerStrategyController;
    }

    /**
     * @param DealerStrategyInterface $dealerStrategyController
     */
    public function setDealerStrategyController(DealerStrategyInterface $dealerStrategyController)
    {
        $this->dealerStrategyController = $dealerStrategyController;
    }

    /**
     * @param Customer $customer
     * @param AssociativeBaseArray $currentProducts
     * @param Order|null $currentOrder
     * @return AssociativeBaseArray
     * @throws InvalidArgumentException
     * @throws LogicalException
     */
    public function getIdealStrategy(Customer $customer, AssociativeBaseArray $currentProducts, Order $currentOrder = null)
    {

        if ($currentProducts->size() < 2) {
            return $currentProducts;
        }

        switch ($this->strategyType) {
            case StrategyType::PASSIVE:
                return $this->getPassiveStrategy($customer, $currentProducts, $currentOrder);
            case StrategyType::AGGRESSIVE:
                return $this->getAggressiveStrategy($customer, $currentProducts, $currentOrder);
            default:
                throw new LogicalException("Invalid strategy type " . $this->strategyType . ".");
        }
    }

    /**
     * @param Customer $customer
     * @param AssociativeBaseArray $currentProducts
     * @param Order|null $currentOrder
     * @return AssociativeBaseArray
     * @throws InvalidArgumentException
     */
    protected function getPassiveStrategy(Customer $customer, AssociativeBaseArray $currentProducts, Order $currentOrder = null)
    {
        $dealerStrategy = $this->dealerStrategyController->getDealerStrategy($currentProducts);
        $customerStrategy = $this->customerStrategyController->getCustomerStrategy($customer, $currentProducts, $currentOrder);


        $sortedProducts = new AssociativeBaseArray(null, CurrentProduct::class);

        $idealStrategy = $this->getPassiveIdealStrategy($dealerStrategy, $customerStrategy);

        foreach ($idealStrategy as $key => $value) {
            $sortedProducts->add($currentProducts->getObj($key));
        }

        return $sortedProducts;
    }

    /**
     * @param array $dealerStrategy
     * @param array $customerStrategy
     * @return array
     */
    protected function getPassiveIdealStrategy($dealerStrategy, $customerStrategy)
    {

        $idealStrategy = array();

        foreach ($dealerStrategy as $product => $strategy) {
            $idealStrategy[$product] = $strategy * $customerStrategy[$product];
        }

        arsort($idealStrategy);

        return $idealStrategy;
    }

    /**
     * @param Customer $customer
     * @param AssociativeBaseArray $currentProducts
     * @param Order|null $currentOrder
     * @return AssociativeBaseArray
     * @throws InvalidArgumentException
     * @throws LogicalException
     */
    protected function getAggressiveStrategy(Customer $customer, AssociativeBaseArray $currentProducts, Order $currentOrder = null)
    {

        $initialCustomerStrategy = $this->customerStrategyController->getCustomerStrategy($customer, $currentProducts, $currentOrder);

        $testProducts = new AssociativeBaseArray($currentProducts, CurrentProduct::class);


        $initialDealerStrategy = $this->dealerStrategyController->getDealerStrategy($testProducts);

        arsort($initialDealerStrategy);
        arsort($initialCustomerStrategy);
        $initialDistance = $this->getVectorDiff($initialDealerStrategy, $initialCustomerStrategy);


        $prevCustomerStrategy = $initialCustomerStrategy;
        $customerStrategyKeys = array_keys($initialCustomerStrategy);
        for ($i = 1; $i < count($customerStrategyKeys); $i++) {
            $testProducts->add($this->getMaxDiscountProduct($currentProducts->getObj($customerStrategyKeys[$i]), $currentProducts->getObj($customerStrategyKeys[$i - 1])));

            $customerStrategy = $this->customerStrategyController->getCustomerStrategy($customer, $testProducts, $currentOrder);

            arsort($customerStrategy);

            if ($initialDistance <= $this->getVectorDiff($initialDealerStrategy, $customerStrategy) || $this->getVectorDiff($prevCustomerStrategy, $customerStrategy) == 0) {
                $testProducts->add($currentProducts->getObj($customerStrategyKeys[$i]));
            }

            $prevCustomerStrategy = $customerStrategy;
        }

        $sortedProducts = new AssociativeBaseArray(null, CurrentProduct::class);

        foreach ($customerStrategy as $key => $value) {
            $sortedProducts->add($testProducts->getObj($key));
        }

        return $sortedProducts;
    }

    /**
     * @param $v1
     * @param $v2
     * @return int
     * @throws LogicalException
     */
    protected function getVectorDiff($v1, $v2)
    {


        if (count($v1) != count($v2)) {
            throw new LogicalException("Dimension of both arrays has to be equal, but is " . count($v1) . " for v1 and " . count($v2) . " for v2.");
        }

        $v1Keys = array_keys($v1);
        $v2Keys = array_keys($v2);

        $dist = 0;

        for ($v1Pos = 0; $v1Pos < count($v1Keys); $v1Pos++) {

            $v2Pos = array_search($v1Keys[$v1Pos], $v2Keys);
            $dist += ($v2Pos === false) ? PHP_INT_MAX : pow($v1Pos - $v2Pos, 2);
        }

        return $dist;
    }

    /**
     * @param CurrentProduct $product
     * @param CurrentProduct $prevProduct
     * @return CurrentProduct
     * @throws InvalidArgumentException
     */
    protected function getMaxDiscountProduct(CurrentProduct $product, CurrentProduct $prevProduct)
    {

        if ($product->getId() == 2) {
            return $product;
        }

        $retProduct = new CurrentProduct($product->getId(), $product->getPrice(), $product->getExpiration(), $product->getPpc(), $product->getDiscount());

        $prevGuess = 0;
        $guess = 50;
        $a = 1;
        $b = 100;

        while (abs($guess - $prevGuess) > 1) {
            $prevGuess = $guess;

            $retProduct->setDiscount($guess);
            $retProduct = $this->mainInterface->setProductPPC($retProduct);

            if ($retProduct->getPpc() > $prevProduct->getPpc()) {
                $a = $guess;
            } else {
                $b = $guess;
            }

            $guess = ($a + $b) / 2;
        }

        $retProduct->setDiscount(floor($guess));

        $retProduct = $this->mainInterface->setProductPPC($retProduct);

        if ($retProduct->getPpc() == $prevProduct->getPpc()) {
            $retProduct->setDiscount($retProduct->getDiscount() - 1);
            $retProduct = $this->mainInterface->setProductPPC($retProduct);
        }

        return $retProduct;
    }

    /**
     * @param Customer $customer
     * @param AssociativeBaseArray $currentProducts
     * @param Order|null $currentOrder
     * @return AssociativeBaseArray
     * @throws InvalidArgumentException
     */
    protected function getConservativeStrategy(Customer $customer, AssociativeBaseArray $currentProducts, Order $currentOrder = null)
    {


        $sortedProducts = new AssociativeBaseArray(null, CurrentProduct::class);

        return $sortedProducts;
    }
}