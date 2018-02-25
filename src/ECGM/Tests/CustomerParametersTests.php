<?php

namespace ECGM\Tests;


use ECGM\Controller\CustomerParametersCleaningController;
use ECGM\Model\BaseArray;
use ECGM\Model\Customer;
use ECGM\Model\CustomerGroup;
use ECGM\Model\Order;
use ECGM\Model\Parameter;
use PHPUnit\Framework\TestCase;

class CustomerParametersTests extends TestCase
{

    /**
     * @var Customer
     */
    private $customer;
    /**
     * @var Customer
     */
    private $cleanedCustomer;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        $this->customer = $this->prepareTestCustomer();
        $this->cleanedCustomer = $this->getCleanedCustomer($this->customer);

        parent::__construct($name, $data, $dataName);
    }

    /**
     * @return Customer
     */
    private function prepareTestCustomer()
    {

        $customer = new Customer(1, new CustomerGroup(1));

        // Set historical parameters
        $history = new BaseArray(null, Order::class);

        //pps1
        $customerHistoryParameters = new BaseArray(null, Parameter::class);
        $customerHistoryParameters->add(new Parameter(1, 4, true, 12));
        $customerHistoryParameters->add(new Parameter(2, 5, true, 7));
        $customerHistoryParameters->add(new Parameter(3, 11, true, 24));
        $customerHistoryParameters->add(new Parameter(4, 49.652456));
        $customerHistoryParameters->add(new Parameter(5, 16.259766));

        $history->add(new Order(1, $customerHistoryParameters, new \DateTime()));

        //pps2
        $customerHistoryParameters = new BaseArray(null, Parameter::class);
        $customerHistoryParameters->add(new Parameter(1, 6, true, 12));
        $customerHistoryParameters->add(new Parameter(2, 6, true, 7));
        $customerHistoryParameters->add(new Parameter(3, 12, true, 24));
        $customerHistoryParameters->add(new Parameter(4, 49.652456));
        $customerHistoryParameters->add(new Parameter(5, 16.259766));

        $history->add(new Order(1, $customerHistoryParameters, new \DateTime()));

        //pps3
        $customerHistoryParameters = new BaseArray(null, Parameter::class);
        $customerHistoryParameters->add(new Parameter(1, 8, true, 12));
        $customerHistoryParameters->add(new Parameter(2, 1, true, 7));
        $customerHistoryParameters->add(new Parameter(3, 9, true, 24));
        $customerHistoryParameters->add(new Parameter(4, 49.652456));
        $customerHistoryParameters->add(new Parameter(5, 16.259766));

        $history->add(new Order(1, $customerHistoryParameters, new \DateTime()));

        //pps4
        $customerHistoryParameters = new BaseArray(null, Parameter::class);
        $customerHistoryParameters->add(new Parameter(1, 10, true, 12));
        $customerHistoryParameters->add(new Parameter(2, 2, true, 7));
        $customerHistoryParameters->add(new Parameter(3, 13, true, 24));
        $customerHistoryParameters->add(new Parameter(4, 35.320802));
        $customerHistoryParameters->add(new Parameter(5, 25.138551));

        $history->add(new Order(1, $customerHistoryParameters, new \DateTime()));

        //pps5
        $customerHistoryParameters = new BaseArray(null, Parameter::class);
        $customerHistoryParameters->add(new Parameter(1, 11, true, 12));
        $customerHistoryParameters->add(new Parameter(2, 6, true, 7));
        $customerHistoryParameters->add(new Parameter(3, 10, true, 24));
        $customerHistoryParameters->add(new Parameter(4, 49.652456));
        $customerHistoryParameters->add(new Parameter(5, 16.259766));

        $history->add(new Order(1, $customerHistoryParameters, new \DateTime()));

        //pps6
        $customerHistoryParameters = new BaseArray(null, Parameter::class);
        $customerHistoryParameters->add(new Parameter(1, 12, true, 12));
        $customerHistoryParameters->add(new Parameter(2, 1, true, 7));
        $customerHistoryParameters->add(new Parameter(3, 23, true, 24));
        $customerHistoryParameters->add(new Parameter(4, 49.652456));
        $customerHistoryParameters->add(new Parameter(5, 16.259766));

        $history->add(new Order(1, $customerHistoryParameters, new \DateTime()));

        $customer->setHistory($history);

        return $customer;
    }

    /**
     * @param Customer $customer
     * @return Customer
     */
    private function getCleanedCustomer(Customer $customer)
    {
        $customerParametersController = new CustomerParametersCleaningController();

        $this->cleanedCustomer = $customerParametersController->cleanCustomer($customer);

        return $this->cleanedCustomer;
    }

    public function testCustomerParametersSize()
    {
        echo "Size test\n\n";

        $this->assertEquals(8, $this->cleanedCustomer->getParameters()->size());

        echo "Size OK.";
        echo MiscTests::$splitLine;
    }

    public function testHistoryCleaning()
    {
        echo "History cleaning test\n\n";

        $numbers = range(0, 5);

        $expecteds = array();

        $expecteds[] = array(0.866, -0.5, -0.975, -0.223, 0.259, -0.966, 49.652, 16.260);
        $expecteds[] = array(0, -1, -0.782, 0.623, 0, -1, 49.652, 16.260);
        $expecteds[] = array(-0.866, -0.5, 0.782, 0.623, 0.707, -0.707, 49.652, 16.260);
        $expecteds[] = array(-0.866, 0.5, 0.975, -0.223, -0.259, -0.966, 35.321, 25.139);
        $expecteds[] = array(-0.5, 0.866, -0.782, 0.623, 0.5, -0.866, 49.652, 16.260);
        $expecteds[] = array(0, 1, 0.782, 0.623, -0.259, 0.966, 49.652, 16.260);


        foreach ($numbers as $number) {

            echo "Testing pps" . ($number + 1) . "\n\n";

            for ($j = 0; $j < 8; $j++) {
                echo $j;
                $expected = $expecteds[$number];
                $this->assertEquals($expected[$j], round($this->cleanedCustomer->getHistory()->getObj($number)->getCustomerParameters()->getObj($j)->getValue(), 3));
                echo " - OK\n";
            }
            echo "\npps" . ($number + 1) . " OK.\n\n";
        }


        echo "Historical parameters OK.";
        echo MiscTests::$splitLine;
    }

    public function testCustomerParametersTransformation()
    {

        echo "Parameter merging test\n\n";

        $expected = array(-0.228, 0.061, 0, 0.341, 0.158, -0.59, 49.401, 16.509);

        for ($j = 0; $j < 8; $j++) {
            echo $j;
            $this->assertEquals($expected[$j], round($this->cleanedCustomer->getParameters()->getObj($j)->getValue(), 3));
            echo " - OK\n";
        }

        echo "\nMerged parameters OK.";
        echo MiscTests::$splitLine;
    }

}