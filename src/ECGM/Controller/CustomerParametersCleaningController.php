<?php

namespace ECGM\Controller;


use ECGM\Exceptions\InvalidValueException;
use ECGM\Model\BaseArray;
use ECGM\Model\Customer;
use ECGM\Model\CustomerGroup;
use ECGM\Model\CustomerParameter;
use ECGM\Model\Order;

class CustomerParametersCleaningController
{

    public function cleanCustomerGroups(BaseArray $customerGroups)
    {
        if ($customerGroups->requiredBaseClass() != Customer::class) {
            throw new InvalidValueException("Required class for customerGroups has to be equal to " . CustomerGroup::class . " but is " . $customerGroups->requiredBaseClass() . ".");
        }

        $retGroups = new BaseArray(null, CustomerGroup::class);

        /**
         * @var CustomerGroup $customerGroup
         */
        foreach ($customerGroups as $customerGroup) {
            $retGroups->add($this->cleanCustomerGroup($customerGroup));
        }

        return $retGroups;
    }

    public function cleanCustomerGroup(CustomerGroup $customerGroup)
    {
        $customerGroup->setCustomers($this->cleanCustomers($customerGroup->getCustomers()));
        return $customerGroup;
    }

    /**
     * @param BaseArray $customers
     * @return BaseArray
     * @throws InvalidValueException
     */
    public function cleanCustomers(BaseArray $customers)
    {
        if ($customers->requiredBaseClass() != Customer::class) {
            throw new InvalidValueException("Required class for customers has to be equal to " . Customer::class . " but is " . $customers->requiredBaseClass() . ".");
        }

        $cleanedCustomerArray = new BaseArray(null, Customer::class);

        /**
         * @var Customer $customer
         */
        foreach ($customers as $customer) {
            $cleanedCustomerArray->add($this->cleanCustomer($customer));
        }

        return $cleanedCustomerArray;
    }


    public function cleanCustomer(Customer $customer)
    {

        $this->validateCustomerParameters($customer);;
        $transformedHistory = new BaseArray(null, Order::class);

        /**
         * @var Order $order
         */
        foreach ($customer->getHistory() as $order) {
            $order->setCustomer($this->transformCircularValues($order->getCustomer()));
            $transformedHistory->add($order);
        }
        $customer->setHistory($transformedHistory);

        $customer->setParameters($this->getMergedCustomerParameters($customer->getHistory(), $customer));

        return $customer;
    }

    //Private functions

    /**
     * @param Customer $customer
     * @throws InvalidValueException
     */
    private function validateCustomerParameters(Customer $customer)
    {
        $expectedSize = null;

        /**
         * @var Order $customerOrder
         */
        foreach ($customer->getHistory() as $customerOrder) {
            if (is_null($expectedSize)) {
                $expectedSize = $customerOrder->getCustomer()->getParameters()->size();
            }

            if ($customerOrder->getCustomer()->getParameters()->size() != $expectedSize) {
                throw new InvalidValueException("Expected parameter size is $expectedSize but the size of parameters in customer " . $customer->getId() . " history are not equal.");
            }
        }
    }

    /**
     * @param Customer $customer
     * @return Customer
     */
    private function transformCircularValues(Customer $customer)
    {

        $transformedCustomerParameters = new BaseArray(null, CustomerParameter::class);

        /**
         * @var CustomerParameter $customerParameter
         */
        foreach ($customer->getParameters() as $customerParameter) {
            if ($customerParameter->isCircular()) {
                $transformedCustomerParameters->merge($this->transformCircularValue($customerParameter));
            } else {
                $transformedCustomerParameters->add($customerParameter);
            }
        }

        $customer->setParameters($transformedCustomerParameters);

        return $customer;
    }

    /**
     * @param CustomerParameter $parameter
     * @return BaseArray
     * @throws InvalidValueException
     */
    private function transformCircularValue(CustomerParameter $parameter)
    {
        if (!$parameter->isCircular()) {
            throw  new InvalidValueException("Customer parameter " . $parameter->getId() . " is expected to be circular, but is not.");
        }

        $parameterValue = $parameter->getValue();

        $maxValue = $parameter->getMaxValue();

        $parameterValueX = sin(2 * pi() * $parameterValue / $maxValue);

        $parameterValueY = cos(2 * pi() * $parameterValue / $maxValue);

        $ret = new BaseArray(null, CustomerParameter::class);
        $ret->add(new CustomerParameter($parameter->getId() . "X", $parameterValueX, $parameter->getCustomer()));
        $ret->add(new CustomerParameter($parameter->getId() . "Y", $parameterValueY, $parameter->getCustomer()));

        return $ret;
    }

    /**
     * @param BaseArray $history
     * @param Customer $customer
     * @return BaseArray
     */
    private function getMergedCustomerParameters(BaseArray $history, Customer $customer)
    {
        $parameterMergeConstroller = new CustomerParametersMergeController();
        return $parameterMergeConstroller->mergeCustomerHistory($history, $customer);
    }

}