<?php
return [
    'db' => [
        'dsn' => 'oci8:dbname=37.221.185.180:1521/oratest',
        'username' => 'EMS',
        'password' => 'APX64Lin',
        'charset' => 'AL32UTF8',
        'attributes' => [
            PDO::ATTR_STRINGIFY_FETCHES => true
        ]
    ],
    'params' => require_once 'params.php'
];