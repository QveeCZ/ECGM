<?php
namespace ECGM\Model;


use ECGM\Exceptions\InvalidArgumentException;

class Customer
{
    /**
     * @var integer
     */
    private $id;
    /**
     * @var BaseArray
     */
    private $parameters;
    /**
     * @var BaseArray
     */
    private $history;
    /**
     * @var CustomerGroup
     */
    private $group;

    /**
     * Customer constructor.
     * @param mixed $id
     */
    public function __construct($id, CustomerGroup $group)
    {
        $this->parameters = new BaseArray(null, Parameter::class);
        $this->history = new BaseArray(null, Order::class);
        $this->id = $id;
        $this->group = $group;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param BaseArray $parameters
     * @throws InvalidArgumentException
     */
    public function setParameters(BaseArray $parameters)
    {
        if($parameters->requiredBaseClass() != Parameter::class){
            throw new InvalidArgumentException("Base class has to be equal to " . Parameter::class . " but is " . $parameters->requiredBaseClass() . ".");
        }
        $this->parameters = $parameters;
    }

    /**
     * @return BaseArray
     */
    public function getParameters()
    {
        return $this->parameters;
    }


    public function getParametersAsSimpleArray(){
        $ret = array();
        /**
         * @var Parameter $parameter
         */
        foreach ($this->parameters as $parameter){
            $ret[] = $parameter->getValue();
        }
        return $ret;
    }

    /**
     * @param Parameter $parameter
     */
    public function addParameter(Parameter $parameter){
        $parameter->setCustomer($this);
        $this->parameters->add($parameter);
    }

    /**
     * @param $parameterId
     */
    public function removeParameter($parameterId){
        $this->parameters->remove($parameterId);
    }

    /**
     * @param BaseArray $history
     * @throws InvalidArgumentException
     */
    public function setHistory(BaseArray $history)
    {
        if($history->requiredBaseClass() != Order::class){
            throw new InvalidArgumentException("Base class has to be equal to " . Order::class . " but is " . $history->requiredBaseClass() . ".");
        }

        $this->history = $history;
    }

    /**
     * @return BaseArray
     */
    public function getHistory()
    {
        return $this->history;
    }

    /**
     * @param Order $order
     */
    public function addOrder(Order $order){
        $order->setCustomer($this);
        $this->history->add($order);
    }

    /**
     * @param $orderId
     */
    public function removeOrder($orderId){
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
    public function setGroup($group)
    {
        $this->group = $group;
    }

}