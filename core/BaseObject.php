<?php


namespace app\core;


use Exception;

abstract class BaseObject
{
    public function __construct($config = [])
    {
        if (is_array($config)){
            foreach ($config as $key => $value){
                if ($this->canSetProperty($key)){
                    $this->$key = $value;
                }
            }
        }
        $this->init();
    }

    protected function init(){

    }

    public function hasProperty($name){
        return $this->canGetProperty($name) || $this->canSetProperty($name);
    }

    public function canGetProperty($name){
        return property_exists($this, $name) || method_exists($this, 'get'.ucfirst($name));
    }

    public function canSetProperty($name){
        return property_exists($this, $name) || method_exists($this, 'set'.ucfirst($name));
    }

    public function __get($name)
    {
        $methodName = 'get'.ucfirst($name);
        if (method_exists($this, $methodName)){
            return $this->$methodName();
        }
        throw new Exception('Trying to get unknown property: '.$name);
    }

    public function __set($name, $value){
        $methodName = 'set'.ucfirst($name);
        if (method_exists($this, $methodName)){
            $this->$methodName($value);
            return;
        }
        throw new Exception('Trying to set unknown property: '.$name);
    }

    public function __isset($name)
    {
        return method_exists($this, 'get'.ucfirst($name));
    }

    public function __unset($name)
    {
        $setter = 'set' . ucfirst($name);
        if (method_exists($this, $setter)){
            $this->$setter(null);
        }
    }
}