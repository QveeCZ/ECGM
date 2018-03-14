<?php

namespace ECGM\Controller;

use ECGM\Exceptions\InvalidArgumentException;
use ECGM\Exceptions\LogicalException;
use ECGM\Int\CustomerStrategyInterface;
use ECGM\Int\DealerStrategyInterface;
use ECGM\Int\StrategyTypeInterface;
use ECGM\MainInterface;
use ECGM\Model\AssociativeBaseArray;
use ECGM\Model\CurrentProduct;
use ECGM\Model\Customer;
use ECGM\Model\Order;

class ConservativeStrategyTypeController implements StrategyTypeInterface
{

    /**
     * @var CustomerStrategyInterface
     */
    protected $customerStrategyController;
    /**
     * @var DealerStrategyInterface
     */
    protected $dealerStrategyController;
    /**
     * @var MainInterface
     */
    protected $mainInterface;

    /**
     * StrategyController constructor.
     * @param $coefficient
     * @param MainInterface $mainInterface
     * @throws InvalidArgumentException
     */
    public function __construct($coefficient, MainInterface $mainInterface)
    {

        $this->mainInterface = $mainInterface;
        $this->customerStrategyController = new CustomerStrategyController($coefficient);
        $this->dealerStrategyController = new DealerStrategyController();
    }

    /**
     * @return CustomerStrategyInterface
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
     * @return DealerStrategyInterface
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
        return $this->getConservativeStrategy( $customer, $currentProducts, $currentOrder);
    }

    /**
     * @param Customer $customer
     * @param AssociativeBaseArray $currentProducts
     * @param Order|null $currentOrder
     * @return AssociativeBaseArray
     * @throws InvalidArgumentException
     * @throws LogicalException
     */
    protected function getConservativeStrategy(Customer $customer, AssociativeBaseArray $currentProducts, Order $currentOrder = null)
    {

        $currentCustomerStrategy = $this->customerStrategyController->getCustomerStrategy($customer, $currentProducts, $currentOrder);
        $currentDealerStrategy = $this->dealerStrategyController->getDealerStrategy($currentProducts);
        arsort($currentDealerStrategy);
        arsort($currentCustomerStrategy);

        $testProducts = new AssociativeBaseArray($currentProducts, CurrentProduct::class);

        /**
         * @var CurrentProduct $currentProduct
         */
        foreach ($currentProducts as $currentProduct) {
            $testProducts->add($this->getMaxDiscountProduct($currentProduct, $customer, $currentProducts, $currentOrder));

            $testCustomerStrategy = $this->customerStrategyController->getCustomerStrategy($customer, $testProducts, $currentOrder);
            $testDealerStrategy = $this->dealerStrategyController->getDealerStrategy($testProducts);
            arsort($testCustomerStrategy);
            arsort($testDealerStrategy);

            if ($this->getVectorDiff($currentDealerStrategy, $testDealerStrategy) != 0 || $this->getVectorDiff($currentCustomerStrategy, $testCustomerStrategy) == 0) {
                $testProducts->add($currentProduct);
            }else{
                $currentCustomerStrategy = $testCustomerStrategy;
                $currentDealerStrategy = $testDealerStrategy;
            }
        }

        $customerStrategy =  $this->customerStrategyController->getCustomerStrategy($customer, $testProducts, $currentOrder);

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
     * @param Customer $customer
     * @param AssociativeBaseArray $currentProducts
     * @param Order|null $currentOrder
     * @return CurrentProduct
     * @throws InvalidArgumentException
     */
    protected function getMaxDiscountProduct(CurrentProduct $product, Customer $customer, AssociativeBaseArray $currentProducts, Order $currentOrder = null)
    {


        $initialCustomerStrategy = $this->customerStrategyController->getCustomerStrategy($customer, $currentProducts, $currentOrder);
        arsort($initialCustomerStrategy);

        $testProducts = new AssociativeBaseArray($currentProducts, CurrentProduct::class);
        $retProduct = new CurrentProduct($product->getId(), $product->getPrice(), $product->getExpiration(), $product->getPpc(), $product->getDiscount());


        $customerStrategyKeys = array_keys($initialCustomerStrategy);
        $retProductPos = array_search($retProduct->getId(), $customerStrategyKeys);

        if($retProductPos == 0){
            return $retProduct;
        }

        $prevProduct = $currentProducts->getObj($customerStrategyKeys[$retProductPos - 1]);


        $prevGuess = 0;
        $guess = 50;
        $a = 1;
        $b = 100;

        while (abs($guess - $prevGuess) > 1) {
            $prevGuess = $guess;

            $retProduct->setDiscount($guess);
            $retProduct = $this->mainInterface->setProductPPC($retProduct);
            $testProducts->add($retProduct);

            $customerStrategy = $this->customerStrategyController->getCustomerStrategy($customer, $testProducts, $currentOrder);

            if ($customerStrategy[$prevProduct->getId()] > $customerStrategy[$retProduct->getId()]) {
                $a = $guess;
            } else {
                $b = $guess;
            }

            $guess = ($a + $b) / 2;
        }

        $retProduct->setDiscount(ceil($guess));
        $retProduct = $this->mainInterface->setProductPPC($retProduct);

        return $retProduct;
    }
}