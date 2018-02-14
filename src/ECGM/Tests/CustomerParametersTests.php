<?php
namespace ECGM\Tests;


use ECGM\Controller\CustomerParametersCleaningController;
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

        // Set historical parameters
        $history = new BaseArray(null, Order::class);

        //pps1
        $customerHistoryParameters = new BaseArray(null, CustomerParameter::class);
        $customerHistoryParameters->add(new CustomerParameter(1, 4,$customer, true, 12));
        $customerHistoryParameters->add(new CustomerParameter(2, 5,$customer, true, 7));
        $customerHistoryParameters->add(new CustomerParameter(3, 11,$customer, true, 24));
        $customerHistoryParameters->add(new CustomerParameter(4, 49.652456,$customer));
        $customerHistoryParameters->add(new CustomerParameter(5, 16.259766,$customer));

        $historicalCustomer = new Customer(1, new CustomerGroup(1));
        $historicalCustomer->setParameters($customerHistoryParameters);
        $history->add(new Order(1, $historicalCustomer));

        //pps2
        $customerHistoryParameters = new BaseArray(null, CustomerParameter::class);
        $customerHistoryParameters->add(new CustomerParameter(1, 6,$customer, true, 12));
        $customerHistoryParameters->add(new CustomerParameter(2, 6,$customer, true, 7));
        $customerHistoryParameters->add(new CustomerParameter(3, 12,$customer, true, 24));
        $customerHistoryParameters->add(new CustomerParameter(4, 49.652456,$customer));
        $customerHistoryParameters->add(new CustomerParameter(5, 16.259766,$customer));

        $historicalCustomer = new Customer(1, new CustomerGroup(1));
        $historicalCustomer->setParameters($customerHistoryParameters);
        $history->add(new Order(1, $historicalCustomer));

        //pps3
        $customerHistoryParameters = new BaseArray(null, CustomerParameter::class);
        $customerHistoryParameters->add(new CustomerParameter(1, 8,$customer, true, 12));
        $customerHistoryParameters->add(new CustomerParameter(2, 1,$customer, true, 7));
        $customerHistoryParameters->add(new CustomerParameter(3, 9,$customer, true, 24));
        $customerHistoryParameters->add(new CustomerParameter(4, 49.652456,$customer));
        $customerHistoryParameters->add(new CustomerParameter(5, 16.259766,$customer));

        $historicalCustomer = new Customer(1, new CustomerGroup(1));
        $historicalCustomer->setParameters($customerHistoryParameters);
        $history->add(new Order(1, $historicalCustomer));

        //pps4
        $customerHistoryParameters = new BaseArray(null, CustomerParameter::class);
        $customerHistoryParameters->add(new CustomerParameter(1, 10,$customer, true, 12));
        $customerHistoryParameters->add(new CustomerParameter(2, 2,$customer, true, 7));
        $customerHistoryParameters->add(new CustomerParameter(3, 13,$customer, true, 24));
        $customerHistoryParameters->add(new CustomerParameter(4, 35.320802,$customer));
        $customerHistoryParameters->add(new CustomerParameter(5, 25.138551,$customer));

        $historicalCustomer = new Customer(1, new CustomerGroup(1));
        $historicalCustomer->setParameters($customerHistoryParameters);
        $history->add(new Order(1, $historicalCustomer));

        //pps5
        $customerHistoryParameters = new BaseArray(null, CustomerParameter::class);
        $customerHistoryParameters->add(new CustomerParameter(1, 11,$customer, true, 12));
        $customerHistoryParameters->add(new CustomerParameter(2, 6,$customer, true, 7));
        $customerHistoryParameters->add(new CustomerParameter(3, 10,$customer, true, 24));
        $customerHistoryParameters->add(new CustomerParameter(4, 49.652456,$customer));
        $customerHistoryParameters->add(new CustomerParameter(5, 16.259766,$customer));

        $historicalCustomer = new Customer(1, new CustomerGroup(1));
        $historicalCustomer->setParameters($customerHistoryParameters);
        $history->add(new Order(1, $historicalCustomer));

        //pps6
        $customerHistoryParameters = new BaseArray(null, CustomerParameter::class);
        $customerHistoryParameters->add(new CustomerParameter(1, 12,$customer, true, 12));
        $customerHistoryParameters->add(new CustomerParameter(2, 1,$customer, true, 7));
        $customerHistoryParameters->add(new CustomerParameter(3, 23,$customer, true, 24));
        $customerHistoryParameters->add(new CustomerParameter(4, 49.652456,$customer));
        $customerHistoryParameters->add(new CustomerParameter(5, 16.259766,$customer));

        $historicalCustomer = new Customer(1, new CustomerGroup(1));
        $historicalCustomer->setParameters($customerHistoryParameters);
        $history->add(new Order(1, $historicalCustomer));

        $customer->setHistory($history);

        $customerParametersController = new CustomerParametersCleaningController();

        $cleanedCustomer = $customerParametersController->cleanCustomer($customer);

        $this->assertEquals(8, $cleanedCustomer->getParameters()->size());

        echo "Size OK.\n\n";

        $expected = array(0.866, -0.5, -0.975, -0.223, 0.259, -0.966, 49.652, 16.260);

        for ($i = 0; $i < 8; $i++){
            echo $i;
            $this->assertEquals($expected[$i], round($cleanedCustomer->getHistory()->getObj(0)->getCustomer()->getParameters()->getObj($i)->getValue(), 3));
            echo " - OK\n";
        }
        echo "\npps1 OK.\n\n";

        $expected = array(0, -1, -0.782, 0.623, 0, -1, 49.652, 16.260);

        for ($i = 0; $i < 8; $i++){
            echo $i;
            $this->assertEquals($expected[$i], round($cleanedCustomer->getHistory()->getObj(1)->getCustomer()->getParameters()->getObj($i)->getValue(), 3));
            echo " - OK\n";
        }
        echo "\npps2 OK.\n\n";

        echo "\nHistorical parameters OK.\n\n";

        $expected = array(-0.228, 0.061, 0, 0.341, 0.158, -0.59, 49.401, 16.509);

        for ($i = 0; $i < 8; $i++){
            echo $i;
            $this->assertEquals($expected[$i], round($cleanedCustomer->getParameters()->getObj($i)->getValue(), 3));
            echo " - OK\n";
        }

        echo "\nMerged parameters OK.\n\n";
    }

}