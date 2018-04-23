<?php

use yii\gii\generators\crud\Generator;
return [
    'vendorPath' => dirname(dirname(__DIR__)).'/vendor',
    'runtimePath' => dirname(dirname(__DIR__)).'/runtime',
    'timezone' => 'PRC',
    'language' => 'zh-CN',
    'name' => '摩申',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => '127.0.0.1',
            'port' => 6379,
            'database' => 0,
        ],
    ],
    'modules' => [
        'gii' => [
            'class' => 'yii\gii\Module',
            'generators' => [
                'crud' => [ //name generator
                    'class' => 'yii\gii\generators\crud\Generator', //class generator
                    'templates' => [ //setting for out templates
                        'vl' => '@yii/gii/generators/crud/vl', //name template => path to template
                    ]
                ]
            ],
            'allowedIPs' => ['*', '127.0.0.1'],
        ]
    ],
    'aliases' => [
        '@common/logic' => '@common/models/logic',
    ],
];
