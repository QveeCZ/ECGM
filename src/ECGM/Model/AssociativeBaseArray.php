<?php

namespace ECGM\Model;


use ECGM\Exceptions\InvalidArgumentException;
use ECGM\Int\KeyeableValue;

class AssociativeBaseArray extends BaseArray
{


    /**
     * AssociativeBaseArray constructor.
     * @param AssociativeBaseArray|null $baseArray
     * @param null $requiredBaseClass
     * @throws InvalidArgumentException
     */
    public function __construct(AssociativeBaseArray $baseArray = null, $requiredBaseClass = null)
    {

        parent::__construct($baseArray, $requiredBaseClass);

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
     * @param KeyeableValue $obj
     * @throws InvalidArgumentException
     */
    public function add($obj)
    {
        if (!$this->isValid($obj)) {
            throw new InvalidArgumentException("Object " . get_class($obj) . " is required to be of, or to inherit from class " . $this->requiredBaseClass . " but does not.");
        }

        $this->list[$obj->getKey()] = $obj;
        $this->size = count($this->list);
    }

    /**
     * @inheritdoc
     */
    protected function isValid($obj)
    {
        if (!in_array(KeyeableValue::class, class_implements($obj))) {
            return false;
        }

        return parent::isValid($obj);
    }

    /**
     * @param KeyeableValue[] $list
     * @throws InvalidArgumentException
     */
    public function setList($list)
    {
        $this->isListValid($list);
        $this->list = array();

        foreach ($list as $obj) {
            $this->list[$obj->getKey()] = $obj;
        }
        $this->size = count($this->list);
    }

    /**
     * @param KeyeableValue[] $list
     * @throws InvalidArgumentException
     */
    public function mergeList($list)
    {
        $this->isListValid($list);
        foreach ($list as $obj) {
            $this->list[$obj->getKey()] = $obj;
        }
        $this->size = count($this->list);
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
        $this->size = count($this->list);
    }

    /**
     * @param int $key
     */
    public function remove($key)
    {
        if (array_key_exists($key, $this->list)) {
            unset($this->list[$key]);
            $this->size--;
        }
    }

    /**
     * @param KeyeableValue $obj
     */
    public function removeByObject($obj)
    {
        $this->isValid($obj);
        if (($key = array_search($obj, $this->list)) !== false) {
            unset($this->list[$key]);
            $this->size--;
        }
    }

    /**
     * @param mixed $key
     * @return mixed|null
     */
    public function getObj($key)
    {
        return $this->list[$key];
    }

    /**
     * @inheritdoc
     */
    public function rewind()
    {
        reset($this->list);
        $this->position = key($this->list);
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
        next($this->list);
        $this->position = key($this->list);
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
        next($this->list);
        return key($this->list);
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        return $this->size();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $str = "Size: " . $this->size() . "\n";
        $str .= "Values\n[\n";
        $str .= implode(";\n", $this->array_map_assoc(function ($k, $v) {
            return "$k => $v";
        }, $this->list));
        $str .= "\n]\n";
        return $str;
    }

    protected function array_map_assoc($callback, $array)
    {
        $r = array();
        foreach ($array as $key => $value)
            $r[$key] = $callback($key, $value);
        return $r;
    }


}