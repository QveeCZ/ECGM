<?php

namespace ECGM\Model;


use ECGM\Enum\DateType;

class OrderProduct extends StrategyProduct
{
    /**
     * @var Order
     */
    protected $order;
    /**
     * @var integer
     */
    protected $expiration;
    /**
     * @var DateType
     */
    protected $expirationDateType;
    /**
     * @var BaseArray $complements
     */
    protected $complements;

    /**
     * OrderProduct constructor.
     * @param Product $product
     * @param Order $order
     * @param int $amount
     */
    public function __construct(Product $product, Order $order, $amount = 0)
    {
        parent::__construct($product->getId(), $order->getId(), $product->getPrice(), $amount, $product->getDiscount());

        $this->order = $order;
        $this->expiration = $product->getExpiration();
        $this->expirationDateType = $product->getExpirationDateType();
        $this->complements = $product->getComplements();
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

    /**
     * @return int
     */
    public function getExpiration()
    {
        return $this->expiration;
    }

    /**
     * @return DateType
     */
    public function getExpirationDateType()
    {
        return $this->expirationDateType;
    }

    /**
     * @return BaseArray
     */
    public function getComplements()
    {
        return $this->complements;
    }

    public function __toString()
    {
        $str = "";
        $str .= "ID: " . $this->getId() . ", ";
        $str .= "Price: " . $this->getPrice() . ", ";
        $str .= "Expiration: " . $this->getExpiration() . ", ";

        $complements = array();

        /**
         * @var Product $complement
         */
        foreach ($this->complements as $complement) {
            $complements[] = $complement->getId();
        }

        $str .= "Complements: [" . implode(", ", $complements) . "]\n";

        $str .= ", Order: " . $this->getOrder()->getId() . ", ";
        $str .= "Amount: " . $this->getAmount();

        return $str;
    }



}