<?php

namespace ECGM\Controller;


use ECGM\Enum\StrategyType;
use ECGM\Exceptions\InvalidArgumentException;
use ECGM\Exceptions\LogicalException;
use ECGM\Int\StrategyInterface;
use ECGM\Int\StrategyTypeInterface;
use ECGM\MainInterface;
use ECGM\Model\AssociativeBaseArray;
use ECGM\Model\Customer;
use ECGM\Model\Order;

/**
 * Class StrategyController
 * @package ECGM\Controller
 */
class StrategyController implements StrategyInterface
{

    /**
     * @var StrategyTypeInterface $passiveStrategyController
     */
    protected $passiveStrategyController;
    /**
     * @var StrategyTypeInterface $conservativeStrategyController
     */
    protected $conservativeStrategyController;
    /**
     * @var StrategyTypeInterface $aggressiveStrategyController
     */
    protected $aggressiveStrategyController;
    protected $mainInterface;

    /**
     * StrategyController constructor.
     * @param int $coefficient
     * @param MainInterface $mainInterface
     * @throws InvalidArgumentException
     */
    public function __construct($coefficient, MainInterface $mainInterface)
    {

        $this->mainInterface = $mainInterface;
        $this->passiveStrategyController = new PassiveStrategyTypeController($coefficient, $mainInterface);
        $this->conservativeStrategyController = new ConservativeStrategyTypeController($coefficient, $mainInterface);
        $this->aggressiveStrategyController = new AggressiveStrategyTypeController($coefficient, $mainInterface);
    }

    /**
     * @param StrategyTypeInterface $passiveStrategyController
     */
    public function setPassiveStrategyController(StrategyTypeInterface $passiveStrategyController)
    {
        $this->passiveStrategyController = $passiveStrategyController;
    }

    /**
     * @param StrategyTypeInterface $conservativeStrategyController
     */
    public function setConservativeStrategyController(StrategyTypeInterface $conservativeStrategyController)
    {
        $this->conservativeStrategyController = $conservativeStrategyController;
    }

    /**
     * @param StrategyTypeInterface $aggressiveStrategyController
     */
    public function setAggressiveStrategyController(StrategyTypeInterface $aggressiveStrategyController)
    {
        $this->aggressiveStrategyController = $aggressiveStrategyController;
    }

    /**
     * @param Customer $customer
     * @param AssociativeBaseArray $currentProducts
     * @param Order|null $currentOrder
     * @param int $strategyType
     * @return AssociativeBaseArray
     * @throws InvalidArgumentException
     * @throws LogicalException
     * @throws \ReflectionException
     */
    public function getStrategy(Customer $customer, AssociativeBaseArray $currentProducts, Order $currentOrder = null, $strategyType = StrategyType::CONSERVATIVE)
    {
        if (!StrategyType::isValidValue($strategyType)) {
            throw new InvalidArgumentException("Strategy type is $strategyType, but available values are " . json_encode(StrategyType::getConstants()) . ".");
        }

        if ($currentProducts->size() < 2) {
            return $currentProducts;
        }

        switch ($strategyType) {
            case StrategyType::PASSIVE:
                return $this->passiveStrategyController->getIdealStrategy($customer, $currentProducts, $currentOrder);
            case StrategyType::CONSERVATIVE:
                return $this->conservativeStrategyController->getIdealStrategy($customer, $currentProducts, $currentOrder);
            case StrategyType::AGGRESSIVE:
                return $this->aggressiveStrategyController->getIdealStrategy($customer, $currentProducts, $currentOrder);
            default:
                throw new LogicalException("Invalid strategy type " . $strategyType . ".");
        }
    }
}