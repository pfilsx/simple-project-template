<?php


namespace app\core;


use Exception;
use pfilsx\db_broker\Connection;
use Throwable;

/**
 * @property-read array $attributes
 */
class ActiveRecord extends Model
{
    protected $_attributes = [];
    protected $oldAttributes = [];

    public $isNewRecord = true;

    public static function getTableName(){
        return '';
    }
    public static function getPrimaryKey(){
        return '';
    }
    /**
     * @return Connection
     */
    public static function getDb(){
        return Application::$instance->getDb();
    }

    public function getAttributes(){
        return $this->_attributes;
    }

    /**
     * @param array $data
     * @return bool
     */
    public function load($data){
        $flag = false;
        foreach ($data as $key => $value){
            if (array_key_exists($key, $this->_attributes)){
                $this->$key = $value;
                $flag = true;
            }
        }
        return $flag;
    }

    protected function beforeSave(){
        return true;
    }

    public function save(){
        if ($this->beforeSave() !== false){
            if ($this->isNewRecord){
                $result = $this->insert();
                $insert = true;
            } else {
                $result = $this->update();
                $insert = false;
            }
            if ($result === true){
                $this->afterSave($insert);
                return $result;
            }
        }
        return false;
    }

    public function afterSave($insert){

    }

    /**
     * @return bool
     */
    protected function insert(){
        $transaction = static::getDb()->beginTransaction();
        try {
            $result = $this->insertInternal();
            if ($result === false) {
                $transaction->rollBack();
            } else {
                $transaction->commit();
                $this->isNewRecord = false;
                $this->oldAttributes = [];
            }
            return $result;
        } catch (Exception $e) {
            $transaction->rollBack();
            throw $e;
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    protected function insertInternal()
    {
        $columns = $this->oldAttributes;
        $values = array_filter($this->_attributes,
            function($key) use ($columns) {
                return array_key_exists($key, $columns);
            },
            ARRAY_FILTER_USE_KEY);
        if (($primaryKeys = static::getDb()->schema->insert(static::getTableName(), $values)) === false) {
            return false;
        }
        foreach ($primaryKeys as $name => $value) {
            $id = static::getTableSchema()->columns[$name]->phpTypecast($value);
            $this->$name = $id;
        }
        return true;
    }

    /**
     * @return bool
     */
    protected function update(){
        $transaction = static::getDb()->beginTransaction();
        try {
            $result = $this->updateInternal();
            if ($result === false) {
                $transaction->rollBack();
            } else {
                $transaction->commit();
            }
            return $result;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    protected function updateInternal(){
        if (empty($this->oldAttributes)){
            return true;
        }
        $columns = $this->oldAttributes;
        $values = array_filter($this->_attributes,
            function($key) use ($columns) {
                return array_key_exists($key, $columns);
            },
            ARRAY_FILTER_USE_KEY);
        $primaryKey = static::getPrimaryKey();
        $result = static::getDb()->createCommand()
            ->update(static::getTableName(), $values, [$primaryKey => $this->$primaryKey])->execute();
        if ($result != 0){
            $this->oldAttributes = [];
            return true;
        }
        return false;
    }

    /**
     * @param array $params
     * @return \pfilsx\db_broker\Query
     */
    public static function find($params = []){
        $query = static::getDb()->createQuery();
        $query->select('*')->from(static::getTableName());
        if (!empty($params)){
            $query->where($params);
        }
        return $query;
    }

    /**
     * @param array $params
     * @return static[] an array of ActiveRecord instances, or an empty array if nothing matches
     */
    public static function findAll($params = []){
        $data = static::find($params)->all();
        $result = [];
        if (is_array($data) && !empty($data)){
            foreach ($data as $value){
                $className = get_called_class();
                /**
                 * @var ActiveRecord $model
                 */
                $model = new $className();
                $model->load($value);
                $model->oldAttributes = [];
                $model->isNewRecord = false;
                $result[] = $model;
            }
        }
        return $result;
    }

    /**
     * @param $params
     * @return static|null
     */
    public static function findOne($params){
        if (is_array($params)){
            $query = static::find($params);
        } elseif (!empty($params)) {
            $query = static::find([static::getPrimaryKey() => $params]);
        } else {
            return null;
        }
        $data = $query->one();
        if (is_array($data) && !empty($data)){
            /**
             * @var ActiveRecord $model
             */
            $className = get_called_class();
            $model = new $className($data[0]);
            $model->oldAttributes = [];
            $model->isNewRecord = false;
            return $model;
        }
        return null;
    }


    public static function getTableSchema()
    {
        $tableSchema = static::getDb()
            ->getSchema()
            ->getTableSchema(static::getTableName());
        if ($tableSchema === null) {
            throw new Exception('The table does not exist: ' . static::getTableName());
        }
        return $tableSchema;
    }


    //region magic
    public function canGetProperty($name)
    {
        return array_key_exists($name, $this->_attributes) || parent::canGetProperty($name);
    }

    public function canSetProperty($name)
    {
        return array_key_exists($name, $this->_attributes) || parent::canSetProperty($name);
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->_attributes)){
            return $this->_attributes[$name];
        }
        return parent::__get($name);
    }

    public function __set($name, $value)
    {
        if (array_key_exists($name, $this->_attributes)){
            if ($this->_attributes[$name] !== $value){
                $this->oldAttributes[$name] = $this->_attributes[$name];
                $this->_attributes[$name] = $value;
            }
            return;
        }
        parent::__set($name, $value);
    }

    public function __isset($name)
    {
        return array_key_exists($name, $this->_attributes) || parent::__isset($name);
    }

    public function __unset($name)
    {
        if (array_key_exists($name, $this->_attributes)){
            $this->_attributes[$name] = null;
            return;
        }
        parent::__unset($name);
    }
    //endregion

}