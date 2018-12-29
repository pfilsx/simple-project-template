<?php


namespace app\core;


use Exception;
use pfilsx\db_broker\Query;

class ActiveQuery extends Query
{
    protected $_model;

    /**
     * @param string $className
     * @return $this
     * @throws Exception
     */
    public function setModel($className){
        if (!class_exists($className) || !is_subclass_of($className, ActiveRecord::class)){
            throw new Exception('Model class must extend ActiveRecord class');
        }
        $this->_model = $className;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getModel(){
        return $this->_model;
    }

    /**
     * @return array
     */
    public function all(){
        $data = parent::all();
        if ($this->_model == null){
            return $data;
        }
        $result = [];
        foreach ($data as $value){
            $className = $this->_model;
            /**
             * @var ActiveRecord $model
             */
            $model = new $className();
            $model->load($value);
            $model->clearOldAttributes();
            $model->isNewRecord = false;
            $result[] = $model;
        }
        return $result;
    }

    /**
     * @return array|null|ActiveRecord
     */
    public function one(){
        $data = parent::one();
        if ($this->_model == null){
            return $data;
        }
        if ($data != false){
            $className = $this->_model;
            /**
             * @var ActiveRecord $model
             */
            $model = new $className($data);
            $model->clearOldAttributes();
            $model->isNewRecord = false;
            return $model;
        }
        return null;
    }
}