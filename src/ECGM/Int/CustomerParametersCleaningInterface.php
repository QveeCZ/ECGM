<?php
/**
 * Created by PhpStorm.
 * User: qvee
 * Date: 4.3.18
 * Time: 13:23
 */

namespace ECGM\Int;


use ECGM\Model\BaseArray;
use ECGM\Model\Customer;
use ECGM\Model\CustomerGroup;

interface CustomerParametersCleaningInterface
{

    /**
     * @return CustomerParametersMergeInterface
     */
    public function getCustomerParametersMergeController();

    /**
     * @param CustomerParametersMergeInterface $customerParametersMergeController
     */
    public function setCustomerParametersMergeController(CustomerParametersMergeInterface $customerParametersMergeController);

    /**
     * @param BaseArray $customerGroups
     * @return BaseArray
     */
    public function cleanCustomerGroups(BaseArray $customerGroups);

    /**
     * @param CustomerGroup $customerGroup
     * @return CustomerGroup
     */
    public function cleanCustomerGroup(CustomerGroup $customerGroup);

    /**
     * @param BaseArray $customers
     * @return BaseArray
     */
    public function cleanCustomers(BaseArray $customers);

    /**
     * @param Customer $customer
     * @return Customer
     */
    public function cleanCustomer(Customer $customer);


}