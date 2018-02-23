<?php

namespace ECGM\Util;


use ECGM\Exceptions\InvalidArgumentException;
use ECGM\Exceptions\UndefinedException;
use ECGM\Model\BaseArray;
use ECGM\Model\Customer;
use ECGM\Model\CustomerGroup;
use ECGM\Model\Parameter;

/**
 * Class KmeansPlusPlus
 * Based on
 * https://github.com/bdelespierre/php-kmeans
 * @package ECGM\Util
 */
class KmeansPlusPlus
{
    protected $dimension;
    protected $groups;
    protected $customers;

    /**
     * KmeansPlusPlus constructor.
     * @param integer $dimension
     * @throws UndefinedException
     */
    public function __construct($dimension)
    {
        if ($dimension < 1) {
            throw new UndefinedException("A space dimension cannot be null or negative.");
        }

        $this->dimension = $dimension;
        $this->groups = new BaseArray(null, CustomerGroup::class);
        $this->customers = new BaseArray(null, Customer::class);
    }

    /**
     * @param BaseArray $groups
     */
    public function setGroups(BaseArray $groups)
    {
        $this->groups->set($groups);
    }

    /**
     * @param Customer $customer
     */
    public function addCustomer(Customer $customer)
    {
        return $this->customers->add($customer);
    }

    /**
     * @param BaseArray $customers
     */
    public function setCustomers(BaseArray $customers){
        $this->customers->set($customers);
    }

    /**
     * @return int
     */
    public function getDimension()
    {
        return $this->dimension;
    }

    /**
     * @param integer $nbGroups
     * @return BaseArray|mixed|null
     * @throws InvalidArgumentException
     */
    public function solve($nbGroups)
    {

        if (!$this->groups->size() && $nbGroups != $this->groups->size()) {
            throw new InvalidArgumentException("Required number of groups $nbGroups is not equal to set number of groups " . $this->groups->size() . ".");
        }

        if (!$this->groups->size()) {
            $this->groups = $this->initializeGroups($nbGroups);
        }

        if ($this->groups->size() == 1) {
            return $this->groups->getObj(0);
        }

        do {
            $continue = $this->iterate();
        } while ($continue);

        return $this->groups;
    }

    /**
     * @param integer $clusterNumber
     * @return BaseArray
     * @throws InvalidArgumentException
     */
    protected function initializeGroups($clusterNumber)
    {

        if (!is_numeric($clusterNumber)) {
            throw new InvalidArgumentException("Number of clusters has to be numeric.");
        }

        if ($clusterNumber <= 0) {
            throw new InvalidArgumentException("Number of clusters has to be greater than 0, but is " . $clusterNumber);
        }

        $groups = new BaseArray(null, CustomerGroup::class);

        $position = rand(1, $this->customers->size());
        for ($i = 1, $this->customers->rewind(); $i < $position; $i++) {
            $this->customers->next();
        }

        $groups->add(new CustomerGroup($this->groups->nextKey(), $this->customers->current()->getParameters()));

        // retains the distances between points and their closest clusters
        $distances = new \SplObjectStorage();

        // create k clusters
        for ($i = 1; $i < $clusterNumber; $i++) {
            $sum = 0;

            // for each points, get the distance with the closest centroid already choosen

            /**
             * @var Customer $customer
             */
            foreach ($this->customers as $customer) {
                $distance = $this->getDistance($customer->getParameters(), $this->getClosest($customer)->getParameters());
                $sum += $distances[$customer->getId()] = $distance;
            }

            // choose a new random point using a weighted probability distribution
            $sum = rand(0, $sum);

            foreach ($this->customers as $customer) {

                if (($sum -= $distances[$customer->getId()]) > 0) {
                    continue;
                }

                $groups->add(new CustomerGroup($this->groups->nextKey(), $customer->getParameters()));
                break;
            }
        }

        /**
         * @var CustomerGroup $firstGroup
         */
        $firstGroup = $groups->getObj(0);
        $firstGroup->getCustomers()->merge($this->customers);

        return $groups;
    }

    /**
     * @param BaseArray $p1
     * @param BaseArray $p2
     * @return float
     * @throws InvalidArgumentException
     */
    protected function getDistance(BaseArray $p1, BaseArray $p2)
    {
        if ($p1->requiredBaseClass() != Parameter::class) {
            throw new InvalidArgumentException("Required class for parameters array has to be equal to " . Parameter::class . " but is " . $p1->requiredBaseClass() . ".");
        }

        if ($p2->requiredBaseClass() != Parameter::class) {
            throw new InvalidArgumentException("Required class for parameters array has to be equal to " . Parameter::class . " but is " . $p2->requiredBaseClass() . ".");
        }

        return MathFunctions::euclideanDistance($p1, $p2);

    }

    /**
     * @param Customer $c1
     * @return CustomerGroup|mixed|null
     */
    protected function getClosest(Customer $c1)
    {

        $minDistance = PHP_INT_MAX;
        $closestGroup = $this->groups->getObj(0);

        /**
         * @var CustomerGroup $group
         */
        foreach ($this->groups as $group) {
            $distance = $this->getDistance($c1->getParameters(), $group->getParameters());
            if ($distance < $minDistance) {
                $minDistance = $distance;
                $closestGroup = $group;
            }
        }

        return $closestGroup;
    }

    /**
     * @return bool
     */
    protected function iterate()
    {
        $continue = false;

        // migration storages
        /**
         * @var BaseArray[] $attach
         * @var BaseArray[] $detach
         */
        $detach = $attach = array();

        /**
         * @var CustomerGroup $group
         */
        foreach ($this->groups as $group) {
            $detach[$group->getId()] = $attach[$group->getId()] = new BaseArray(null, Customer::class);
        }

        // calculate proximity amongst points and clusters

        /**
         * @var CustomerGroup $group
         */
        foreach ($this->groups as $group) {

            /**
             * @var Customer $customer
             */
            foreach ($group->getCustomers() as $customer) {

                // find the closest cluster
                $closest = $this->getClosest($customer);

                // move the point from its old cluster to its closest
                if ($closest->getId() !== $group->getId()) {
                    $attach[$closest->getId()]->add($customer);
                    $detach[$group->getId()]->add($customer);
                    $continue = true;
                }
            }
        }

        /**
         * Two foreach cycles are required for right replacing customer group with new one
         *
         * @var CustomerGroup $group
         */
        foreach ($this->groups as $group) {
            $group->mergeCustomers($attach[$group->getId()]);
        }
        foreach ($this->groups as $group) {
            $group->getCustomers()->removeAll($detach[$group->getId()]);
            $group->setParameters($this->updateCentroid($group));
        }

        return $continue;
    }

    /**
     * @param CustomerGroup $group
     * @return BaseArray
     */
    protected function updateCentroid(CustomerGroup $group)
    {

        if (!$count = $group->getCustomers()->size()) {
            return $group->getParameters();
        }

        $newCenter = new BaseArray(null, Parameter::class);
        $centroid = array_fill(0, $this->dimension, 0);

        /**
         * @var Customer $customer
         */
        foreach ($group->getCustomers() as $customer) {
            for ($i = 0; $i < $this->dimension; $i++) {
                $centroid[$i] += $customer->getParameters()->getObj($i);
            }
        }

        for ($i = 0; $i < $this->dimension; $i++) {
            $newCenter->add($centroid->coordinates[$i] / $count);
        }

        return $newCenter;
    }
}