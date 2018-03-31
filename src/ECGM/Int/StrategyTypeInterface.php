<?php

namespace ECGM\Int;


use ECGM\Exceptions\InvalidArgumentException;
use ECGM\MainInterface;
use ECGM\Model\AssociativeBaseArray;
use ECGM\Model\Customer;
use ECGM\Model\Order;

interface StrategyTypeInterface
{

    /**
     * StrategyTypeInterface constructor.
     * @param float $coefficient
     * @param MainInterface $mainInterface
     * @param int $maxProductsInStrategy
     */
    public function __construct($coefficient, MainInterface $mainInterface, $maxProductsInStrategy = 40);

    /**
     * @return CustomerStrategyInterface
     */
    public function getCustomerStrategyController();

    /**
     * @param CustomerStrategyInterface $customerStrategyController
     */
    public function setCustomerStrategyController(CustomerStrategyInterface $customerStrategyController);

    /**
     * @return DealerStrategyInterface
     */
    public function getDealerStrategyController();

    /**
     * @param DealerStrategyInterface $dealerStrategyController
     */
    public function setDealerStrategyController(DealerStrategyInterface $dealerStrategyController);

    /**
     * @param Customer $customer
     * @param AssociativeBaseArray $currentProducts
     * @param Order|null $currentOrder
     * @return AssociativeBaseArray
     * @throws InvalidArgumentException
     */
    public function getIdealStrategy(Customer $customer, AssociativeBaseArray $currentProducts, Order $currentOrder = null);
}