<?php

namespace ECGM\Model;


class StrategyProduct
{


    /**
     * @var mixed
     */
    protected $id;
    /**
     * @var float
     */
    protected $price;
    /**
     * @var integer
     */
    protected $amount;
    /**
     * @var float
     */
    protected $discount;
    /**
     * @var mixed
     */
    protected $orderId;

    /**
     * StrategyProduct constructor.
     * @param mixed $id
     * @param float $price
     * @param int $amount
     * @param float $discount
     */
    public function __construct($id, $orderId, $price = 0, $amount = 0, $discount = 0.0)
    {
        $this->id = $id;
        $this->orderId = $orderId;
        $this->price = $price;
        $this->amount = $amount;
        $this->discount = $discount;
    }

    /**
     * @return mixed
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @return float|int
     */
    public function getDiscountedPrice()
    {
        return $this->getPrice() - ($this->getPrice() * ($this->getDiscount() / 100));
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return float
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * @param float $discount
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;
    }

    public function __toString()
    {

        $str = "";
        $str .= "ID: " . $this->getId() . ", ";
        $str .= "Amount: " . $this->getAmount() . ", ";
        $str .= "Price: " . $this->getPrice();
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
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }


}