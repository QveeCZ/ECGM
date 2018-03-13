<?php

namespace ECGM\Controller;


use ECGM\Enum\StrategyType;
use ECGM\Exceptions\InvalidArgumentException;
use ECGM\Exceptions\LogicalException;
use ECGM\Int\StrategyInterface;
use ECGM\MainInterface;
use ECGM\Model\AssociativeBaseArray;
use ECGM\Model\Customer;
use ECGM\Model\Order;

class StrategyController implements StrategyInterface
{

    /**
     * @var StrategyInterface $passiveStrategyController
     */
    private $passiveStrategyController;
    /**
     * @var StrategyInterface $conservativeStrategyController
     */
    private $conservativeStrategyController;
    /**
     * @var StrategyInterface $aggressiveStrategyController
     */
    private $aggressiveStrategyController;
    private $strategyType;
    private $mainInterface;

    /**
     * StrategyController constructor.
     * @param $coefficient
     * @param MainInterface $mainInterface
     * @param int $strategyType
     * @throws InvalidArgumentException
     * @throws \ReflectionException
     */
    public function __construct($coefficient, MainInterface $mainInterface, $strategyType = StrategyType::CONSERVATIVE)
    {
        if (!StrategyType::isValidValue($strategyType)) {
            throw new InvalidArgumentException("Strategy type is $strategyType, but available values are " . json_encode(StrategyType::getConstants()) . ".");
        }

        $this->mainInterface = $mainInterface;
        $this->strategyType = $strategyType;
        $this->passiveStrategyController = new PassiveStrategyController($coefficient, $mainInterface);
        $this->conservativeStrategyController = new ConservativeStrategyController($coefficient, $mainInterface);
        $this->aggressiveStrategyController = new AggressiveStrategyController($coefficient, $mainInterface);
    }

    /**
     * @param StrategyInterface $passiveStrategyController
     */
    public function setPassiveStrategyController(StrategyInterface $passiveStrategyController)
    {
        $this->passiveStrategyController = $passiveStrategyController;
    }

    /**
     * @param StrategyInterface $conservativeStrategyController
     */
    public function setConservativeStrategyController(StrategyInterface $conservativeStrategyController)
    {
        $this->conservativeStrategyController = $conservativeStrategyController;
    }

    /**
     * @param StrategyInterface $aggressiveStrategyController
     */
    public function setAggressiveStrategyController(StrategyInterface $aggressiveStrategyController)
    {
        $this->aggressiveStrategyController = $aggressiveStrategyController;
    }

    /**
     * @param Customer $customer
     * @param AssociativeBaseArray $currentProducts
     * @param Order|null $currentOrder
     * @return AssociativeBaseArray
     * @throws LogicalException
     */
    public function getIdealStrategy(Customer $customer, AssociativeBaseArray $currentProducts, Order $currentOrder = null)
    {

        if ($currentProducts->size() < 2) {
            return $currentProducts;
        }

        switch ($this->strategyType) {
            case StrategyType::PASSIVE:
                return $this->passiveStrategyController->getIdealStrategy($customer, $currentProducts, $currentOrder);
            case StrategyType::CONSERVATIVE:
                return $this->conservativeStrategyController->getIdealStrategy($customer, $currentProducts, $currentOrder);
            case StrategyType::AGGRESSIVE:
                return $this->aggressiveStrategyController->getIdealStrategy($customer, $currentProducts, $currentOrder);
            default:
                throw new LogicalException("Invalid strategy type " . $this->strategyType . ".");
        }
    }
}