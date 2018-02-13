<?php
namespace ECGM\Tests;


use ECGM\Controller\CustomerParametersController;
use ECGM\Model\BaseArray;
use ECGM\Model\Customer;
use ECGM\Model\CustomerGroup;
use ECGM\Model\CustomerParameter;
use PHPUnit\Framework\TestCase;

class CustomerParametersControllerTests extends TestCase
{

    public function testCustomerParametersTransformation(){

        echo "\n\nTesting customers parameters transformation\n\n";

        $customer = new Customer(1, new CustomerGroup(1));

        $customerParameters = new BaseArray(null, CustomerParameter::class);

        $customerParameters->add(new CustomerParameter(1, 4,$customer, true, 12), 1);
        $customerParameters->add(new CustomerParameter(2, 5,$customer, true, 7), 2);
        $customerParameters->add(new CustomerParameter(3, 11,$customer, true, 24), 3);
        $customerParameters->add(new CustomerParameter(4, 49.652456,$customer), 4);
        $customerParameters->add(new CustomerParameter(5, 16.259766,$customer), 5);

        $customer->setParameters($customerParameters);

        $customerParametersController = new CustomerParametersController();

        $cleanedCustomer = $customerParametersController->cleanCustomer($customer);

        $expected = array('1X' => 0.866, '1Y' => -0.5, '2X' => -0.975, '2Y' => -0.223, '3X' => 0.259, '3Y' => -0.966, '4' => 49.652, '5' => 16.260);

        $this->assertEquals(8, $cleanedCustomer->getParameters()->size());

        foreach ($expected as $key => $value){
            echo $key;
            $this->assertEquals($value, round($cleanedCustomer->getParameters()->getObj($key)->getValue(), 3));
            echo " - OK\n";
        }

    }

}