<?php

use leinonen\Yii2Eloquent\MigrateController;
use leinonen\Yii2Eloquent\Yii2Eloquent;

return [
    'id' => 'testconsoleapp',
    'basePath' => dirname(__DIR__),
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'bootstrap' => ['db'],
    'components' => [
        'db' => [
            'class'     => Yii2Eloquent::class,
            'driver'    => getenv('DB_DRIVER'),
            'database'  => getenv('DB_NAME'),
            'prefix'    => '',
            'host'      => getenv('DB_HOST'),
            'username'  => getenv('DB_USERNAME'),
            'password'  => getenv('DB_PASSWORD'),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',

        ]
    ],
    'controllerMap' => [
        'migrate' => [
            'class' => MigrateController::class,
            'migrationPath' => __DIR__ . '/../migrations'
        ]
    ]
];
