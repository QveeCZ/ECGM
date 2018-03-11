<?php
namespace ECGM\Controller;


use ECGM\Enum\StrategyType;
use ECGM\Exceptions\InvalidArgumentException;
use ECGM\Exceptions\LogicalException;
use ECGM\Int\CustomerStrategyInterface;
use ECGM\Int\DealerStrategyInterface;
use ECGM\Int\StrategyInterface;
use ECGM\Model\AssociativeBaseArray;
use ECGM\Model\CurrentProduct;
use ECGM\Model\Customer;
use ECGM\Model\Order;

class StrategyController implements StrategyInterface
{

    private $customerStrategyController;
    private $dealerStrategyController;
    private $strategyType;

    /**
     * StrategyController constructor.
     * @param $coefficient
     * @param int $strategyType
     * @throws InvalidArgumentException
     */
    public function __construct($coefficient, $strategyType = StrategyType::CONSERVATIVE)
    {
        if(!StrategyType::isValidValue($strategyType)){
            throw new InvalidArgumentException("Strategy type is $strategyType, but available values are " . json_encode(StrategyType::getConstants()) . "." );
        }

        $this->customerStrategyController = new CustomerStrategyController($coefficient);
        $this->dealerStrategyController = new DealerStrategyController();
        $this->strategyType = $strategyType;
    }

    /**
     * @return CustomerStrategyController
     */
    public function getCustomerStrategyController()
    {
        return $this->customerStrategyController;
    }

    /**
     * @param CustomerStrategyInterface $customerStrategyController
     */
    public function setCustomerStrategyController(CustomerStrategyInterface $customerStrategyController)
    {
        $this->customerStrategyController = $customerStrategyController;
    }

    /**
     * @return DealerStrategyController
     */
    public function getDealerStrategyController()
    {
        return $this->dealerStrategyController;
    }

    /**
     * @param DealerStrategyInterface $dealerStrategyController
     */
    public function setDealerStrategyController(DealerStrategyInterface $dealerStrategyController)
    {
        $this->dealerStrategyController = $dealerStrategyController;
    }

    /**
     * @param Customer $customer
     * @param AssociativeBaseArray $currentProducts
     * @param Order|null $currentOrder
     * @return AssociativeBaseArray
     * @throws InvalidArgumentException
     * @throws LogicalException
     */
    public function getIdealStrategy(Customer $customer, AssociativeBaseArray $currentProducts, Order $currentOrder = null){

        switch ($this->strategyType){
            case StrategyType::PASSIVE:
                return $this->getPassiveStrategy($customer, $currentProducts, $currentOrder);
            default:
                throw new LogicalException("Invalid strategy type " . $this->strategyType . ".");
        }
    }

    /**
     * @param Customer $customer
     * @param AssociativeBaseArray $currentProducts
     * @param Order|null $currentOrder
     * @return AssociativeBaseArray
     * @throws InvalidArgumentException
     */
    protected function getPassiveStrategy(Customer $customer, AssociativeBaseArray $currentProducts, Order $currentOrder = null){
        $dealerStrategy = $this->dealerStrategyController->getDealerStrategy($currentProducts);
        $customerStrategy = $this->customerStrategyController->getCustomerStrategy($customer, $currentProducts, $currentOrder);

        $idealStrategy = array();

        foreach ($dealerStrategy as $product => $strategy){
            $idealStrategy[$product] = $strategy * $customerStrategy[$product];
        }

        $idealStrategy = arsort($idealStrategy);

        $sortedProducts = new AssociativeBaseArray(null, CurrentProduct::class);

        foreach ($idealStrategy as $key => $value){
            $sortedProducts->add($currentProducts->getObj($key));
        }

        return $sortedProducts;
    }
}