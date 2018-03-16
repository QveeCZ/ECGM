<?php

namespace ECGM\Tests;


use ECGM\Controller\CustomerGroupingController;
use ECGM\Model\BaseArray;
use ECGM\Model\Customer;
use ECGM\Model\Parameter;
use ECGM\Util\KmeansPlusPlus;
use ECGM\Util\SilhouetteAnalysis;

/**
 * Class CustomerGroupingTests
 * @package ECGM\tests
 */
class CustomerGroupingTests extends MiscTests
{

    protected $parameterDimension = 10;
    protected $customerNum = 10000;
    protected $initialK = 2;
    protected $parameterRules;


    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        for ($i = 0; $i < $this->parameterDimension; $i++) {
            $min = rand(1, 303);
            $max = rand($min + 1, $min + 600);
            $this->parameterRules[] = array('min' => $min, 'max' => $max);
        }
    }

    /**
     * @throws \ECGM\Exceptions\InvalidArgumentException
     * @throws \ECGM\Exceptions\LogicalException
     * @throws \ECGM\Exceptions\UndefinedException
     */
    public function testComplete()
    {
        echo "\n\nComplete test\n\n";

        $customerGrouping = new CustomerGroupingController($this->parameterDimension, $this->initialK, true);

        $customer = $this->getCustomers();

        $groupNum = $customerGrouping->groupCustomers($customer)->size();

        echo "Group number $groupNum\n\n";
        echo self::$splitLine;
    }

    /**
     * @return BaseArray
     * @throws \ECGM\Exceptions\InvalidArgumentException
     */
    protected function getCustomers()
    {
        $customers = new BaseArray(null, Customer::class);
        for ($i = 0; $i < $this->customerNum; $i++) {
            $customers->add($this->getCustomer());
        }
        return $customers;
    }

    /**
     * @return Customer
     * @throws \ECGM\Exceptions\InvalidArgumentException
     */
    protected function getCustomer()
    {
        $customer = new Customer(uniqid(), null);
        $customer = $this->setParameters($customer);
        return $customer;
    }

    /**
     * @param Customer $customer
     * @return Customer
     * @throws \ECGM\Exceptions\InvalidArgumentException
     */
    protected function setParameters(Customer $customer)
    {
        for ($i = 0; $i < $this->parameterDimension; $i++) {
            $customer->addParameter(new Parameter(uniqid(), rand($this->parameterRules[$i]['min'], $this->parameterRules[$i]['max'])));
        }
        return $customer;
    }

    /**
     * @throws \ECGM\Exceptions\InvalidArgumentException
     * @throws \ECGM\Exceptions\UndefinedException
     */
    public function testGrouping()
    {
        echo "\n\nGrouping test\n\n";

        $kmeansPlusPlus = new KmeansPlusPlus($this->parameterDimension);
        $customer = $this->getCustomers();
        $kmeansPlusPlus->setCustomers($customer);
        $kmeansPlusPlus->solve($this->initialK);
        echo "Complete\n\n";
        echo self::$splitLine;
    }

    /**
     * @throws \ECGM\Exceptions\InvalidArgumentException
     * @throws \ECGM\Exceptions\LogicalException
     * @throws \ECGM\Exceptions\UndefinedException
     */
    public function testSilhouette()
    {

        echo "\n\nSilhouette test\n\n";

        $kmeansPlusPlus = new KmeansPlusPlus($this->parameterDimension);
        $customer = $this->getCustomers();
        $kmeansPlusPlus->setCustomers($customer);
        $time = microtime(true);
        echo $time . "\n";
        $groups = $kmeansPlusPlus->solve($this->initialK);
        echo "Kmeans: " . (microtime(true) - $time) . "\n";
        $time = microtime(true);

        $silhouetteAnalysis = new SilhouetteAnalysis();
        $silhouette = $silhouetteAnalysis->getGroupingScore($groups);
        echo "Silhouette: " . (microtime(true) - $time) . "\n";

        echo "Average silhouette width: " . $silhouette;
        echo self::$splitLine;
    }

}