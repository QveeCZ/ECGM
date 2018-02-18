<?php

namespace ECGM\Model;


class Order
{
    /**
     * @var mixed
     */
    private $id;
    /**
     * @var Customer
     */
    private $customer;
    /**
     * @var BaseArray
     */
    private $products;

    /**
     * Order constructor.
     * @param mixed $id
     * @param Customer $customer
     */
    public function __construct($id, Customer $customer)
    {
        $this->id = $id;
        $this->customer = $customer;
        $this->products = new BaseArray(null, OrderProduct::class);
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
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
     * @return BaseArray
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @param OrderProduct $product
     */
    public function addProduct(OrderProduct $product)
    {
        $product->setOrder($this);
        $this->products->add($product);
    }

    /**
     * @param $productId
     */
    public function removeProduct($productId)
    {
        $this->products->remove($productId);
    }

}