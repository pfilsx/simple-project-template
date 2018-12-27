<?php


namespace app\core;


use app\models\TestModel;
use Exception;
use pfilsx\db_broker\Connection;

/**
 * @property-read Connection|null $db
 * @property-read array $config
 * @property-read array $params
 * @property-read $controller
 * @property $view
 */
class Application extends BaseObject
{

    /**
     * @var null|Application
     */
    public static $instance;
    /**
     * @var Connection|null|array
     */
    private $_db = null;
    /**
     * @var array
     */
    private $_config = null;
    /**
     * @var array
     */
    private $_params = null;

    private $_controller = null;

    public $view = null;

    public function __construct(array $config = [])
    {
        $this->_config = $config;
        parent::__construct($config);
    }

    protected function init()
    {
        if (isset($this->_config['db'])){
            unset($this->_config['db']);
        }
        static::$instance = $this;
    }

    public function run(){
    }

    /**
     * @return Connection|null
     */
    public function getDb(){
        return $this->_db;
    }

    /**
     * @param Connection|array $value
     */
    protected function setDb($value){
        if ($value instanceof Connection){
            $this->_db = $value;
        } elseif (is_array($value)){
            $this->_db = new Connection($value);
        } else {
            throw new Exception("Invalid configuration: Db must be an array or Connection instance");
        }
    }

    /**
     * @return array
     */
    public function getConfig(){
        return $this->_config;
    }

    /**
     * @param array $value
     */
    protected function setConfig($value){
        $this->_config = $value;
    }

    /**
     * @return array
     */
    public function getParams(){
        return $this->_params;
    }

    /**
     * @param array $value
     */
    protected function setParams($value){
        $this->_params = $value;
    }
    /**
     * @return mixed
     */
    public function getController()
    {
        return $this->_controller;
    }
    /**
     * @param mixed $controller
     */
    protected function setController($controller)
    {
        $this->_controller = $controller;
    }

}