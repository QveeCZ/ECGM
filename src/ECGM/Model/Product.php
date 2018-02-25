<?php

namespace ECGM\Model;


use ECGM\Exceptions\InvalidArgumentException;

class Product
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
    private $expiration;
    /**
     * @var BaseArray $complements
     */
    private $complements;

    /**
     * Product constructor.
     * @param $id
     * @param $price
     * @param $expiration
     * @param float $discount
     * @throws InvalidArgumentException
     */
    public function __construct($id, $price, $expiration, $discount = 0.0)
    {
        if (!is_numeric($price) || !is_numeric($discount) || !is_numeric($expiration)) {
            throw  new InvalidArgumentException("Price, expiration or discount are not numeric.");
        }

        if ($expiration < 0) {
            throw  new InvalidArgumentException("Expiration cannot be lower than 0, but is " . $expiration . ".");
        }

        $this->id = $id;
        $this->price = $price;
        $this->discount = $discount;
        $this->expiration = $expiration;
        $this->complements = new BaseArray(null, Product::class);
    }

    /**
     * @return BaseArray
     */
    public function getComplements()
    {
        return $this->complements;
    }

    /**
     * @param BaseArray $complements
     */
    public function setComplements($complements)
    {
        $this->complements->set($complements);
    }

    public function __toString()
    {
        $str = "";
        $str .= "ID: " . $this->getId() . ", ";
        $str .= "Price: " . $this->getPrice() . ", ";
        $str .= "Expiration: " . $this->getExpiration() . ", ";
        $str .= "Discount: " . $this->getDiscount();

        $complements = array();

        /**
         * @var Product $complement
         */
        foreach ($this->complements as $complement) {
            $complements[] = $complement->getId();
        }


        $str .= "Complements: [" . implode(", ", $complements) . "]\n";

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

}