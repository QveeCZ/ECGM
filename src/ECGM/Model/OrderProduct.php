<?php

namespace ECGM\Model;


use ECGM\Exceptions\InvalidArgumentException;

class OrderProduct
{

    /**
     * @var mixed
     */
    private $id;
    /**
     * @var float
     */
    private $price;
    /**
     * @var float
     */
    private $discount;
    /**
     * @var integer
     */
    private $amount;
    /**
     * @var integer
     */
    private $expiration;
    /**
     * @var Order
     */
    private $order;

    /**
     * OrderProduct constructor.
     * @param $id
     * @param float $price
     * @param integer $expiration
     * @param integer $amount
     * @param float $discount
     * @throws InvalidArgumentException
     */
    public function __construct($id, $price, $expiration, $amount = 1, $discount = 0.0)
    {
        if (!is_numeric($price) || !is_numeric($discount) || !is_numeric($amount) || !is_numeric($expiration)) {
            throw  new InvalidArgumentException("Price, expiration, amount or discount are not numeric.");
        }

        if ($amount < 1) {
            throw  new InvalidArgumentException("Amount cannot be lower than 1, but is " . $amount . ".");
        }

        if ($expiration < 0) {
            throw  new InvalidArgumentException("Expiration cannot be lower than 0, but is " . $amount . ".");
        }

        $this->id = $id;
        $this->price = $price;
        $this->discount = $discount;
        $this->expiration = $expiration;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
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
     * @return int
     */
    public function getExpiration()
    {
        return $this->expiration;
    }

    /**
     * @param int $expiration
     */
    public function setExpiration($expiration)
    {
        $this->expiration = $expiration;
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


}