<?php

namespace ECGM\Int;


use ECGM\Model\BaseArray;
use ECGM\Model\Customer;

interface GroupingImplementationInterface
{

    /**
     * @param BaseArray $groups
     */
    public function setGroups(BaseArray $groups);

    /**
     * @param Customer $customer
     */
    public function addCustomer(Customer $customer);

    /**
     * @param BaseArray $customers
     */
    public function setCustomers(BaseArray $customers);

    /**
     * @return int
     */
    public function getDimension();

    /**
     * @param integer $nbGroups
     * @return BaseArray|mixed|null
     */
    public function solve($nbGroups);

    /**
     * @return DistanceFuncInterface
     */
    public function getDistanceFunctions();

    /**
     * @param DistanceFuncInterface $distanceFunctions
     */
    public function setDistanceFunctions(DistanceFuncInterface $distanceFunctions);

}