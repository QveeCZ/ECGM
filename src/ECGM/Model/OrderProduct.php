<?php
namespace ECGM\Model;


use ECGM\Exceptions\InvalidValueException;

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
     * @var Order
     */
    private $order;

    /**
     * OrderProduct constructor.
     * @param $id
     * @param float $price
     * @param integer $amount
     * @param float $discount
     * @throws InvalidValueException
     */
    public function __construct($id, $price, $amount = 1, $discount = 0.0)
    {
        if(!is_numeric($price) || !is_numeric($discount) || !is_numeric($amount)){
            throw  new InvalidValueException("Price, amount or discount are not numeric.");
        }

        if($amount < 1){
            throw  new InvalidValueException("Amount cannot be lower than 1, but is " . $amount . ".");
        }

        $this->id = $id;
        $this->price = $price;
        $this->discount = $discount;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @return float
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
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