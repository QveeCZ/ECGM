<?php

namespace ECGM\Controller;


use ECGM\Exceptions\InvalidValueException;
use ECGM\Model\BaseArray;
use ECGM\Model\Customer;
use ECGM\Model\CustomerGroup;
use ECGM\Model\CustomerParameter;
use ECGM\Model\Order;

class CustomerParametersController
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

        $customer->setParameters($this->getCleanedCustomerParameters($customer->getHistory(), $customer));

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
                throw new InvalidValueException("Expected parameter size is $expectedSize but some parameters in customer " . $customer->getId() . " history are not equal.");
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

    private function getCleanedCustomerParameters(BaseArray $history, Customer $customer)
    {
        $parameters = new BaseArray(null, CustomerParameter::class);
        $historyMatrix = $this->transformHistoryParameterMatrix($history);

        for ($i = 0; $i < count($historyMatrix); $i++) {
            $parameters->add(new CustomerParameter($i+1, $this->mergeParameters($historyMatrix[$i]), $customer));
        }

        return $parameters;
    }

    private function transformHistoryParameterMatrix(BaseArray $history)
    {

        $matrix = array();

        for ($i = 0; $i < $history->size(); $i++) {
            $row = array();
            /**
             * @var Order $order
             */
            $order = $history->getObj($i);
            for ($j = 0; $j < $order->getCustomer()->getParameters()->size(); $j++) {
                /**
                 * @var CustomerParameter $parameter
                 */
                $parameter = $order->getCustomer()->getParameters()->getObj($j);
                $row[] = $parameter->getValue();
            }
            $matrix[] = $row;
        }

        return $this->transposeMatrix($matrix);
    }

    private function transposeMatrix($matrix)
    {
        $transposedMatrix = array();
        foreach ($matrix as $row => $columns) {
            foreach ($columns as $row2 => $column2) {
                $transposedMatrix[$row2][$row] = $column2;
            }
        }
        return $transposedMatrix;
    }

    private function mergeParameters($parameters)
    {

        $zScores = $this->getModifietZScore($parameters);
        $nonExtremeValues = $this->getNonExtremeValues($parameters, $zScores);

        $minNonExtreme = min($nonExtremeValues);
        $maxNonExtreme = max($nonExtremeValues);
        $weightedSum = 0;
        $weightSum = 0;

        for ($i = 0; $i < count($parameters); $i++) {
            if ($zScores[$i] > 3.5) {
                $weight = $this->getParameterWeight($parameters[$i], $maxNonExtreme);
                $weightSum += $weight;
                $weightedSum += $parameters[$i] * $weight;
            } else if ($zScores[$i] < -3.5) {
                $weight = $this->getParameterWeight($parameters[$i], $minNonExtreme);
                $weightSum += $weight;
                $weightedSum += $parameters[$i] * $weight;
            } else {
                $weightSum += 1;
                $weightedSum += $parameters[$i];
            }
        }

        return $weightedSum / $weightSum;
    }

    private function getModifietZScore($parameters)
    {

        $median = StaticFuncController::arrayMedian($parameters);

        //Using MAD
        $divider = $this->getMAD($parameters, $median);

        if ($divider == 0) {
            //Switch to MeanAD
            $divider = $this->getMeanAD($parameters, $median);
        }

        $zScores = array();

        foreach ($parameters as $parameter) {
            $zScores[] = (0.6745 * ($parameter - $median)) / $divider;
        }

        return $zScores;
    }

    private function getMAD($parameters, $median = null)
    {
        if (is_null($median)) {
            $median = StaticFuncController::arrayMedian($parameters);
        }

        $absMedianDiffs = array();

        foreach ($parameters as $parameter) {
            $absMedianDiffs[] = abs($parameter - $median);
        }

        return StaticFuncController::arrayMedian($absMedianDiffs);
    }

    private function getMeanAD($parameters, $median = null)
    {
        if (is_null($median)) {
            $median = StaticFuncController::arrayMedian($parameters);
        }

        $n = count($parameters);
        $medianDeviationSum = 0;

        foreach ($parameters as $parameter) {
            $medianDeviationSum += abs($parameter - $median);
        }

        return $medianDeviationSum / $n;
    }

    private function getNonExtremeValues($parameters, $zScores)
    {
        $nonExtremeValues = array();
        for ($i = 0; $i < count($parameters); $i++) {
            if (abs($zScores[$i]) <= 3.5) {
                $nonExtremeValues[] = $parameters[$i];
            }
        }

        return $nonExtremeValues;
    }

    private function getParameterWeight($parameter, $hatU)
    {
        $c = 1.28;

        $weight = min(1, ($c / abs($parameter - abs($hatU))));

        return $weight;
    }

}