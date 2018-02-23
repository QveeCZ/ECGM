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
     * CustomerGroupingController constructor.
     * @param integer $dimension
     * @param integer $initK
     * @throws InvalidArgumentException
     */
    public function __construct($dimension, $initK)
    {
        if (!is_numeric($dimension) || $dimension < 1) {
            throw new InvalidArgumentException("Dimension $dimension is invalid.");
        }

        $this->k = $initK;
    }

    /**
     * @param BaseArray $customers
     * @param BaseArray|null $initialGroups
     * @return BaseArray|mixed|null
     */
    public function groupCustomers(BaseArray $customers, BaseArray $initialGroups = null)
    {

        $prevSilhouette = PHP_INT_MIN;
        $silhouette = 0;

        $groups = new BaseArray(null, CustomerGroup::class);

        while ($prevSilhouette > $silhouette) {
            $groups = $this->getGroups($customers, $initialGroups);
            $prevSilhouette = $silhouette;
            $silhouette = $this->getSilhouette($groups);
            $this->k += 1;
        }

        return $groups;
    }

    /**
     * @param BaseArray $customers
     * @param BaseArray|null $groups
     * @return BaseArray|mixed|null
     */
    protected function getGroups(BaseArray $customers, BaseArray $groups = null)
    {
        $customers = new BaseArray($customers, Customer::class);
        $groups = new BaseArray($groups, CustomerGroup::class);
        $kmeansPlusPlus = new KmeansPlusPlus($this->dimension);

        if ($groups->size() > 0) {
            $kmeansPlusPlus->setGroups($groups);
        }

        $kmeansPlusPlus->setCustomers($customers);

        return $kmeansPlusPlus->solve($this->k);
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