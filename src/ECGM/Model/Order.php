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
    private $customerParameters;
    /**
     * @var BaseArray
     */
    private $products;

    /**
     * Order constructor.
     * @param mixed $id
     * @param BaseArray $parameters
     */
    public function __construct($id, BaseArray $parameters)
    {
        $this->id = $id;
        $this->customerParameters = new BaseArray(null, Parameter::class);
        $this->customerParameters->set($parameters);
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
     * @return BaseArray
     */
    public function getCustomerParameters()
    {
        return $this->customerParameters;
    }

    /**
     * @param BaseArray $customerParameters
     */
    public function setCustomerParameters(BaseArray $customerParameters)
    {
        $this->customerParameters->set($customerParameters);
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