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

        $customerParameters->add(new CustomerParameter(1, 4,$customer, true, 12));
        $customerParameters->add(new CustomerParameter(2, 5,$customer, true, 7));
        $customerParameters->add(new CustomerParameter(3, 11,$customer, true, 24));
        $customerParameters->add(new CustomerParameter(4, 49.652456,$customer));
        $customerParameters->add(new CustomerParameter(5, 16.259766,$customer));

        $customer->setParameters($customerParameters);

        $customerParametersController = new CustomerParametersController();

        $cleanedCustomer = $customerParametersController->cleanCustomer($customer);

        $expected = array(0.866, -0.5, -0.975, -0.223, 0.259, -0.966, 49.652, 16.260);

        $this->assertEquals(8, $cleanedCustomer->getParameters()->size());

        echo "Size OK.\n\n";

        for ($i = 0; $i < 8; $i++){
            echo $i;
            $this->assertEquals($expected[$i], round($cleanedCustomer->getParameters()->getObj($i)->getValue(), 3));
            echo " - OK\n";
        }

    }

}