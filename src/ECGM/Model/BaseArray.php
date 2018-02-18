<?php

namespace ECGM\Model;


use ECGM\Exceptions\InvalidArgumentException;

class BaseArray implements \Iterator
{
    /**
     * @var integer $size
     */
    protected $size;
    /**
     * @var array
     */
    protected $list;
    /**
     * @var integer
     */
    protected $position;
    /**
     * @var string if specified and valid classname, only instances or children of this class will be allowed into array
     */
    protected $requiredBaseClass;

    /**
     * BaseArray constructor.
     * @param BaseArray|null $baseArray
     * @param string|null $requiredBaseClass
     * @throws InvalidArgumentException
     */
    public function __construct(BaseArray $baseArray = null, $requiredBaseClass = null)
    {
        if (is_null($baseArray) && !is_null($requiredBaseClass) && !class_exists($requiredBaseClass)) {
            throw new InvalidArgumentException("Required class " . $requiredBaseClass . " is invalid.");
        }

        if (is_null($baseArray)) {
            $this->list = array();
            $this->size = 0;
            $this->requiredBaseClass = $requiredBaseClass;
            $this->position = 0;
        } else {
            $this->list = $baseArray->list;
            $this->size = $baseArray->size;
            $this->requiredBaseClass = $baseArray->requiredBaseClass;
            $this->position = $baseArray->position;
        }
    }

    /**
     * @param mixed $obj
     * @throws InvalidArgumentException
     */
    public function add($obj)
    {
        if (!$this->isValid($obj)) {
            throw new InvalidArgumentException("Object " . get_class($obj) . " is required to be of, or to inherit from class " . $this->requiredBaseClass . " but does not.");
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
        $this->position = 0;
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
        $this->position = $baseArray->position;
    }

    /**
     * @param BaseArray $baseArray
     * @throws InvalidArgumentException
     */
    public function merge(BaseArray $baseArray)
    {
        if ($this->requiredBaseClass != $baseArray->requiredBaseClass) {
            throw new InvalidArgumentException("Required base class " . $baseArray->requiredBaseClass . " of array to be inserted is not equal to " . $this->requiredBaseClass . ".");
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

    public function removeByObject($obj)
    {
        $this->isValid($obj);

        if (($key = array_search($obj, $this->list)) !== false) {
            unset($this->list[$key]);
            $this->size--;
        }
    }

    /**
     * @param BaseArray $baseArray
     * @throws InvalidArgumentException
     */
    public function removeAll(BaseArray $baseArray)
    {
        if ($this->requiredBaseClass != $baseArray->requiredBaseClass) {
            throw new InvalidArgumentException("Required base class " . $baseArray->requiredBaseClass . " of array to be inserted is not equal to " . $this->requiredBaseClass . ".");
        }

        foreach ($baseArray as $obj) {
            $this->removeByObject($obj);
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

    //Iterator functions

    /**
     *
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * @return mixed
     */
    public function current()
    {
        return $this->list[$this->position];
    }

    /**
     * @return int
     */
    public function key()
    {
        return $this->position;
    }

    /**
     *
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return isset($this->list[$this->position]);
    }

    /**
     * @return int
     */
    public function nextKey()
    {
        return $this->size() + 1;
    }

    //Private functions

    /**
     * @param mixed $obj
     * @return bool
     */
    protected function isValid($obj)
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
     * @throws InvalidArgumentException
     */
    protected function isListValid($list)
    {
        if (!is_array($list)) {
            throw new InvalidArgumentException("Parameter is not an array.");
        }

        $listCount = count($list);

        for ($i = 0; $i < $listCount; $i++) {
            $obj = $list[$i];
            if (!$this->isValid($obj)) {
                throw new InvalidArgumentException("Object " . get_class($obj) . " on position " . $i . " in array is required to be of, or to inherit from class " . $this->requiredBaseClass . " but does not.");
            }
        }
    }

}