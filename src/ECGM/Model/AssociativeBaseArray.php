<?php
namespace ECGM\Model;


use ECGM\Exceptions\InvalidArgumentException;
use ECGM\Int\KeyeableValue;

class AssociativeBaseArray extends BaseArray
{
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
        $this->size++;
    }

    /**
     * @param KeyeableValue[] $list
     * @throws InvalidArgumentException
     */
    public function setList($list)
    {
        $this->isListValid($list);
        $this->list = array();

        foreach ($list as $obj){
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
        foreach ($list as $obj){
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
        if(array_key_exists($key, $this->list)){
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
    protected function isValid($obj)
    {
        if (!in_array(KeyeableValue::class, class_implements($obj))) {
            return false;
        }

        return parent::isValid($obj);
    }


}