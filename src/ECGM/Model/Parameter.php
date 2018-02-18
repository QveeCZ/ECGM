<?php
namespace ECGM\Model;


use ECGM\Exceptions\InvalidArgumentException;

class Parameter
{
    /**
     * @var mixed
     */
    private $id;
    /**
     * @var mixed
     */
    private $value;
    /**
     * @var Customer
     */
    private $customer;
    /**
     * @var boolean
     */
    private $isCircular;
    /**
     * @var integer
     */
    private $maxValue;

    /**
     * CustomerParameter constructor.
     * @param mixed $id
     * @param float $value
     * @param Customer $customer
     * @param boolean $isCircular eg hours in day or months in year
     * @param integer $maxValue is parameter is circular maxValue must be set
     * @throws InvalidArgumentException
     */
    public function __construct($id, $value, Customer $customer, $isCircular = false, $maxValue = 0)
    {
        $this->id = $id;

        if (!is_numeric($value)) {
            throw new InvalidArgumentException("Parameter $value is not numeric and cannot be used.");
        }

        if($isCircular && !$maxValue){
            throw new InvalidArgumentException("When parameter is circular, max valu must be set.");
        }

        if($isCircular && $value > $maxValue){
            throw new InvalidArgumentException("Value $value cannot be greater than max value ($maxValue)");
        }

        $this->customer = $customer;
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
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return bool
     */
    public function isCircular()
    {
        return $this->isCircular;
    }

    /**
     * @return int
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

}