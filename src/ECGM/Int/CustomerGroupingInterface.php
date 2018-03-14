<?php

namespace ECGM\Int;


use ECGM\Exceptions\InvalidArgumentException;
use ECGM\Model\BaseArray;
use ECGM\Model\Customer;

interface CustomerGroupingInterface
{


    /**
     * @return GroupingValidationInterface
     */
    public function getValidationClass();

    /**
     * @param GroupingValidationInterface $validationClass
     */
    public function setValidationClass(GroupingValidationInterface $validationClass);

    /**
     * @return GroupingImplementationInterface
     */
    public function getGroupingClass();

    /**
     * @param GroupingImplementationInterface $groupingClass
     */
    public function setGroupingClass(GroupingImplementationInterface $groupingClass);

    /**
     * @return DistanceFuncInterface
     */
    public function getDistanceFunctions();

    /**
     * @param DistanceFuncInterface $distanceFunctions
     */
    public function setDistanceFunctions(DistanceFuncInterface $distanceFunctions);

    /**
     * @param BaseArray $customers
     * @param BaseArray|null $initialGroups
     * @return BaseArray
     */
    public function groupCustomers(BaseArray $customers, BaseArray $initialGroups = null);

    /**
     * @param Customer $customer
     * @param BaseArray $groups
     * @return Customer
     * @throws InvalidArgumentException
     */
    public function assignToGroup(Customer $customer, BaseArray $groups);
}