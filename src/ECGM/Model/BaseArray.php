<?php

namespace ECGM\Model;


use ECGM\Exceptions\InvalidValueException;

class BaseArray
{
    /**
     * @var integer $size
     */
    private $size;
    /**
     * @var array
     */
    private $list;
    /**
     * @var string if specified and valid classname, only instances or children of this class will be allowed into array
     */
    private $requiredBaseClass;

    /**
     * BaseArray constructor.
     * @param BaseArray|null $baseArray
     * @param string|null $requiredBaseClass
     * @throws InvalidValueException
     */
    public function __construct(BaseArray $baseArray = null, $requiredBaseClass = null)
    {
        if (is_null($baseArray) && !is_null($requiredBaseClass) && !class_exists($requiredBaseClass)) {
            throw new InvalidValueException("Required class " . $requiredBaseClass . " is invalid.");
        }

        if (is_null($baseArray)) {
            $this->list = array();
            $this->size = 0;
            $this->requiredBaseClass = $requiredBaseClass;
        } else {
            $this->list = $baseArray->list;
            $this->size = $baseArray->size;
            $this->requiredBaseClass = $baseArray->requiredBaseClass;
        }
    }

    /**
     * @param mixed $obj
     * @throws InvalidValueException
     */
    public function add($obj)
    {
        if (!$this->isValid($obj)) {
            throw new InvalidValueException("Object " . get_class($obj) . " is required to be of, or to inherit from class " . $this->requiredBaseClass . " but does not.");
        }

        $this->list[] = $obj;
        $this->size++;
    }

    /**
     * @param array $list
     */
    public function setList($list)
    {
        $this->isListValid($list);
        $this->list = $list;
        $this->size = count($list);
    }

    /**
     * @param $list
     */
    public function mergeList($list)
    {
        $this->isListValid($list);
        $this->list = array_merge($this->list, $list);
        $this->size += count($list);
    }

    /**
     * @param BaseArray $baseArray
     */
    public function set(BaseArray $baseArray)
    {
        $this->list = $baseArray->list;
        $this->size = $baseArray->size;
        $this->requiredBaseClass = $baseArray->requiredBaseClass;
    }

    /**
     * @param BaseArray $baseArray
     * @throws InvalidValueException
     */
    public function merge(BaseArray $baseArray)
    {
        if ($this->requiredBaseClass != $baseArray->requiredBaseClass) {
            throw new InvalidValueException("Required base class " . $baseArray->requiredBaseClass . " of array to be inserted is not equal to " . $this->requiredBaseClass . ".");
        }
        $this->list = array_merge($this->list, $baseArray->list);
        $this->size += $baseArray->size;
    }

    /**
     *
     */
    public function clear()
    {
        $this->list = array();
        $this->size = 0;
    }

    /**
     * @param integer $index
     */
    public function remove($index)
    {
        if (is_numeric($index) && $index < $this->size()) {
            unset($this->list[$index]);
            $this->size--;
        }
    }

    /**
     * @return integer
     */
    public function size()
    {
        return $this->size;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return !$this->size;
    }

    /**
     * @param integer $index
     * @return mixed|null
     */
    public function getObj($index)
    {

        if (!is_numeric($index) || $index > $this->size() - 1 || $index < 0) {
            return null;
        }

        return $this->list[$index];
    }

    /**
     * @return array
     */
    public function get()
    {
        return $this->list;
    }

    /**
     * @return string
     */
    public function requiredBaseClass()
    {
        return $this->requiredBaseClass;
    }

    //Private functions

    /**
     * @param mixed $obj
     * @return bool
     */
    private function isValid($obj)
    {
        if (is_null($this->requiredBaseClass)) {
            return true;
        }

        if (!$obj instanceof $this->requiredBaseClass) {
            return false;
        }

        return true;
    }

    /**
     * @param array $list
     * @throws InvalidValueException
     */
    private function isListValid($list)
    {
        if (!is_array($list)) {
            throw new InvalidValueException("Parameter is not an array.");
        }

        $listCount = count($list);

        for ($i = 0; $i < $listCount; $i++) {
            $obj = $list[$i];
            if (!$this->isValid($obj)) {
                throw new InvalidValueException("Object " . get_class($obj) . " on position " . $i . " in array is required to be of, or to inherit from class " . $this->requiredBaseClass . " but does not.");
            }
        }
    }

}