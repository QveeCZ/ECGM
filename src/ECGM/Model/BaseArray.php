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
        if(is_null($baseArray) && !is_null($requiredBaseClass) && !class_exists ($requiredBaseClass)){
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
     * @param null|mixed $key
     * @throws InvalidValueException
     */
    public function add($obj, $key = null)
    {
        if (!$this->isValid($obj)) {
            throw new InvalidValueException("Object " . get_class($obj) . " is required to be of, or to inherit from class " . $this->requiredBaseClass . " but does not.");
        }

        if (is_null($key) || !$this->keyExists($key)) {
            $this->size++;
        }

        if (is_null($key)) {
            $this->list[$key] = $obj;
        } else {
            array_push($this->list, $obj);
        }
    }

    /**
     * @param array $list
     * @throws InvalidValueException
     */
    public function set($list)
    {
        if(!is_array($list)){
            throw new InvalidValueException("Parameter is not an array..");
        }

        $listCount = count($list);

        for ($i = 0; $i < $listCount; $i++){
            $obj = $list[$i];
            if (!$this->isValid($obj)) {
                throw new InvalidValueException("Object " . get_class($obj) . " on position " . $i . " in array is required to be of, or to inherit from class " . $this->requiredBaseClass . " but does not.");
            }
        }

        $this->list = $list;
        $this->size = $listCount;
    }

    /**
     * @param mixed $key
     */
    public function remove($key)
    {
        if (array_key_exists($key, $this->list)) {
            unset($this->list[$key]);
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
     * @param mixed $key
     * @return mixed|null
     */
    public function getObj($key)
    {
        if (array_key_exists($key, $this->list)) {
            return $this->list[$key];
        } else {
            return NULL;
        }
    }

    /**
     * @return array
     */
    public function get()
    {
        return $this->list;
    }

    /**
     * @param mixed $obj
     * @return int
     */
    public function getKey($obj)
    {
        $arrKeys = array_keys($this->list, $obj);

        if (empty($arrKeys)) {
            return -1;
        } else {
            return $arrKeys[0];
        }
    }

    /**
     * @param mixed $key
     * @return bool
     */
    public function keyExists($key)
    {
        return ($this->getKey($key) === -1) ? false : true;
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

}