<?php
namespace ECGM\Controller;


use ECGM\Exceptions\InvalidArgumentException;
use ECGM\Int\StrategyInterface;
use ECGM\Model\BaseArray;
use ECGM\Model\Customer;
use ECGM\Model\Order;

class StrategyController implements StrategyInterface
{

    private $customerStrategyController;
    private $dealerStrategyController;

    /**
     * StrategyController constructor.
     * @param $coefficient
     * @throws InvalidArgumentException
     */
    public function __construct($coefficient)
    {
        $this->customerStrategyController = new CustomerStrategyController($coefficient);
        $this->dealerStrategyController = new DealerStrategyController();
    }

    /**
     * @param Customer $customer
     * @param BaseArray $currentProducts
     * @param Order|null $currentOrder
     * @return array|bool
     * @throws InvalidArgumentException
     */
    public function getIdealStrategy(Customer $customer, BaseArray $currentProducts, Order $currentOrder = null){
        $dealerStrategy = $this->dealerStrategyController->getDealerStrategy($currentProducts);
        $customerStrategy = $this->customerStrategyController->getCustomerStrategy($customer, $currentProducts, $currentOrder);

        $idealStrategy = array();

        foreach ($dealerStrategy as $product => $strategy){
            $idealStrategy[$product] = $strategy * $customerStrategy[$product];
        }

        $idealStrategy = arsort($idealStrategy);

        return $idealStrategy;
    }
}