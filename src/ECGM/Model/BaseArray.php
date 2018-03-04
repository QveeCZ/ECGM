<?php

namespace ECGM\Model;


use ECGM\Exceptions\InvalidArgumentException;

class BaseArray implements \Iterator, \Countable
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
     * @var string if specified and valid classname, only instances of this class will be allowed into array
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
        if (!is_null($baseArray) && !is_null($requiredBaseClass) && !is_a($baseArray->requiredBaseClass(), $requiredBaseClass, true)) {
            throw new InvalidArgumentException("Required base class " . $baseArray->requiredBaseClass . " of array to be inserted is not equal to " . $requiredBaseClass . ".");
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
     * @return string
     */
    public function requiredBaseClass()
    {
        return $this->requiredBaseClass;
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
     * @param mixed $obj
     * @return bool
     */
    protected function isValid($obj)
    {
        if (is_null($this->requiredBaseClass) || !$this->requiredBaseClass) {
            return true;
        }
        if (!is_a($obj, $this->requiredBaseClass)) {
            return false;
        }
        return true;
    }
    /**
     * @param array $list
     */
    public function setList($list)
    {
        $this->isListValid($list);
        $this->list = array_values($list);
        $this->size = count($list);
        $this->position = 0;
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

        foreach ($list as $key => $value) {
            if (!$this->isValid($value)) {
                throw new InvalidArgumentException("Object " . get_class($value) . " on position " . $key . " in array is required to be of, or to inherit from class " . $this->requiredBaseClass . " but does not.");
            }
        }
    }
    /**
     * @param $list
     */
    public function mergeList($list)
    {
        $this->isListValid($list);
        $this->list = array_merge($this->list, array_values($list));
        $this->size += count($list);
    }
    /**
     * @param BaseArray $baseArray
     * @throws InvalidArgumentException
     */
    public function set(BaseArray $baseArray)
    {
        if ($this->requiredBaseClass && $this->requiredBaseClass != $baseArray->requiredBaseClass()) {
            throw new InvalidArgumentException("Required base class " . $baseArray->requiredBaseClass . " of array to be inserted is not equal to " . $this->requiredBaseClass . ".");
        }
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
            $this->list = array_values($this->list);
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
    public function removeByObject($obj)
    {
        $this->isValid($obj);
        if (($key = array_search($obj, $this->list)) !== false) {
            unset($this->list[$key]);
            $this->list = array_values($this->list);
            $this->size--;
        }
    }
    /**
     * @return bool
     */
    public function isEmpty()
    {
        return !$this->size;
    }
    //Iterator functions
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
     * @inheritdoc
     */
    public function rewind()
    {
        $this->position = 0;
    }
    /**
     * @inheritdoc
     */
    public function current()
    {
        return $this->list[$this->position];
    }
    /**
     * @inheritdoc
     */
    public function key()
    {
        return $this->position;
    }
    /**
     * @inheritdoc
     */
    public function next()
    {
        ++$this->position;
    }
    //protected functions
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
    /**
     * @inheritdoc
     */
    public function count()
    {
        return $this->size();
    }
    public function __toString()
    {
        $str = "Size: " . $this->size() . "\n";
        $str .= "Values\n[\n";
        $str .= implode(";\n", $this->list);
        $str .= "\n]\n";
        return $str;
    }
}