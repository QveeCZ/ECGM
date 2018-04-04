<?php

namespace ECGM\Model;

use ECGM\Exceptions\InvalidArgumentException;

/**
 * Class CustomerGroup
 * @package ECGM\Model
 */
class CustomerGroup
{
    /**
     * @var mixed
     */
    protected $id;
    /**
     * @var BaseArray
     */
    protected $customers;
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
     * CustomerGroup constructor.
     * @param $id
     * @param BaseArray|null $parameters
     * @throws InvalidArgumentException
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
     * @throws InvalidArgumentException
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
     * @throws InvalidArgumentException
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
     * @param Parameter $parameter
     * @throws InvalidArgumentException
     */
    public function addParameter(Parameter $parameter)
    {
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
     * @param int $parameterId
     */
    public function removeParameter($parameterId)
    {
        $this->parameters->remove($parameterId);
        $this->populateSimpleParams();
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
        $this->populateSimpleParams();
    }

    public function getParametersAsSimpleArray()
    {
        return $this->simpleParams;
    }

    /**
     * @param BaseArray $customers
     * @throws InvalidArgumentException
     */
    public function setCustomers(BaseArray $customers)
    {
        $this->customers->set($customers);
    }

    /**
     * @return BaseArray
     */
    public function getCustomers()
    {
        return $this->customers;
    }

    public function __toString()
    {
        $str = "Group: " . $this->getId() . "\n";
        $str .= "Parameters\n{\n" . $this->getParameters()->__toString() . "}\n";
        $str .= "Customers\n{\n" . $this->getCustomers()->__toString() . "}";
        return $str;
    }

}