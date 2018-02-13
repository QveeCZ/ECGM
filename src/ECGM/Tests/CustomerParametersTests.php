<?php
namespace ECGM\Tests;


use ECGM\Controller\CustomerParametersController;
use ECGM\Model\BaseArray;
use ECGM\Model\Customer;
use ECGM\Model\CustomerGroup;
use ECGM\Model\CustomerParameter;
use ECGM\Model\Order;
use PHPUnit\Framework\TestCase;

class CustomerParametersTests extends TestCase
{

    public function testCustomerParametersTransformation(){

        echo "\n\nTesting customers parameters transformation\n\n";

        $customer = new Customer(1, new CustomerGroup(1));

        //Set current parameters
        $customerParameters = new BaseArray(null, CustomerParameter::class);

        $customerParameters->add(new CustomerParameter(1, 4,$customer, true, 12));
        $customerParameters->add(new CustomerParameter(2, 5,$customer, true, 7));
        $customerParameters->add(new CustomerParameter(3, 11,$customer, true, 24));
        $customerParameters->add(new CustomerParameter(4, 49.652456,$customer));
        $customerParameters->add(new CustomerParameter(5, 16.259766,$customer));

        $customer->setParameters($customerParameters);

        // Set historical parameters
        $customerHistoryParameters = new BaseArray(null, CustomerParameter::class);

        $customerHistoryParameters->add(new CustomerParameter(1, 6,$customer, true, 12));
        $customerHistoryParameters->add(new CustomerParameter(2, 6,$customer, true, 7));
        $customerHistoryParameters->add(new CustomerParameter(3, 12,$customer, true, 24));
        $customerHistoryParameters->add(new CustomerParameter(4, 49.652456,$customer));
        $customerHistoryParameters->add(new CustomerParameter(5, 16.259766,$customer));

        $historicalCustomer = new Customer(1, new CustomerGroup(1));
        $historicalCustomer->setParameters($customerHistoryParameters);

        $history = new BaseArray(null, Order::class);
        $history->add(new Order(1, $historicalCustomer));

        $customer->setHistory($history);

        $customerParametersController = new CustomerParametersController();

        $cleanedCustomer = $customerParametersController->cleanCustomer($customer);

        $this->assertEquals(8, $cleanedCustomer->getParameters()->size());

        echo "Size OK.\n\n";

        $expected = array(0.866, -0.5, -0.975, -0.223, 0.259, -0.966, 49.652, 16.260);

        for ($i = 0; $i < 8; $i++){
            echo $i;
            $this->assertEquals($expected[$i], round($cleanedCustomer->getParameters()->getObj($i)->getValue(), 3));
            echo " - OK\n";
        }

        echo "\nCurrent parameters OK.\n\n";

        $expected = array(0, -1, -0.782, 0.623, 0, -1, 49.652, 16.260);

        for ($i = 0; $i < 8; $i++){
            echo $i;
            $this->assertEquals($expected[$i], round($cleanedCustomer->getHistory()->getObj(0)->getCustomer()->getParameters()->getObj($i)->getValue(), 3));
            echo " - OK\n";
        }

        echo "\nHistorical parameters OK.\n\n";
    }

}