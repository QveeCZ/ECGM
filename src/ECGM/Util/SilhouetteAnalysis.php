<?php

namespace ECGM\Util;


use ECGM\Exceptions\InvalidArgumentException;
use ECGM\Exceptions\LogicalException;
use ECGM\Int\GroupingValidationInterface;
use ECGM\Model\BaseArray;
use ECGM\Model\Customer;
use ECGM\Model\CustomerGroup;

/**
 *
 * Class SilhouetteAnalysis
 * @package ECGM\Util
 */
class SilhouetteAnalysis implements GroupingValidationInterface
{
    /**
     * @var boolean
     */
    protected $verbose;
    /**
     * @var FileWriter $fileWriter
     */
    protected $fileWriter;

    /**
     * SilhouetteAnalysis constructor.
     * @param bool $verbose
     */
    public function __construct($verbose = false)
    {
        $this->verbose = ($verbose) ? true : false;

        if ($this->verbose) {
            $dateTime = new \DateTime();
            $this->fileWriter = new FileWriter("silhouette_" . $dateTime->format('Y-m-d_H:i:s') . "_" . uniqid() . ".csv");
        }
    }

    /**
     * @param BaseArray $groups
     * @return float|int
     */
    public function getGroupingScore(BaseArray $groups){
        return $this->getAverageSilhouetteWidth($groups);
    }

    /**
     * @param BaseArray $groups
     * @return float|int
     */
    protected function getAverageSilhouetteWidth(BaseArray $groups)
    {
        $groups = new BaseArray($groups, CustomerGroup::class);
        $customers = $this->getCustomers($groups);

        $silhouettesSum = $this->getSilhouettes($customers, $groups);

        $avgSilhouetteWidth = $silhouettesSum / $customers->size();

        return $avgSilhouetteWidth;
    }

    /**
     * @param BaseArray $groups
     * @return BaseArray
     */
    protected function getCustomers(BaseArray $groups)
    {
        $customers = new BaseArray(null, Customer::class);

        /**
         * @var CustomerGroup $group
         */
        foreach ($groups as $group) {
            $customers->merge($group->getCustomers());
        }

        return $customers;
    }

    /**
     * @param BaseArray $customers
     * @param BaseArray $groups
     * @return integer
     */
    protected function getSilhouettes(BaseArray $customers, BaseArray $groups)
    {
        $customers = new BaseArray($customers, Customer::class);
        $groups = new BaseArray($groups, CustomerGroup::class);

        $silhouettesSum = 0;

        /**
         * @var Customer $customer
         */
        foreach ($customers as $customer) {
            $silhouette = $this->getCustomerSilhouette($customer, $groups);
            $this->addToFile($silhouette, $customer->getGroup()->getId());
            $silhouettesSum += $silhouette;
        }

        return $silhouettesSum;
    }

    /**
     * Interpretation:
     * 0.71 - 1.00 -- x is strongly placed to given group
     * 0.51 - 0.70 -- x is well placed to given group
     * 0.26 - 0.50 -- x is weakly placed to given group
     * -1.00 - 0.25 -- x is probably badly placed in given group
     *
     * @param Customer $targetCustomer
     * @param BaseArray $groups
     * @return float
     */
    protected function getCustomerSilhouette(Customer $targetCustomer, BaseArray $groups)
    {
        $groups = new BaseArray($groups, CustomerGroup::class);

        $innerGroup = $targetCustomer->getGroup();
        $outerGroups = new BaseArray($groups, CustomerGroup::class);

        $outerGroups->removeByObject($innerGroup);

        $innerDistance = $this->getInnerGroupDistance($targetCustomer, $innerGroup);
        $neighbourDistance = $this->getNeighbouringGroupDistance($targetCustomer, $outerGroups);

        $silhouette = ($neighbourDistance - $innerDistance) / (max($innerDistance, $neighbourDistance));

        return $silhouette;
    }

    /**
     * @param Customer $targetCustomer
     * @param CustomerGroup $group
     * @return float|int
     * @throws InvalidArgumentException
     */
    protected function getInnerGroupDistance(Customer $targetCustomer, CustomerGroup $group)
    {
        if (!$targetCustomer->getGroup() || $targetCustomer->getGroup()->getId() != $group->getId()) {
            throw new InvalidArgumentException("Customer has bad or undefined group.");
        }

        $wGroup = new BaseArray($group->getCustomers(), Customer::class);
        $wGroup->removeByObject($targetCustomer);

        return $this->getCustomersDistanceSum($targetCustomer, $wGroup);
    }

    /**
     * @param Customer $targetCustomer
     * @param BaseArray $customers
     * @return float|int
     */
    protected function getCustomersDistanceSum(Customer $targetCustomer, BaseArray $customers)
    {

        $customers = new BaseArray($customers, Customer::class);
        $distanceSum = 0;

        if($customers->size() == 0){
            return 0;
        }

        /**
         * @var Customer $customer
         */
        foreach ($customers as $customer) {
            $distanceSum += MathFunctions::manhattanDistance($targetCustomer->getParametersAsSimpleArray(), $customer->getParametersAsSimpleArray());
        }

        return $distanceSum / $customers->size();
    }

    /**
     * @param Customer $targetCustomer
     * @param BaseArray $groups
     * @return mixed
     * @throws LogicalException
     */
    protected function getNeighbouringGroupDistance(Customer $targetCustomer, BaseArray $groups)
    {
        $groups = new BaseArray($groups, CustomerGroup::class);

        if($groups->size() == 0){
            return PHP_INT_MAX;
        }

        $min = PHP_INT_MAX;

        /**
         * @var CustomerGroup $group
         */
        foreach ($groups as $group) {
            if ($targetCustomer->getGroup()->getId() == $group->getId()) {
                throw new LogicalException("Target customers group is equal to group " . $group->getId() . " that makes it inner group. Outer groups distance would be invalid.");
            }

            $outerDistance = $this->getCustomersDistanceSum($targetCustomer, $group->getCustomers());

            if($outerDistance < $min){
                $min = $outerDistance;
            }
        }


        return $min;
    }

    /**
     * @param integer|float $silhouette
     * @param mixed $group
     */
    protected function addToFile($silhouette, $group)
    {
        if ($this->verbose) {
            $this->fileWriter->putLineToCSV(array($silhouette, $group));
        }
    }

}