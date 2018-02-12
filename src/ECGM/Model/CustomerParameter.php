<?php
namespace ECGM\Model;


class CustomerParameter
{

    private $id;

    /**
     * CustomerParameter constructor.
     * @param mixed $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }




}