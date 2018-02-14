<?php
namespace ECGM\Controller;


use ECGM\Exceptions\InvalidValueException;
use ECGM\Model\BaseArray;
use ECGM\Model\Customer;
use ECGM\Model\CustomerParameter;
use ECGM\Model\Order;

class CustomerParametersMergeController
{

    /**
     * @param BaseArray $customerHistory
     * @param Customer $customer
     * @return BaseArray
     * @throws InvalidValueException
     */
    public function mergeCustomerHistory(BaseArray $customerHistory, Customer $customer){

        if($customerHistory->requiredBaseClass() != Order::class){
            throw new InvalidValueException("Required class for customers has to be equal to " . Order::class . " but is " . $customerHistory->requiredBaseClass() . ".");
        }

        $parameters = new BaseArray(null, CustomerParameter::class);

        $historyMatrix = $this->transformHistoryParameterMatrix($customerHistory);

        for ($i = 0; $i < count($historyMatrix); $i++) {
            $parameters->add(new CustomerParameter($i + 1, $this->mergeTransformedParameters($historyMatrix[$i]), $customer));
        }


        return $parameters;
    }

    /**
     * @param BaseArray $history
     * @return array
     */
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

    /**
     * @param array $matrix
     * @return array
     */
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

    /**
     * @param array $parameters
     * @return float|int
     */
    private function mergeTransformedParameters($parameters)
    {

        $median = SharedStaticFunctionsController::arrayMedian($parameters);
        $zScores = $this->getModifietZScore($parameters, $median);
        $weightedSum = 0;
        $weightSum = 0;

        for ($i = 0; $i < count($parameters); $i++) {
            if (abs($zScores[$i]) > 3.5) {
                $weight = $this->getParameterWeight($parameters[$i], $median);
                $weightSum += $weight;
                $weightedSum += $parameters[$i] * $weight;
            } else {
                $weightSum += 1;
                $weightedSum += $parameters[$i];
            }
        }

        return $weightedSum / $weightSum;
    }

    /**
     * @param array $parameters
     * @param float $median
     * @return array
     */
    private function getModifietZScore($parameters, $median)
    {
        if (is_null($median)) {
            $median = SharedStaticFunctionsController::arrayMedian($parameters);
        }

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

    /**
     * @param array $parameters
     * @param float|null $median
     * @return float|int
     */
    private function getMAD($parameters, $median = null)
    {
        if (is_null($median)) {
            $median = SharedStaticFunctionsController::arrayMedian($parameters);
        }

        $absMedianDiffs = array();

        foreach ($parameters as $parameter) {
            $absMedianDiffs[] = abs($parameter - $median);
        }

        return SharedStaticFunctionsController::arrayMedian($absMedianDiffs);
    }

    /**
     * @param array $parameters
     * @param float|null $median
     * @return float|int
     */
    private function getMeanAD($parameters, $median = null)
    {
        if (is_null($median)) {
            $median = SharedStaticFunctionsController::arrayMedian($parameters);
        }

        $n = count($parameters);
        $medianDeviationSum = 0;

        foreach ($parameters as $parameter) {
            $medianDeviationSum += abs($parameter - $median);
        }

        return $medianDeviationSum / $n;
    }

    /**
     * @param float $parameter
     * @param float $hatU
     * @return mixed
     */
    private function getParameterWeight($parameter, $hatU)
    {
        $c = 1.28;

        if ($hatU == 0) {
            $hatU = 1;
        }

        $weight = min(1, ($c / abs($parameter - abs($hatU))));

        return $weight;
    }
}