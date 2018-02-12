<?php
namespace ECGM\Model;


use ECGM\Exceptions\InvalidValueException;

class CustomerParameter
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
     * CustomerParameter constructor.
     * @param mixed $id
     * @param float $value
     * @param Customer $customer
     * @throws InvalidValueException
     */
    public function __construct($id, $value, Customer $customer)
    {
        $this->id = $id;

        if (!is_numeric($value)) {
            throw new InvalidValueException("Parameter " . $value . " is not numeric and cannot be used.");
        }

        $this->customer = $customer;

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