<?php

namespace ECGM\Tests;


use ECGM\Model\BaseArray;
use ECGM\Model\Customer;
use ECGM\Model\Parameter;
use ECGM\Util\KmeansPlusPlus;
use PHPUnit\Framework\TestCase;

class CustomerGroupingTests extends TestCase
{

    private $parameterDimension = 2;
    private $customerNum = 20;
    private $initialK = 2;
    private $parameterRules;


    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        for ($i = 0; $i < $this->parameterDimension; $i++) {
            $min = rand(0, 60);
            $max = rand($min + 1, $min + 60);
            $this->parameterRules[] = array('min' => $min, 'max' => $max);
        }
    }

    public function testGrouping()
    {
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

}