<?php

namespace ECGM\Tests;


use ECGM\Controller\CustomerStrategyController;
use ECGM\Controller\DealerStrategyController;
use ECGM\Model\BaseArray;
use ECGM\Model\CurrentProduct;
use ECGM\Model\Customer;
use ECGM\Model\CustomerGroup;
use ECGM\Model\Order;
use ECGM\Model\OrderProduct;
use ECGM\Model\Parameter;
use ECGM\Model\Product;
use ECGM\Model\ProductComplement;

class StrategyTests extends MiscTests
{

    public function testDealerStrategy()
    {
        echo "Dealer strategy tests\n\n";

        $products = new BaseArray(null, CurrentProduct::class);

        $products->add(new CurrentProduct(1, 100, 30, 200));
        $products->add(new CurrentProduct(2, 200, 30, 161));
        $products->add(new CurrentProduct(3, 300, 30, 150));
        $products->add(new CurrentProduct(4, 400, 30, 400));

        $strategyController = new DealerStrategyController();

        $strategy = $strategyController->getDealerStrategy($products);

        $expected = array(1 => 200 / 911, 2 => 161 / 911, 3 => 150 / 911, 4 => 400 / 911);

        $this->assertEquals(count($expected), count($strategy));

        for ($j = 1; $j <= count($expected); $j++) {
            echo $j;
            $this->assertEquals(round($expected[$j], 3), $strategy[$j], 3);
            echo " - OK\n";
        }

        echo self::$splitLine;
    }

    public function testCustomerStrategy()
    {

        echo "Customer strategy test\n\n";


        $currentProducts = new BaseArray(null, CurrentProduct::class);

        $currentProducts->add(new CurrentProduct(1, 600, 1000, 700));
        $currentProducts->add(new CurrentProduct(2, 250, 1000, 700));
        $currentProducts->add(new CurrentProduct(3, 900, 1000, 700));
        $currentProducts->add(new CurrentProduct(4, 1100, 1000, 700));

        $strategyController = new CustomerStrategyController(2);

        $strategy = $strategyController->getCustomerStrategy($this->getCustomer(), $currentProducts);


        $expected = array(1 => 0.219, 2 => 0.475, 3 => 0.162, 4 => 0.144);

        $this->assertEquals(count($expected), count($strategy));

        for ($j = 1; $j <= count($expected); $j++) {
            echo $j;
            $this->assertEquals(round($expected[$j], 3), $strategy[$j], 3);
            echo " - OK\n";
        }

        echo self::$splitLine;

    }

    /**
     * @return Customer
     */
    private function getCustomer()
    {

        $tempProductsBase = new BaseArray(null, Product::class);

        $tempProductsBase->add(new CurrentProduct(1, 600, 1000, 700));
        $tempProductsBase->add(new CurrentProduct(2, 250, 1000, 700));
        $tempProductsBase->add(new CurrentProduct(3, 900, 1000, 700));
        $tempProductsBase->add(new CurrentProduct(4, 1100, 1000, 700));

        $customerOrders = new BaseArray(null, Order::class);

        //O1
        $customerOrder = new Order(1, new BaseArray(null, Parameter::class), new \DateTime);
        $tempProducts = new BaseArray($tempProductsBase, Product::class);
        $tempProducts->getObj(0)->setPrice(600);
        $tempProducts->getObj(1)->setPrice(300);
        $tempProducts->getObj(2)->setPrice(900);
        $tempProducts->getObj(3)->setPrice(1500);
        $customerOrder->addProduct(new OrderProduct($tempProducts->getObj(1), $customerOrder, 2));
        $customerOrder->addProduct(new OrderProduct($tempProducts->getObj(2), $customerOrder, 1));

        $customerOrders->add($customerOrder);

        //O2
        $customerOrder = new Order(1, new BaseArray(null, Parameter::class), new \DateTime);
        $tempProducts = new BaseArray($tempProductsBase, Product::class);
        $tempProducts->getObj(0)->setPrice(400);
        $tempProducts->getObj(1)->setPrice(400);
        $tempProducts->getObj(2)->setPrice(900);
        $tempProducts->getObj(3)->setPrice(1500);
        $customerOrder->addProduct(new OrderProduct($tempProducts->getObj(0), $customerOrder, 3));
        $customerOrder->addProduct(new OrderProduct($tempProducts->getObj(2), $customerOrder, 1));

        $customerOrders->add($customerOrder);

        //O3
        $customerOrder = new Order(3, new BaseArray(null, Parameter::class), new \DateTime);
        $tempProducts = new BaseArray($tempProductsBase, Product::class);
        $tempProducts->getObj(0)->setPrice(600);
        $tempProducts->getObj(1)->setPrice(300);

        $tempProducts->getObj(2)->setPrice(900);
        $tempProducts->getObj(3)->setPrice(1500);
        //Add complements to p2
        $complements = new BaseArray(null, ProductComplement::class);
        $complements->add(new ProductComplement($tempProducts->getObj(3)));

        $tempProducts->getObj(1)->setExpiration(-1);
        $tempProducts->getObj(1)->setComplements($complements);


        $customerOrder->addProduct(new OrderProduct($tempProducts->getObj(1), $customerOrder, 1));
        $customerOrder->addProduct(new OrderProduct($tempProducts->getObj(2), $customerOrder, 2));

        $customerOrders->add($customerOrder);

        //O4
        $customerOrder = new Order(4, new BaseArray(null, Parameter::class), new \DateTime);
        $tempProducts = new BaseArray($tempProductsBase, Product::class);

        $tempProducts->getObj(1)->setExpiration(1000);
        $tempProducts->getObj(0)->setPrice(600);
        $tempProducts->getObj(1)->setPrice(250);
        $tempProducts->getObj(2)->setPrice(900);
        $tempProducts->getObj(3)->setPrice(1100);
        $customerOrder->addProduct(new OrderProduct($tempProducts->getObj(1), $customerOrder, 3));

        $customerOrders->add($customerOrder);


        $customer = new Customer(uniqid());
        $customer->setHistory($customerOrders);


        //Customer group history

        $customer->setGroup($this->getGroupHistory());

        return $customer;
    }

    /**
     * @return CustomerGroup
     */
    private function getGroupHistory()
    {


        $tempProducts = new BaseArray(null, Product::class);

        $tempProducts->add(new CurrentProduct(1, 600, 1000, 700));
        $tempProducts->add(new CurrentProduct(2, 250, 1000, 700));
        $tempProducts->add(new CurrentProduct(3, 900, 1000, 700));
        $tempProducts->add(new CurrentProduct(4, 1000, 1000, 700));

        $customerOrders = new BaseArray(null, Order::class);

        //O5
        $customerOrder = new Order(5, new BaseArray(null, Parameter::class), new \DateTime);
        $tempProducts = new BaseArray($tempProducts, Product::class);
        $tempProducts->getObj(0)->setPrice(600);
        $tempProducts->getObj(1)->setPrice(300);
        $tempProducts->getObj(2)->setPrice(750);
        $tempProducts->getObj(3)->setPrice(1500);
        $customerOrder->addProduct(new OrderProduct($tempProducts->getObj(0), $customerOrder, 2));
        $customerOrder->addProduct(new OrderProduct($tempProducts->getObj(1), $customerOrder, 3));
        $customerOrder->addProduct(new OrderProduct($tempProducts->getObj(2), $customerOrder, 2));

        $customerOrders->add($customerOrder);

        //O6
        $customerOrder = new Order(6, new BaseArray(null, Parameter::class), new \DateTime);
        $tempProducts = new BaseArray($tempProducts, Product::class);
        $tempProducts->getObj(0)->setPrice(600);
        $tempProducts->getObj(1)->setPrice(300);
        $tempProducts->getObj(2)->setPrice(750);
        $tempProducts->getObj(3)->setPrice(1500);
        $customerOrder->addProduct(new OrderProduct($tempProducts->getObj(1), $customerOrder, 1));
        $customerOrder->addProduct(new OrderProduct($tempProducts->getObj(2), $customerOrder, 1));
        $customerOrder->addProduct(new OrderProduct($tempProducts->getObj(3), $customerOrder, 1));

        $customerOrders->add($customerOrder);

        //O7
        $customerOrder = new Order(7, new BaseArray(null, Parameter::class), new \DateTime);
        $tempProducts = new BaseArray($tempProducts, Product::class);
        $tempProducts->getObj(0)->setPrice(400);
        $tempProducts->getObj(1)->setPrice(400);
        $tempProducts->getObj(2)->setPrice(900);
        $tempProducts->getObj(3)->setPrice(1500);
        $customerOrder->addProduct(new OrderProduct($tempProducts->getObj(0), $customerOrder, 2));

        $customerOrders->add($customerOrder);

        //O8
        $customerOrder = new Order(8, new BaseArray(null, Parameter::class), new \DateTime);
        $tempProducts = new BaseArray($tempProducts, Product::class);
        $tempProducts->getObj(0)->setPrice(600);
        $tempProducts->getObj(1)->setPrice(300);
        $tempProducts->getObj(2)->setPrice(900);
        $tempProducts->getObj(3)->setPrice(1500);
        $customerOrder->addProduct(new OrderProduct($tempProducts->getObj(1), $customerOrder, 3));
        $customerOrder->addProduct(new OrderProduct($tempProducts->getObj(3), $customerOrder, 2));

        $customerOrders->add($customerOrder);

        //O9
        $customerOrder = new Order(9, new BaseArray(null, Parameter::class), new \DateTime);
        $tempProducts = new BaseArray($tempProducts, Product::class);
        $tempProducts->getObj(0)->setPrice(600);
        $tempProducts->getObj(1)->setPrice(250);
        $tempProducts->getObj(2)->setPrice(900);
        $tempProducts->getObj(3)->setPrice(1100);
        $customerOrder->addProduct(new OrderProduct($tempProducts->getObj(0), $customerOrder, 3));
        $customerOrder->addProduct(new OrderProduct($tempProducts->getObj(1), $customerOrder, 3));
        $customerOrder->addProduct(new OrderProduct($tempProducts->getObj(3), $customerOrder, 1));

        $customerOrders->add($customerOrder);

        //O10
        $customerOrder = new Order(10, new BaseArray(null, Parameter::class), new \DateTime);
        $tempProducts = new BaseArray($tempProducts, Product::class);
        $tempProducts->getObj(0)->setPrice(600);
        $tempProducts->getObj(1)->setPrice(250);
        $tempProducts->getObj(2)->setPrice(900);
        $tempProducts->getObj(3)->setPrice(1500);
        $customerOrder->addProduct(new OrderProduct($tempProducts->getObj(1), $customerOrder, 3));
        $customerOrder->addProduct(new OrderProduct($tempProducts->getObj(3), $customerOrder, 2));

        $customerOrders->add($customerOrder);

        //O11
        $customerOrder = new Order(11, new BaseArray(null, Parameter::class), new \DateTime);
        $tempProducts = new BaseArray($tempProducts, Product::class);
        $tempProducts->getObj(0)->setPrice(600);
        $tempProducts->getObj(1)->setPrice(250);
        $tempProducts->getObj(2)->setPrice(900);
        $tempProducts->getObj(3)->setPrice(1100);
        $customerOrder->addProduct(new OrderProduct($tempProducts->getObj(1), $customerOrder, 1));

        $customerOrders->add($customerOrder);


        $customer = new Customer(uniqid());
        $customer->setHistory($customerOrders);

        $group = new CustomerGroup(uniqid());
        $group->addCustomer($customer);

        return $group;
    }

}