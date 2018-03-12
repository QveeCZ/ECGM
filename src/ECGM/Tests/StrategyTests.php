<?php

namespace ECGM\Tests;


use ECGM\Controller\CustomerStrategyController;
use ECGM\Controller\DealerStrategyController;
use ECGM\Controller\StrategyController;
use ECGM\Enum\StrategyType;
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
    protected $mainInterface;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->mainInterface = new TestMainInterface();
    }

    /**
     * @throws \ECGM\Exceptions\InvalidArgumentException
     * @throws \ECGM\Exceptions\LogicalException
     * @throws \ReflectionException
     */
    public function testPassiveStrategy()
    {

        echo "Passive strategy test\n\n";

        $currentProducts = $this->mainInterface->getProducts();

        $strategyController = new StrategyController(2, $this->mainInterface, StrategyType::PASSIVE);

        $strategy = $strategyController->getIdealStrategy($this->getCustomer(), $currentProducts, null);


        $expected = array(1, 4, 2, 3);
        $this->assertEquals(count($expected), $strategy->size());

        $i = 0;
        /**
         * @var CurrentProduct $value
         */
        foreach ($strategy as $value) {
            echo $value->getId();
            $this->assertEquals($expected[$i], $value->getId());
            echo " - OK\n";
            $i++;
        }

        echo self::$splitLine;
    }

    /**
     * @throws \ECGM\Exceptions\InvalidArgumentException
     * @throws \ECGM\Exceptions\LogicalException
     * @throws \ReflectionException
     */
    public function testAggressiveStrategy()
    {

        echo "Aggressive strategy test\n\n";

        $currentProducts = $this->mainInterface->getProducts();

        $strategyController = new StrategyController(2, $this->mainInterface, StrategyType::AGGRESSIVE);

        $strategy = $strategyController->getIdealStrategy($this->getCustomer(), $currentProducts, null);

        $expected = array(1, 2, 3, 4);
        $this->assertEquals(count($expected), $strategy->size());

        $i = 0;
        /**
         * @var CurrentProduct $value
         */
        foreach ($strategy as $value) {
            echo $value->getId();
            $this->assertEquals($expected[$i], $value->getId());
            echo " - OK\n";
            $i++;
        }

        echo self::$splitLine;

    }

    /**
     * @return Customer
     * @throws \ECGM\Exceptions\InvalidArgumentException
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


        $customerOrder->addProduct(new OrderProduct($tempProducts->getObj(0), $customerOrder, 3));
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
        $customerOrder->addProduct(new OrderProduct($tempProducts->getObj(1), $customerOrder, 1));

        $customerOrders->add($customerOrder);


        $customer = new Customer(uniqid());
        $customer->setHistory($customerOrders);


        //Customer group history

        $customer->setGroup($this->getGroupHistory());

        return $customer;
    }

    /**
     * @return CustomerGroup
     * @throws \ECGM\Exceptions\InvalidArgumentException
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

    /**
     * @throws \ECGM\Exceptions\InvalidArgumentException
     */
    public function testDealerStrategy()
    {
        echo "Dealer strategy tests\n\n";

        $products = new BaseArray(null, CurrentProduct::class);

        $products->add(new CurrentProduct(1, 100, 30, 420));
        $products->add(new CurrentProduct(2, 200, 30, 126));
        $products->add(new CurrentProduct(3, 300, 30, 150));
        $products->add(new CurrentProduct(4, 400, 30, 400));

        $strategyController = new DealerStrategyController();

        $strategy = $strategyController->getDealerStrategy($products);

        $expected = array(1 => 420 / 1096, 2 => 126 / 1096, 3 => 150 / 1096, 4 => 400 / 1096);

        $this->assertEquals(count($expected), count($strategy));

        for ($j = 1; $j <= count($expected); $j++) {
            echo $j;
            $this->assertEquals(round($expected[$j], 3), $strategy[$j], 3);
            echo " - OK\n";
        }

        echo self::$splitLine;
    }

    /**
     * @throws \ECGM\Exceptions\InvalidArgumentException
     */
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

        $expected = array(1 => 0.319, 2 => 0.386, 3 => 0.156, 4 => 0.139);

        $this->assertEquals(count($expected), count($strategy));

        for ($j = 1; $j <= count($expected); $j++) {
            echo $j;
            $this->assertEquals(round($expected[$j], 3), $strategy[$j], 3);
            echo " - OK\n";
        }

        echo self::$splitLine;

    }

}