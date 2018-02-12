<?php
namespace ECGM\Model;


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
        $this->customers->add($customer, $customer->getId());
    }

    /**
     * @param $customerId
     */
    public function removeOrder($customerId){
        $this->customers->remove($customerId);
    }

}