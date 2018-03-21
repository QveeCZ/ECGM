<?php

namespace ECGM\Controller;


use ECGM\Exceptions\InvalidArgumentException;
use ECGM\Int\CustomerParametersMergeInterface;
use ECGM\Model\BaseArray;
use ECGM\Model\Order;
use ECGM\Model\Parameter;
use ECGM\Util\MathFunctions;

/**
 * Class CustomerParametersMergeController
 * @package ECGM\Controller
 */
class CustomerParametersMergeController implements CustomerParametersMergeInterface
{

    /**
     * @param BaseArray $customerHistory
     * @return BaseArray
     * @throws InvalidArgumentException
     * @throws \ECGM\Exceptions\UndefinedException
     */
    public function mergeCustomerHistory(BaseArray $customerHistory)
    {

        if ($customerHistory->requiredBaseClass() != Order::class) {
            throw new InvalidArgumentException("Required class for customers has to be equal to " . Order::class . " but is " . $customerHistory->requiredBaseClass() . ".");
        }

        $parameters = new BaseArray(null, Parameter::class);

        $historyMatrix = $this->transformHistoryParameterMatrix($customerHistory);

        for ($i = 0; $i < count($historyMatrix); $i++) {
            $parameters->add(new Parameter($i + 1, $this->mergeTransformedParameters($historyMatrix[$i])));
        }


        return $parameters;
    }

    /**
     * @param BaseArray $history
     * @return array
     */
    protected function transformHistoryParameterMatrix(BaseArray $history)
    {

        $matrix = array();

        for ($i = 0; $i < $history->size(); $i++) {
            $row = array();
            /**
             * @var Order $order
             */
            $order = $history->getObj($i);
            for ($j = 0; $j < $order->getCustomerParameters()->size(); $j++) {
                /**
                 * @var Parameter $parameter
                 */
                $parameter = $order->getCustomerParameters()->getObj($j);
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
    protected function transposeMatrix($matrix)
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
     * @throws \ECGM\Exceptions\UndefinedException
     */
    protected function mergeTransformedParameters($parameters)
    {

        $median = MathFunctions::arrayMedian($parameters);
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
     * @throws \ECGM\Exceptions\UndefinedException
     */
    protected function getModifietZScore($parameters, $median)
    {
        if (is_null($median)) {
            $median = MathFunctions::arrayMedian($parameters);
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
     * @throws \ECGM\Exceptions\UndefinedException
     */
    protected function getMAD($parameters, $median = null)
    {
        if (is_null($median)) {
            $median = MathFunctions::arrayMedian($parameters);
        }

        $absMedianDiffs = array();

        foreach ($parameters as $parameter) {
            $absMedianDiffs[] = abs($parameter - $median);
        }

        return MathFunctions::arrayMedian($absMedianDiffs);
    }


    /**
     * @param array $parameters
     * @param float|null $median
     * @return float|int
     * @throws \ECGM\Exceptions\UndefinedException
     */
    protected function getMeanAD($parameters, $median = null)
    {
        if (is_null($median)) {
            $median = MathFunctions::arrayMedian($parameters);
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
    protected function getParameterWeight($parameter, $hatU)
    {
        $c = 1.28;

        $hatU = abs($hatU);

        if (-$hatU == $parameter) {
            $hatU += 1;
        }

        $weight = min(1, ($c / abs($parameter - $hatU)));

        return $weight;
    }
}