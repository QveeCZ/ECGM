<?php

namespace ECGM\Controller;

use ECGM\Exceptions\InvalidArgumentException;
use ECGM\Model\BaseArray;
use ECGM\Model\Customer;
use ECGM\Model\CustomerGroup;
use ECGM\Util\KmeansPlusPlus;
use ECGM\Util\SilhouetteAnalysis;

class CustomerGroupingController
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
     * CustomerGroupingController constructor.
     * @param integer $dimension
     * @param integer $initK
     * @param boolean $verbose
     * @throws InvalidArgumentException
     */
    public function __construct($dimension, $initK, $verbose = false)
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
            if($this->verbose){
                echo "K-" . $this->k . ".\n";
            }
            $groups = $this->getGroups($this->k, $customers, $initialGroups);
            $prevSilhouette = $silhouette;
            $silhouette = $this->getSilhouette($groups);
            $this->k += 1;
        }

        if($this->verbose){
            echo "Silhouette $silhouette, Prev silhouette $prevSilhouette.\n";
        }

        return $groups;
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
        $kmeansPlusPlus = new KmeansPlusPlus($this->dimension);

        if ($groups->size() > 0) {
            $kmeansPlusPlus->setGroups($groups);
        }

        $kmeansPlusPlus->setCustomers($customers);

        return $kmeansPlusPlus->solve($k);
    }

    /**
     * @param BaseArray $groups
     * @return float|int
     */
    protected function getSilhouette(BaseArray $groups)
    {
        $groups = new BaseArray($groups, CustomerGroup::class);
        $silhouetteAnalysis = new SilhouetteAnalysis();
        return $silhouetteAnalysis->getAverageSilhouetteWidth($groups);
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

}