<?php

namespace ECGM\Controller;


use ECGM\Enum\StrategyType;
use ECGM\Enum\TreshholdType;
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
    private $strategyTreshhold;
    private $treshholdType;

    /**
     * StrategyController constructor.
     * @param $coefficient
     * @param MainInterface $mainInterface
     * @param int $strategyType
     * @throws InvalidArgumentException
     * @throws \ReflectionException
     */
    public function __construct($coefficient, MainInterface $mainInterface, $treshhold = 2, $strategyType = StrategyType::CONSERVATIVE, $treshHoldType = TreshholdType::NUMERIC)
    {
        if (!StrategyType::isValidValue($strategyType)) {
            throw new InvalidArgumentException("Strategy type is $strategyType, but available values are " . json_encode(StrategyType::getConstants()) . ".");
        }

        if (!TreshholdType::isValidValue($treshHoldType)) {
            throw new InvalidArgumentException("Treshhold type is $treshHoldType, but available values are " . json_encode(TreshholdType::getConstants()) . ".");
        }

        if ($treshHoldType == TreshholdType::PERCENTUAL && !((0 <= $treshhold) && ($treshhold <= 100))) {
            throw new InvalidArgumentException("Percentual treshhold has to be between 0 and 100 but is $treshhold.");
        }

        if ($treshHoldType == TreshholdType::NUMERIC && $treshhold <= 0) {
            throw new InvalidArgumentException("Numeric treshhold has to be larger than 0 but is $treshhold.");
        }

        $this->mainInterface = $mainInterface;
        $this->customerStrategyController = new CustomerStrategyController($coefficient);
        $this->dealerStrategyController = new DealerStrategyController();
        $this->strategyType = $strategyType;
        $this->strategyTreshhold = $treshhold;
        $this->treshholdType = $treshHoldType;
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

        if ($this->treshholdType == TreshholdType::PERCENTUAL) {
            $strategyTreshhold = round($currentProducts->size() * ($this->strategyTreshhold / 100));
        } else {
            $strategyTreshhold = $this->strategyTreshhold;
        }

        $initialCustomerStrategy = array_slice($this->customerStrategyController->getCustomerStrategy($customer, $currentProducts, $currentOrder), 0, $strategyTreshhold, true);

        $testProducts = new AssociativeBaseArray(null, CurrentProduct::class);

        foreach ($initialCustomerStrategy as $key => $value) {
            $testProducts->add($currentProducts->getObj($key));
        }

        $initialDealerStrategy = $this->dealerStrategyController->getDealerStrategy($testProducts);

        arsort($initialDealerStrategy);
        arsort($initialCustomerStrategy);
        $initialDistance = $this->getVectorDiff($initialDealerStrategy, $initialCustomerStrategy);


        $dealerStrategyKeys = array_keys($this->getPassiveIdealStrategy($initialDealerStrategy, $initialCustomerStrategy));
        for ($i = 0; $i < count($dealerStrategyKeys) - 1; $i++) {
            $testProducts->add($this->getMaxDiscountProduct($currentProducts->getObj($dealerStrategyKeys[$i]), $currentProducts->getObj($dealerStrategyKeys[$i + 1])));

            $customerStrategy = array_slice($this->customerStrategyController->getCustomerStrategy($customer, $testProducts, $currentOrder), 0, $strategyTreshhold, true);

            arsort($customerStrategy);

            if ($initialDistance <= $this->getVectorDiff($initialDealerStrategy, $customerStrategy)) {
                $testProducts->add($currentProducts->getObj($dealerStrategyKeys[$i]));
            }
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
     * @param CurrentProduct $nextProduct
     * @return CurrentProduct
     * @throws InvalidArgumentException
     */
    protected function getMaxDiscountProduct(CurrentProduct $product, CurrentProduct $nextProduct)
    {
        $discGuess = 50;
        $maxDisc = $discGuess;

        $retProduct = new CurrentProduct($product->getId(), $product->getPrice(), $product->getExpiration(), $product->getPpc(), $product->getDiscount());

        while ($discGuess > 1) {
            $retProduct->setDiscount($maxDisc);
            $retProduct = $this->mainInterface->setProductPPC($retProduct);
            $newPPC = $retProduct->getPpc();

            $discGuess = $discGuess / 2;

            if ($newPPC > $nextProduct->getPpc()) {
                $maxDisc += $discGuess;
            } else {
                $maxDisc -= $discGuess;
            }
        }

        $retProduct->setDiscount(floor($maxDisc));
        $retProduct = $this->mainInterface->setProductPPC($retProduct);

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