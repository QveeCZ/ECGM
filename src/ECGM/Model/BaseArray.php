<?php

namespace ECGM\Model;


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
     * @var string allows only object of specified class to be inserted into array
     */
    private $requiredBaseClass;

    /**
     * BaseArray constructor.
     * @param BaseArray|null $baseArray
     * @param string $requiredBaseClass
     */
    public function __construct(BaseArray $baseArray = null,$requiredBaseClass = null)
    {
        if (!is_null($baseArray)) {
            $this->list = $baseArray->list;
            $this->size = $baseArray->size;
            $this->requiredBaseClass = $requiredBaseClass;
        } else {
            $this->list = array();
            $this->requiredBaseClass = $baseArray->requiredBaseClass;
        }
    }

    /**
     * @param mixed $obj
     * @param mixed|null $key
     * @return boolean
     */
    public function add($obj, $key = null)
    {
        if(!$this->isValid($obj)){
            return false;
        }

        if(is_null($key) || !$this->keyExists($key)){
            $this->size++;
        }

        if(is_null($key)){
            $this->list[$key] = $obj;
        }else{
            array_push($this->list, $obj);
        }

    }

    /**
     * @param array $list
     * @return boolean
     */
    public function set($list)
    {
        if(!$this->isValid($list)){
            return false;
        }

        $this->list = $list;
        $this->size = count($list);
        return true;
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
    public function get(){
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
    public function keyExists($key){
        return ($this->getKey($key) === -1) ? false : true;
    }

    //Private functions

    private function isValid($obj){

        if(is_null($this->requiredBaseClass)){
            return true;
        }

        if(!is_array($obj)){
            $obj = array($obj);
        }

        foreach ($obj as $singleObj){
            if(!is_subclass_of($singleObj, $this->requiredBaseClass)){
                return false;
            }
        }

        return true;
    }

}