<?php

namespace ECGM\Model;


use ECGM\Exceptions\InvalidArgumentException;

class OrderProduct extends Product
{
    /**
     * @var integer
     */
    private $amount;
    /**
     * @var Order
     */
    private $order;

    /**
     * OrderProduct constructor.
     * @param $id
     * @param float $price
     * @param int $expiration
     * @param Order $order
     * @param int $amount
     * @param float $discount
     * @throws InvalidArgumentException
     */
    public function __construct($id, $price, $expiration, Order $order, $amount = 1, $discount = 0.0)
    {
        parent::__construct($id, $price, $expiration, $discount);

        if (!is_numeric($amount)) {
            throw new InvalidArgumentException("Amount has to be number.");
        }

        if ($amount < 1) {
            throw  new InvalidArgumentException("Amount cannot be lower than 1, but is " . $amount . ".");
        }
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

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param Order $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }


    public function __toString()
    {
        $str = parent::__toString();
        $str .= ", Order: " . $this->getOrder()->getId() . ", ";
        $str .= "Amount: " . $this->getAmount();

        return $str;
    }

}