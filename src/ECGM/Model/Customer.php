<?php
namespace ECGM\Model;


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
        $this->parameters = new BaseArray(null, "ECGM\Model\CustomerParameter");
        $this->history = new BaseArray(null, "ECGM\Model\Order");
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
     * @return BaseArray
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param CustomerParameter $parameter
     */
    public function addParameter(CustomerParameter $parameter){
        $parameter->setCustomer($this);
        $this->parameters->add($parameter, $parameter->getId());
    }

    /**
     * @param $parameterId
     */
    public function removeParameter($parameterId){
        $this->parameters->remove($parameterId);
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
        $this->history->add($order, $order->getId());
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