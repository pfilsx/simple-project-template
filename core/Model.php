<?php


namespace app\core;


class Model extends BaseObject
{
    protected $_errors = [];

    protected static function rules(){
        return [];
    }
    /**
     * @param array $data
     * @return bool
     */
    public function load($data){
        $flag = false;
        if (is_array($data)){
            foreach ($data as $key => $value){
                if ($this->canSetProperty($key)){
                    $this->$key = $value;
                    $flag = true;
                }
            }
        }
        return $flag;
    }

    /**
     * @param null|string $attribute
     * @return array|null
     */
    public function getErrors($attribute = null){
        if ($attribute == null){
            return $this->_errors;
        } else {
            return $this->_errors[$attribute];
        }
    }

    /**
     * @param null|string $attribute
     * @return bool
     */
    public function hasErrors($attribute = null){
        if ($attribute == null){
            return !empty($this->_errors);
        } else {
            return !empty($this->_errors[$attribute]);
        }
    }
    /**
     * @param string $attribute
     * @param string $message
     */
    public function addError($attribute, $message){
        if (array_key_exists($attribute, $this->_errors)){
            $this->_errors[$attribute][] = $message;
        } else {
            $this->_errors[$attribute] = [$message];
        }
    }

    /**
     * @param null|string $attribute
     * @return bool
     */
    public function validate($attribute = null){
        foreach (static::rules() as $rule){
            if (!is_array($rule) || !isset($rule[0]) || !isset($rule[1]))
                continue;
            list($attributes, $callback) = $rule;
            if (!is_callable($callback)) continue;
            if (!is_array($attributes)){
                $attributes = [$attributes];
            }
            if ($attribute != null && array_search($attribute, $attributes) === false)
                continue;
            foreach ($attributes as $attr){
                $this->validateAttribute($attr, $callback);
            }
        }
        return !$this->hasErrors($attribute);
    }

    /**
     * @param $attribute
     * @param callable $validator
     */
    private function validateAttribute($attribute, callable $validator){
        if ($this->hasProperty($attribute)){
            call_user_func($validator, $this, $attribute, $this->$attribute);
        }
    }

}