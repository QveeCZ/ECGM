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
     * @var Order
     */
    private $order;

    /**
     * OrderProduct constructor.
     * @param $id
     * @param float $price
     * @param float $discount
     * @throws InvalidValueException
     */
    public function __construct($id, $price, $discount = 0.0)
    {
        if(!is_numeric($price) || !is_numeric($discount)){
            throw  new InvalidValueException("Price or discount are not numeric.");
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