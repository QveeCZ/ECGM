<?php

namespace ECGM\Controller;

use ECGM\Exceptions\InvalidArgumentException;
use ECGM\Int\CustomerStrategyInterface;
use ECGM\Int\DealerStrategyInterface;
use ECGM\Int\StrategyTypeInterface;
use ECGM\MainInterface;
use ECGM\Model\AssociativeBaseArray;
use ECGM\Model\CurrentProduct;
use ECGM\Model\Customer;
use ECGM\Model\Order;

class PassiveStrategyTypeController implements StrategyTypeInterface
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
     */
    public function getIdealStrategy(Customer $customer, AssociativeBaseArray $currentProducts, Order $currentOrder = null)
    {
        $dealerStrategy = $this->dealerStrategyController->getDealerStrategy($currentProducts);
        $customerStrategy = $this->customerStrategyController->getCustomerStrategy($customer, $currentProducts, $currentOrder);


        $sortedProducts = new AssociativeBaseArray(null, CurrentProduct::class);

        $idealStrategy = $this->getPassiveStrategy($dealerStrategy, $customerStrategy);

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
    protected function getPassiveStrategy($dealerStrategy, $customerStrategy)
    {

        $idealStrategy = array();

        foreach ($dealerStrategy as $product => $strategy) {
            $idealStrategy[$product] = $strategy * $customerStrategy[$product];
        }

        arsort($idealStrategy);

        return $idealStrategy;
    }
}