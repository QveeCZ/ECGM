<?php

namespace ECGM;


use ECGM\Controller\CustomerGroupingController;
use ECGM\Controller\CustomerParametersCleaningController;
use ECGM\Controller\StrategyController;
use ECGM\Enum\StrategyType;
use ECGM\Int\CustomerGroupingInterface;
use ECGM\Int\CustomerParametersCleaningInterface;
use ECGM\Int\StrategyInterface;
use ECGM\Model\Customer;
use ECGM\Model\Order;

/**
 * Class ECGM
 * @package ECGM
 */
class ECGM
{
    /**
     * @var MainInterface
     */
    private $mainInterface;
    /**
     * @var StrategyInterface
     */
    private $strategyController;
    /**
     * @var CustomerGroupingInterface
     */
    private $groupingController;
    /**
     * @var CustomerParametersCleaningInterface
     */
    private $parameterCleaningController;

    /**
     * ECGM constructor.
     * @param MainInterface $mainInterface
     * @param int $strategyMultiplierCoefficient
     * @param int $dimension
     * @param int $initialClusterNumber
     * @param bool $autoClusterNumberAdjustment
     * @param int $maxProductsInStrategy
     * @throws Exceptions\InvalidArgumentException
     * @throws Exceptions\UndefinedException
     */
    public function __construct(MainInterface $mainInterface, $strategyMultiplierCoefficient, $dimension, $initialClusterNumber, $autoClusterNumberAdjustment = true, $maxProductsInStrategy = 40)
    {
        $this->mainInterface = $mainInterface;
        $this->strategyController = new StrategyController($strategyMultiplierCoefficient, $mainInterface, $maxProductsInStrategy);
        $this->groupingController = new CustomerGroupingController($dimension, $initialClusterNumber, $autoClusterNumberAdjustment);
        $this->parameterCleaningController = new CustomerParametersCleaningController();
    }

    /**
     * @return StrategyInterface
     */
    public function getStrategyController()
    {
        return $this->strategyController;
    }

    /**
     * @param StrategyInterface $strategyController
     */
    public function setStrategyController(StrategyInterface $strategyController)
    {
        $this->strategyController = $strategyController;
    }

    /**
     * @return CustomerGroupingInterface
     */
    public function getGroupingController()
    {
        return $this->groupingController;
    }

    /**
     * @param CustomerGroupingInterface $groupingController
     */
    public function setGroupingController(CustomerGroupingInterface $groupingController)
    {
        $this->groupingController = $groupingController;
    }

    /**
     * @return CustomerParametersCleaningInterface
     */
    public function getParameterCleaningController()
    {
        return $this->parameterCleaningController;
    }

    /**
     * @param CustomerParametersCleaningInterface $parameterCleaningController
     */
    public function setParameterCleaningController(CustomerParametersCleaningInterface $parameterCleaningController)
    {
        $this->parameterCleaningController = $parameterCleaningController;
    }


    /**
     * @return Model\BaseArray
     */
    public function groupCustomers()
    {
        $customers = $this->mainInterface->getUngroupedCustomers();
        $cleanedCustomers = $this->parameterCleaningController->cleanCustomers($customers);
        $currentGroups = $this->mainInterface->getCustomerGroups();
        $customerGroups = $this->groupingController->groupCustomers($cleanedCustomers, $currentGroups);
        return $customerGroups;
    }

    /**
     * @param Customer $customer
     * @param Order|null $currentOrder
     * @param int $strategyType
     * @return Model\AssociativeBaseArray
     */
    public function getStrategy(Customer $customer, Order $currentOrder = null, $strategyType = StrategyType::CONSERVATIVE)
    {
        $products = $this->mainInterface->getProducts();
        return $this->strategyController->getStrategy($customer, $products, $currentOrder, $strategyType);
    }

}