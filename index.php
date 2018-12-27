<?php


require_once 'vendor/autoload.php';
require_once 'autoload.php';

$config = require_once 'config/config.php';
$app = new \app\core\Application($config);
$app->run();