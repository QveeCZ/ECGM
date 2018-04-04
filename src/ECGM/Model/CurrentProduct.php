<?php

namespace ECGM\Model;


use ECGM\Enum\DateType;
use ECGM\Exceptions\InvalidArgumentException;

/**
 * Class CurrentProduct
 * @package ECGM\Model
 */
class CurrentProduct extends Product
{

    protected $ppc;

    /**
     * CurrentProduct constructor.
     * @param $id
     * @param float $price
     * @param int $expiration
     * @param mixed $ppc
     * @param float $discount
     * @throws InvalidArgumentException
     * @throws \ReflectionException
     */
    public function __construct($id, $price, $expiration, $ppc, $discount = 0.0)
    {
        parent::__construct($id, $price, $expiration, DateType::DAYS, $discount);

        if (!is_numeric($ppc)) {
            throw new InvalidArgumentException("PRC has to be number.");
        }

        if ($ppc < 1) {
            throw  new InvalidArgumentException("PRC cannot be lower than 1, but is " . $ppc . ".");
        }

        $this->ppc = $ppc;
    }

    public function __toString()
    {
        $str = parent::__toString();
        $str .= ", PPC: " . $this->getPpc();
        return $str;
    }

    /**
     * @return mixed
     */
    public function getPpc()
    {
        return $this->ppc;
    }

    /**
     * @param mixed $ppc
     */
    public function setPpc($ppc)
    {
        $this->ppc = $ppc;
    }
}