<?php

namespace ECGM\tests;


use ECGM\Controller\CustomerGroupingController;
use ECGM\Int\CustomerGroupingInterface;
use ECGM\Int\GroupingImplementationInterface;
use ECGM\Int\GroupingValidationInterface;
use ECGM\Model\BaseArray;
use ECGM\Model\Customer;
use ECGM\Model\Parameter;
use ECGM\Util\KmeansPlusPlus;
use ECGM\Util\SilhouetteAnalysis;

/**
 * Class CustomerGroupingTests
 * @package ECGM\tests
 */
class CustomerGroupingTests extends ECGMTest
{

    protected $customerNum;
    protected $parameterDimension;
    protected $initialK;
    protected $parameterRules;

    /**
     * CustomerGroupingTests constructor.
     * @param int $customerNum
     * @param int $parameterDimension
     * @param int $initialK
     * @param null $name
     * @param array $data
     * @param string $dataName
     */
    public function __construct($customerNum = 1000, $parameterDimension = 10, $initialK = 2, $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->customerNum = $customerNum;
        $this->parameterDimension = $parameterDimension;
        $this->initialK = $initialK;

        for ($i = 0; $i < $this->parameterDimension; $i++) {
            $min = rand(1, 303);
            $max = rand($min + 1, $min + 600);
            $this->parameterRules[] = array('min' => $min, 'max' => $max);
        }
    }

    /**
     * @param CustomerGroupingInterface|null $customerGrouping
     * @throws \ECGM\Exceptions\InvalidArgumentException
     * @throws \ECGM\Exceptions\UndefinedException
     */
    public function testComplete(CustomerGroupingInterface $customerGrouping = null)
    {
        if (is_null($customerGrouping)) {
            $customerGrouping = new CustomerGroupingController($this->parameterDimension, $this->initialK, true);
        }

        echo "\n\nComplete test\n\n";

        $customer = $this->getCustomers();

        $groupNum = $customerGrouping->groupCustomers($customer)->size();

        echo "Group number $groupNum\n\n";
        echo self::$splitLine;
    }

    /**
     * @throws \ECGM\Exceptions\InvalidArgumentException
     * @throws \ECGM\Exceptions\UndefinedException
     */
    public function testGrouping(GroupingImplementationInterface $groupingImplementation = null)
    {

        if (is_null($groupingImplementation)) {
            $groupingImplementation = new KmeansPlusPlus($this->parameterDimension);
        }

        echo "\n\nGrouping test\n\n";

        $customer = $this->getCustomers();
        $groupingImplementation->setCustomers($customer);
        $groupingImplementation->solve($this->initialK);
        echo "Complete\n\n";
        echo self::$splitLine;
    }

    /**
     * @param GroupingImplementationInterface|null $groupingImplementation
     * @param GroupingValidationInterface|null $groupingValidation
     * @throws \ECGM\Exceptions\InvalidArgumentException
     * @throws \ECGM\Exceptions\UndefinedException
     */
    public function testSilhouette(GroupingImplementationInterface $groupingImplementation = null, GroupingValidationInterface $groupingValidation = null)
    {

        if (is_null($groupingImplementation)) {
            $groupingImplementation = new KmeansPlusPlus($this->parameterDimension);
        }

        if (is_null($groupingValidation)) {
            $groupingValidation = new SilhouetteAnalysis();
        }


        echo "\n\nSilhouette test\n\n";

        $customer = $this->getCustomers();
        $groupingImplementation->setCustomers($customer);
        $time = microtime(true);
        echo $time . "\n";
        $groups = $groupingImplementation->solve($this->initialK);
        echo "Kmeans: " . (microtime(true) - $time) . "\n";
        $time = microtime(true);

        $silhouette = $groupingValidation->getGroupingScore($groups);
        echo "Silhouette: " . (microtime(true) - $time) . "\n";

        echo "Average silhouette width: " . $silhouette;
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

}