<?php

namespace ECGM\Model;


use ECGM\Exceptions\InvalidArgumentException;

/**
 * Class Parameter
 * @package ECGM\Model
 */
class Parameter
{
    /**
     * @var mixed
     */
    protected $id;
    /**
     * @var mixed
     */
    protected $value;
    /**
     * @var Customer
     */
    protected $customer;
    /**
     * @var boolean
     */
    protected $isCircular;
    /**
     * @var float
     */
    protected $maxValue;

    /**
     * CustomerParameter constructor.
     * @param mixed $id
     * @param float $value
     * @param boolean $isCircular eg hours in day or months in year
     * @param float $maxValue is parameter is circular maxValue must be set
     * @throws InvalidArgumentException
     */
    public function __construct($id, $value, $isCircular = false, $maxValue = 0)
    {
        $this->id = $id;

        if (!is_numeric($value)) {
            throw new InvalidArgumentException("Parameter $value is not numeric and cannot be used.");
        }

        if ($isCircular && !$maxValue) {
            throw new InvalidArgumentException("When parameter is circular, max value must be set.");
        }

        if ($isCircular && $value > $maxValue) {
            throw new InvalidArgumentException("Value $value cannot be greater than max value ($maxValue)");
        }

        $this->value = $value;
        $this->isCircular = ($isCircular) ? true : false;
        $this->maxValue = $maxValue;

    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function isCircular()
    {
        return $this->isCircular;
    }

    /**
     * @return float
     */
    public function getMaxValue()
    {
        return $this->maxValue;
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param Customer $customer
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    public function __toString()
    {
        return strval($this->getValue());
    }

}