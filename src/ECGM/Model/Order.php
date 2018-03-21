<?php

namespace ECGM\Model;

/**
 * Class Order
 * @package ECGM\Model
 */
class Order
{
    /**
     * @var mixed
     */
    protected $id;
    /**
     * @var BaseArray
     */
    protected $customerParameters;
    /**
     * @var BaseArray
     */
    protected $products;
    /**
     * @var \DateTime $orderDate
     */
    protected $orderDate;

    /**
     * Order constructor.
     * @param mixed $id
     * @param BaseArray $parameters
     * @param \DateTime $orderDate
     * @throws \ECGM\Exceptions\InvalidArgumentException
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
     * @return \DateTime
     */
    public function getOrderDate()
    {
        return $this->orderDate;
    }

    /**
     * @param OrderProduct $product
     * @throws \ECGM\Exceptions\InvalidArgumentException
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
     * @throws \ECGM\Exceptions\InvalidArgumentException
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

}