<?php
namespace ECGM\Model;


use ECGM\Exceptions\InvalidArgumentException;

class CurrentProduct extends Product
{

    protected $ppc;

    /**
     * OrderProduct constructor.
     * @param $id
     * @param float $price
     * @param int $expiration
     * @param int $ppc
     * @param float $discount
     * @throws InvalidArgumentException
     */
    public function __construct($id, $price, $expiration, $ppc, $discount = 0.0)
    {
        parent::__construct($id, $price, $expiration, $discount);

        if (!is_numeric($ppc)) {
            throw new InvalidArgumentException("PRC has to be number.");
        }

        if ($ppc < 1) {
            throw  new InvalidArgumentException("PRC cannot be lower than 1, but is " . $ppc . ".");
        }

        $this->ppc = $ppc;
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

    public function __toString()
    {
        $str = parent::__toString();
        $str .= ", PPC: " . $this->getPpc();
        return $str;
    }
}