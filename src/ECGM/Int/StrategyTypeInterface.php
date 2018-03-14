<?php

namespace ECGM\Int;


use ECGM\Exceptions\InvalidArgumentException;
use ECGM\Model\AssociativeBaseArray;
use ECGM\Model\Customer;
use ECGM\Model\Order;

interface StrategyTypeInterface
{
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