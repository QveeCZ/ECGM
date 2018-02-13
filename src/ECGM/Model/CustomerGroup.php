<?php
namespace ECGM\Model;


use ECGM\Exceptions\InvalidValueException;

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
     * CustomerGroup constructor.
     * @param mixed $id
     */
    public function __construct($id)
    {
        $this->id = $id;
        $this->customers = new BaseArray(null, Customer::class);
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param BaseArray $customers
     */
    public function setCustomers(BaseArray $customers)
    {
        if($customers->requiredBaseClass() != Customer::class){
            throw new InvalidValueException("Required class for customerArray has to be equal to " . Customer::class . " but is " . $customers->requiredBaseClass() . ".");
        }

        $this->customers = $customers;
    }



    /**
     * @return BaseArray
     */
    public function getCustomers()
    {
        return $this->customers;
    }

    /**
     * @param Customer $customer
     */
    public function addCustomer(Customer $customer){
        $customer->setGroup($this);
        $this->customers->add($customer);
    }

    /**
     * @param $customerId
     */
    public function removeOrder($customerId){
        $this->customers->remove($customerId);
    }

}