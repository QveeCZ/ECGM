<?php
namespace ECGM\Model;


class ProductComplement
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
     * ProductComplement constructor.
     * @param Product $product
     */
    public function __construct(Product $product)
    {
        $this->id = $product->getId();
        $this->price = $product->getPrice();
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

    public function __toString()
    {

        $str = "";
        $str .= "ID: " . $this->getId() . ", ";
        $str .= "Price: " . $this->getPrice();
        return $str;
    }

}