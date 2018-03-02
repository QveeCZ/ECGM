<?php
namespace ECGM\Tests;


use ECGM\Controller\DealerStrategyController;
use ECGM\Model\BaseArray;
use ECGM\Model\CurrentProduct;

class StrategyTests extends MiscTests
{

    public function testDealerStrategy(){
        echo "Dealer strategy tests\n\n";

        $products = new BaseArray(null, CurrentProduct::class);

        $products->add(new CurrentProduct(1, 100, 30, 200));
        $products->add(new CurrentProduct(2, 200, 30, 161));
        $products->add(new CurrentProduct(3, 300, 30, 150));
        $products->add(new CurrentProduct(4, 400, 30, 400));

        $strategyController = new DealerStrategyController();

        $strategy = $strategyController->getDealerStrategy($products);

        $expected = array(1 => 200/911, 2 => 161/911, 3 => 150/911, 4 => 400/911);

        $this->assertEquals(count($expected), count($strategy));

        for ($j = 1; $j <= count($expected); $j++) {
            echo $j;
            $this->assertEquals(round($expected[$j], 3), $strategy[$j], 3);
            echo " - OK\n";
        }

        echo self::$splitLine;
    }

}