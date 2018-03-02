<?php

namespace ECGM\Model;

use ECGM\Exceptions\InvalidArgumentException;

class CustomerGroup
{
    /**
     * @var mixed
     */
    private $id;
    /**
     * @var BaseArray
     */
    private $customers;
    /**
     * @var BaseArray
     */
    private $parameters;

    /**
     * CustomerGroup constructor.
     * @param mixed $id
     */
    public function __construct($id, BaseArray $parameters = null)
    {
        $this->id = $id;
        $this->customers = new BaseArray(null, Customer::class);

        if ($parameters && $parameters->requiredBaseClass() != Parameter::class) {
            throw new InvalidArgumentException("Required class for parameters array has to be equal to " . Parameter::class . " but is " . $parameters->requiredBaseClass() . ".");
        }

        if ($parameters) {
            $this->parameters = $parameters;
        } else {
            $this->parameters = new BaseArray(null, Parameter::class);
        }
    }

    /**
     * @param BaseArray $customers
     */
    public function mergeCustomers(BaseArray $customers)
    {
        /**
         * @var Customer $customer
         */
        foreach ($customers as $customer) {
            $customer->setGroup($this);
            $this->addCustomer($customer);
        }
    }

    /**
     * @param Customer $customer
     */
    public function addCustomer(Customer $customer)
    {
        $customer->setGroup($this);
        $this->customers->add($customer);
    }

    /**
     * @param Customer $customer
     */
    public function removeCustomer(Customer $customer)
    {
        $this->customers->removeByObject($customer);
    }

    /**
     * @param BaseArray $customers
     */
    public function removeCustomers(BaseArray $customers)
    {
        /**
         * @var Customer $customer
         */
        foreach ($customers as $customer) {
            $customer->setGroup(null);
            $this->customers->removeByObject($customer);
        }
    }

    /**
     * @param Customer $customer
     */
    public function addParameter(Parameter $parameter)
    {
        $this->parameters->add($parameter);
    }

    /**
     * @param $parameterId
     */
    public function removeParameter($parameterId)
    {
        $this->parameters->remove($parameterId);
    }

    public function __toString()
    {
        $str = "Group: " . $this->getId() . "\n";
        $str .= "Parameters\n{\n" . $this->getParameters()->__toString() . "}\n";
        $str .= "Customers\n{\n" . $this->getCustomers()->__toString() . "}";
        return $str;
    }

    /**
     * @return mixed
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
     * @param BaseArray $parameters
     * @throws InvalidArgumentException
     */
    public function setParameters(BaseArray $parameters)
    {
        $this->parameters->set($parameters);
    }

    /**
     * @return BaseArray
     */
    public function getCustomers()
    {
        return $this->customers;
    }

    /**
     * @param BaseArray $customers
     */
    public function setCustomers(BaseArray $customers)
    {
        $this->customers->set($customers);
    }

}