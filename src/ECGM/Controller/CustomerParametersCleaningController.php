<?php

namespace ECGM\Controller;


use ECGM\Exceptions\InvalidArgumentException;
use ECGM\Int\CustomerParametersCleaningInterface;
use ECGM\Int\CustomerParametersMergeInterface;
use ECGM\Model\BaseArray;
use ECGM\Model\Customer;
use ECGM\Model\CustomerGroup;
use ECGM\Model\Order;
use ECGM\Model\Parameter;

/**
 * Class CustomerParametersCleaningController
 * @package ECGM\Controller
 */
class CustomerParametersCleaningController implements CustomerParametersCleaningInterface
{
    /**
     * @var CustomerParametersMergeInterface
     */
    protected $customerParametersMergeController;

    /**
     * CustomerParametersCleaningController constructor.
     */
    public function __construct()
    {
        $this->customerParametersMergeController = new CustomerParametersMergeController();
    }

    /**
     * @return CustomerParametersMergeInterface
     */
    public function getCustomerParametersMergeController()
    {
        return $this->customerParametersMergeController;
    }

    /**
     * @param CustomerParametersMergeInterface $customerParametersMergeController
     */
    public function setCustomerParametersMergeController(CustomerParametersMergeInterface $customerParametersMergeController)
    {
        $this->customerParametersMergeController = $customerParametersMergeController;
    }

    /**
     * @param BaseArray $customerGroups
     * @return BaseArray
     * @throws InvalidArgumentException
     */
    public function cleanCustomerGroups(BaseArray $customerGroups)
    {
        if ($customerGroups->requiredBaseClass() != Customer::class) {
            throw new InvalidArgumentException("Required class for customerGroups has to be equal to " . CustomerGroup::class . " but is " . $customerGroups->requiredBaseClass() . ".");
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

    /**
     * @param CustomerGroup $customerGroup
     * @return CustomerGroup
     * @throws InvalidArgumentException
     */
    public function cleanCustomerGroup(CustomerGroup $customerGroup)
    {
        $customerGroup->setCustomers($this->cleanCustomers($customerGroup->getCustomers()));
        return $customerGroup;
    }

    /**
     * @param BaseArray $customers
     * @return BaseArray
     * @throws InvalidArgumentException
     */
    public function cleanCustomers(BaseArray $customers)
    {
        if ($customers->requiredBaseClass() != Customer::class) {
            throw new InvalidArgumentException("Required class for customers has to be equal to " . Customer::class . " but is " . $customers->requiredBaseClass() . ".");
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

    /**
     * @param Customer $customer
     * @return Customer
     * @throws InvalidArgumentException
     */
    public function cleanCustomer(Customer $customer)
    {

        $this->validateCustomerParameters($customer);;
        $transformedHistory = new BaseArray(null, Order::class);

        /**
         * @var Order $order
         */
        foreach ($customer->getHistory() as $order) {
            $order->setCustomerParameters($this->transformCircularValues($order->getCustomerParameters()));
            $transformedHistory->add($order);
        }
        $customer->setHistory($transformedHistory);

        $customer->setParameters($this->getMergedCustomerParameters($customer->getHistory()));

        return $customer;
    }

    //protected functions

    /**
     * @param Customer $customer
     * @throws InvalidArgumentException
     */
    protected function validateCustomerParameters(Customer $customer)
    {
        $expectedSize = null;

        /**
         * @var Order $customerOrder
         */
        foreach ($customer->getHistory() as $customerOrder) {
            if (is_null($expectedSize)) {
                $expectedSize = $customerOrder->getCustomerParameters()->size();
            }

            if ($customerOrder->getCustomerParameters()->size() != $expectedSize) {
                throw new InvalidArgumentException("Expected parameter size is $expectedSize but the size of parameters in customer " . $customer->getId() . " history are not equal.");
            }
        }
    }

    /**
     * @param BaseArray $parameters
     * @return BaseArray
     * @throws InvalidArgumentException
     */
    protected function transformCircularValues(BaseArray $parameters)
    {

        $transformedCustomerParameters = new BaseArray(null, Parameter::class);

        /**
         * @var Parameter $customerParameter
         */
        foreach ($parameters as $customerParameter) {
            if ($customerParameter->isCircular()) {
                $transformedCustomerParameters->merge($this->transformCircularValue($customerParameter));
            } else {
                $transformedCustomerParameters->add($customerParameter);
            }
        }

        return $transformedCustomerParameters;
    }

    /**
     * @param Parameter $parameter
     * @return BaseArray
     * @throws InvalidArgumentException
     */
    protected function transformCircularValue(Parameter $parameter)
    {
        if (!$parameter->isCircular()) {
            throw  new InvalidArgumentException("Customer parameter " . $parameter->getId() . " is expected to be circular, but is not.");
        }

        $parameterValue = $parameter->getValue();

        $maxValue = $parameter->getMaxValue();

        $parameterValueX = sin(2 * pi() * $parameterValue / $maxValue);

        $parameterValueY = cos(2 * pi() * $parameterValue / $maxValue);

        $ret = new BaseArray(null, Parameter::class);
        $ret->add(new Parameter($parameter->getId() . "X", $parameterValueX, $parameter->getCustomer()));
        $ret->add(new Parameter($parameter->getId() . "Y", $parameterValueY, $parameter->getCustomer()));

        return $ret;
    }

    /**
     * @param BaseArray $history
     * @return BaseArray
     */
    protected function getMergedCustomerParameters(BaseArray $history)
    {
        return $this->customerParametersMergeController->mergeCustomerHistory($history);
    }

}