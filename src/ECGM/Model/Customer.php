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
     * Customer constructor.
     * @param mixed $id
     */
    public function __construct($id)
    {
        $this->parameters = new BaseArray(null, "CustomerParameter");
        $this->history = new BaseArray();
        $this->id = $id;
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

    public function addParameter(CustomerParameter $parameter){
        return $this->parameters->add($parameter, $parameter->getId());
    }

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



}