<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=aphamea',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
    'attributes'=>[
        PDO::ATTR_EMULATE_PREPARES=>false
    ],
    

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
