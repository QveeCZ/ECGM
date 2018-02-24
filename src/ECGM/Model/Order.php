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
     * @var \DateTime $orderDate
     */
    private $orderDate;

    /**
     * Order constructor.
     * @param $id
     * @param BaseArray $parameters
     * @param \DateTime $orderDate
     */
    public function __construct($id, BaseArray $parameters, \DateTime $orderDate)
    {
        $this->id = $id;
        $this->orderDate = $orderDate;
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
     * @return \DateTime
     */
    public function getOrderDate()
    {
        return $this->orderDate;
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

    public function __toString()
    {
        $str = "Order: " . $this->getId() . "\n";
        $str .= "Customer parameters:\n" . $this->getCustomerParameters()->__toString() . "\n";
        $str .= "Products:\n" . $this->getProducts()->__toString();
        return $str;
    }

}