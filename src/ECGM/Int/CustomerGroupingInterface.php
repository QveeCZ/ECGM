<?php
namespace ECGM\Int;


use ECGM\Exceptions\InvalidArgumentException;
use ECGM\Model\BaseArray;
use ECGM\Model\Customer;

interface CustomerGroupingInterface
{

    /**
     * @param BaseArray $customers
     * @param BaseArray|null $initialGroups
     * @return BaseArray|mixed|null
     */
    public function groupCustomers(BaseArray $customers, BaseArray $initialGroups = null);

    /**
     * @param Customer $customer
     * @param BaseArray $groups
     * @return Customer
     * @throws InvalidArgumentException
     */
    public function assignToGroup(Customer $customer, BaseArray $groups);


    /**
     * @return integer
     */
    public function getDimension();

    /**
     * @return integer
     */
    public function getK();
}