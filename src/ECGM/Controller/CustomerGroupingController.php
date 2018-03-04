<?php

namespace ECGM\Controller;

use ECGM\Exceptions\InvalidArgumentException;
use ECGM\Int\CustomerGroupingInterface;
use ECGM\Int\GroupingImplementationInterface;
use ECGM\Int\GroupingValidationInterface;
use ECGM\Model\BaseArray;
use ECGM\Model\Customer;
use ECGM\Model\CustomerGroup;
use ECGM\Util\KmeansPlusPlus;
use ECGM\Util\MathFunctions;
use ECGM\Util\SilhouetteAnalysis;

class CustomerGroupingController implements CustomerGroupingInterface
{
    /**
     * @var integer
     */
    protected $dimension;
    /**
     * @var integer
     */
    protected $k;
    /**
     * @var boolean
     */
    protected $verbose;
    /**
     * @var boolean
     */
    protected $autoKAdjustment;
    
    /*
     * @var GroupingValidationInterface
     */
    protected $validationClass;


    /*
     * @var GroupingImplementationInterface
     */
    protected $groupingClass;

    /**
     * CustomerGroupingController constructor.
     * @param integer $dimension
     * @param integer $initK
     * @param bool $autoKAdjustment
     * @param bool $verbose
     * @throws InvalidArgumentException
     */
    public function __construct($dimension, $initK, $autoKAdjustment = true, $verbose = false)
    {
        if (!is_numeric($dimension) || $dimension < 1) {
            throw new InvalidArgumentException("Dimension $dimension is invalid.");
        }

        $this->dimension = $dimension;

        if (!is_numeric($initK) || $initK < 1) {
            throw new InvalidArgumentException("Initial K $initK is invalid.");
        }

        $this->k = $initK;

        $this->verbose = ($verbose) ? true : false;
        $this->autoKAdjustment = ($autoKAdjustment) ? true : false;
        
        $this->groupingClass = new KmeansPlusPlus($dimension);
        $this->validationClass = new SilhouetteAnalysis();
    }

    /**
     * @return GroupingValidationInterface
     */
    public function getValidationClass()
    {
        return $this->validationClass;
    }

    /**
     * @param GroupingValidationInterface $validationClass
     */
    public function setValidationClass(GroupingValidationInterface $validationClass)
    {
        $this->validationClass = $validationClass;
    }

    /**
     * @return GroupingImplementationInterface
     */
    public function getGroupingClass()
    {
        return $this->groupingClass;
    }

    /**
     * @param GroupingImplementationInterface $groupingClass
     */
    public function setGroupingClass(GroupingImplementationInterface $groupingClass)
    {
        $this->groupingClass = $groupingClass;
    }

    /**
     * @param BaseArray $customers
     * @param BaseArray|null $initialGroups
     * @return BaseArray|mixed|null
     */
    public function groupCustomers(BaseArray $customers, BaseArray $initialGroups = null)
    {

        $prevSilhouette = PHP_INT_MIN;
        $silhouette = PHP_INT_MIN + 1;

        $groups = new BaseArray(null, CustomerGroup::class);

        while ($prevSilhouette < $silhouette) {
            if ($this->verbose) {
                echo "K-" . $this->k . ".\n";
            }
            $groups = $this->getGroups($this->k, $customers, $initialGroups);

            if (!$this->autoKAdjustment) {
                break;
            }

            $prevSilhouette = $silhouette;
            $silhouette = $this->getSilhouette($groups);
            $this->k += 1;
        }

        if ($this->verbose && $this->autoKAdjustment) {
            echo "Silhouette $silhouette, Prev silhouette $prevSilhouette.\n";
        }

        return $groups;
    }

    /**
     * @param Customer $customer
     * @param BaseArray $groups
     * @return Customer
     * @throws InvalidArgumentException
     */
    public function assignToGroup(Customer $customer, BaseArray $groups)
    {
        if($customer->getGroup()){
            throw new InvalidArgumentException("Customer has already assigned group " . $customer->getGroup()->getId() . ".");
        }

        $groups = new BaseArray($groups, CustomerGroup::class);

        $customer->setGroup($this->getBestGroup($customer, $groups));

        return $customer;
    }

    /**
     * @return mixed
     */
    public function getDimension()
    {
        return $this->dimension;
    }

    /**
     * @return mixed
     */
    public function getK()
    {
        return $this->k;
    }

    /**
     * @param Customer $customer
     * @param BaseArray $groups
     * @return CustomerGroup|null
     */
    protected function getBestGroup(Customer $customer, BaseArray $groups)
    {
        $groups = new BaseArray($groups, CustomerGroup::class);

        $dist = PHP_INT_MAX;
        $bestGroup = null;

        /**
         * @var CustomerGroup $group
         */
        foreach ($groups as $group){
            $currDist = MathFunctions::euclideanDistance($customer->getParametersAsSimpleArray(), $group->getParametersAsSimpleArray());
            if($dist > $currDist){
                $dist = $currDist;
                $bestGroup = $group;
            }
        }

        return $bestGroup;
    }

    /**
     * @param integer $k
     * @param BaseArray $customers
     * @param BaseArray|null $groups
     * @return BaseArray|mixed|null
     */
    protected function getGroups($k, BaseArray $customers, BaseArray $groups = null)
    {
        $customers = new BaseArray($customers, Customer::class);
        $groups = new BaseArray($groups, CustomerGroup::class);

        if ($groups->size() > 0) {
            $this->groupingClass->setGroups($groups);
        }

        $this->groupingClass->setCustomers($customers);

        return $this->groupingClass->solve($k);
    }

    /**
     * @param BaseArray $groups
     * @return float|int
     */
    protected function getSilhouette(BaseArray $groups)
    {
        $groups = new BaseArray($groups, CustomerGroup::class);
        return $this->validationClass->getGroupingScore($groups);
    }

}