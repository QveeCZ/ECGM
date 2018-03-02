<?php

namespace ECGM\Tests;


use ECGM\Controller\CustomerGroupingController;
use ECGM\Model\BaseArray;
use ECGM\Model\Customer;
use ECGM\Model\Parameter;
use ECGM\Util\KmeansPlusPlus;
use ECGM\Util\SilhouetteAnalysis;
use PHPUnit\Framework\TestCase;

class CustomerGroupingTests extends TestCase
{

    private $parameterDimension = 10;
    private $customerNum = 1000;
    private $initialK = 2;
    private $parameterRules;


    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        for ($i = 0; $i < $this->parameterDimension; $i++) {
            $min = rand(1, 303);
            $max = rand($min + 1, $min + 600);
            $this->parameterRules[] = array('min' => $min, 'max' => $max);
        }
    }

    public function testComplete()
    {
        echo "\n\nComplete test\n\n";

        $customerGrouping = new CustomerGroupingController($this->parameterDimension, $this->initialK, true);

        $customer = $this->getCustomers();

        $groupNum = $customerGrouping->groupCustomers($customer)->size();

        echo "Group number $groupNum\n\n";
    }

    public function testGrouping()
    {
        echo "\n\nGrouping test\n\n";

        $kmeansPlusPlus = new KmeansPlusPlus($this->parameterDimension);
        $customer = $this->getCustomers();
        $kmeansPlusPlus->setCustomers($customer);
        $groups = $kmeansPlusPlus->solve($this->initialK);
        echo $groups->__toString();
    }

    protected function getCustomers()
    {
        $customers = new BaseArray(null, Customer::class);
        for ($i = 0; $i < $this->customerNum; $i++) {
            $customers->add($this->getCustomer());
        }
        return $customers;
    }

    protected function getCustomer()
    {
        $customer = new Customer(uniqid(), null);
        $customer = $this->setParameters($customer);
        return $customer;
    }

    protected function setParameters(Customer $customer)
    {
        for ($i = 0; $i < $this->parameterDimension; $i++) {
            $customer->addParameter(new Parameter(uniqid(), rand($this->parameterRules[$i]['min'], $this->parameterRules[$i]['max'])));
        }
        return $customer;
    }

    public function testSilhouette()
    {

        echo "\n\nSilhouette test\n\n";

        $kmeansPlusPlus = new KmeansPlusPlus($this->parameterDimension);
        $customer = $this->getCustomers();
        $kmeansPlusPlus->setCustomers($customer);
        $groups = $kmeansPlusPlus->solve($this->initialK);

        $silhouetteAnalysis = new SilhouetteAnalysis();
        $silhouette = $silhouetteAnalysis->getAverageSilhouetteWidth($groups);

        echo "Average silhouette width: " . $silhouette;
    }

}