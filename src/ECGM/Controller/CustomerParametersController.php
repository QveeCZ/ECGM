<?php

namespace ECGM\Controller;


use ECGM\Exceptions\InvalidValueException;
use ECGM\Model\BaseArray;
use ECGM\Model\Customer;
use ECGM\Model\CustomerParameter;
use ECGM\Model\Order;

class CustomerParametersController
{

    /**
     * @param BaseArray $customerArray
     * @return BaseArray
     * @throws InvalidValueException
     */
    public function cleanCustomers(BaseArray $customerArray)
    {
        if ($customerArray->requiredBaseClass() != Customer::class) {
            throw new InvalidValueException("Required class for customerArray should be equal to " . Customer::class . " but is " . $customerArray->requiredBaseClass() . ".");
        }

        $cleanedCustomerArray = new BaseArray(null, Customer::class);

        /**
         * @var Customer $customer
         */
        foreach ($customerArray->get() as $customer) {
            $cleanedCustomerArray->add($this->cleanCustomer($customer));
        }

        return $cleanedCustomerArray;
    }


    public function cleanCustomer(Customer $customer)
    {

        $this->validateCustomerParameters($customer);

        $customer = $this->transformCircularValues($customer);

        $transformedHistory = new BaseArray(null, Order::class);
        /**
         * @var Order $order
         */
        foreach ($customer->getHistory()->get() as $order){
            $order->setCustomer($this->transformCircularValues($order->getCustomer()));
            $transformedHistory->add($order);
        }
        $customer->setHistory($transformedHistory);

        return $customer;
    }

    private function validateCustomerParameters(Customer $customer){
        $expectedSize = $customer->getParameters()->size();

        /**
         * @var Order $customerOrder
         */
        foreach ($customer->getHistory()->get() as $customerOrder){
            if($customerOrder->getCustomer()->getParameters()->size() != $expectedSize){
                throw new InvalidValueException("Expected parameter size is $expectedSize but some parameters in customer " . $customer->getId() . " history are not equal.");
            }
        }
    }

    private function transformCircularValues(Customer $customer){

        $transformedCustomerParameters = new BaseArray(null, CustomerParameter::class);

        /**
         * @var CustomerParameter $customerParameter
         */
        foreach ($customer->getParameters()->get() as $customerParameter){
            if($customerParameter->isCircular()){
                $transformedCustomerParameters->merge($this->transformCircularValue($customerParameter));
            }else{
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

}