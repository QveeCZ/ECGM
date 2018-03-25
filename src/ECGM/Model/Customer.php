<?php

namespace ECGM\Model;


use ECGM\Exceptions\InvalidArgumentException;

/**
 * Class Customer
 * @package ECGM\Model
 */
class Customer
{
    /**
     * @var int
     */
    protected $id;
    /**
     * @var BaseArray
     */
    protected $parameters;
    /**
     * @var array
     *
     * Parameters as simple array
     *
     */
    protected $simpleParams;
    /**
     * @var BaseArray
     */
    protected $history;
    /**
     * @var CustomerGroup
     */
    protected $group;

    /**
     * Customer constructor.
     * @param mixed $id
     * @param CustomerGroup|null $group
     * @throws InvalidArgumentException
     */
    public function __construct($id, CustomerGroup $group = null)
    {
        $this->parameters = new BaseArray(null, Parameter::class);
        $this->history = new BaseArray(null, Order::class);
        $this->id = $id;
        $this->group = $group;
    }

    /**
     * @param Parameter $parameter
     * @throws InvalidArgumentException
     */
    public function addParameter(Parameter $parameter)
    {
        $parameter->setCustomer($this);
        $this->parameters->add($parameter);
        $this->populateSimpleParams();
    }

    protected function populateSimpleParams()
    {

        $this->simpleParams = array();
        /**
         * @var Parameter $parameter
         */
        foreach ($this->parameters as $parameter) {
            $this->simpleParams[] = $parameter->getValue();
        }
    }

    /**
     * @param $parameterId
     */
    public function removeParameter($parameterId)
    {
        $this->parameters->remove($parameterId);
        $this->populateSimpleParams();
    }

    /**
     * @return BaseArray
     */
    public function getHistory()
    {
        return $this->history;
    }

    /**
     * @param BaseArray $history
     * @throws InvalidArgumentException
     */
    public function setHistory(BaseArray $history)
    {
        if ($history->requiredBaseClass() != Order::class) {
            throw new InvalidArgumentException("Base class has to be equal to " . Order::class . " but is " . $history->requiredBaseClass() . ".");
        }

        $this->history = $history;
    }

    /**
     * @param Order $order
     * @throws InvalidArgumentException
     */
    public function addOrder(Order $order)
    {
        $order->setCustomerParameters($this->getParameters());
        $this->history->add($order);
    }

    /**
     * @return BaseArray
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param BaseArray $parameters
     * @throws InvalidArgumentException
     */
    public function setParameters(BaseArray $parameters)
    {
        if ($parameters->requiredBaseClass() != Parameter::class) {
            throw new InvalidArgumentException("Base class has to be equal to " . Parameter::class . " but is " . $parameters->requiredBaseClass() . ".");
        }
        $this->parameters = $parameters;
        $this->populateSimpleParams();
    }

    /**
     * @param $orderId
     */
    public function removeOrder($orderId)
    {
        $this->history->remove($orderId);
    }

    /**
     * @return CustomerGroup
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param CustomerGroup $group
     */
    public function setGroup(CustomerGroup $group)
    {
        $this->group = $group;
    }

    public function __toString()
    {
        $str = "Customer " . $this->getId() . "\n";
        $str .= "Parameters: " . ($this->simpleParams) ? implode(", ", $this->getParametersAsSimpleArray()) : "" . "\n";
        $groupId = (isset($this->group) && $this->group) ? $this->group->getId() : "none";
        $str .= "Group: " . $groupId;
        return $str;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function getParametersAsSimpleArray()
    {
        return $this->simpleParams;
    }
}