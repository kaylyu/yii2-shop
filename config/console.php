<?php

$params = require(__DIR__ . '/params.php');
$db = require(__DIR__ . '/db.php');

$config = [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'app\commands',
    'aliases' => [
        '@lvfk/mailerqueue' => '@vendor/lvfk/mailerqueue/src'
    ],
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info'],
                    'categories' => ['testkafka'],
                    'exportInterval' => 1,
                    'logVars' => [],//不包含其他任何信息，只保留我们的日志
                    'logFile' => '@app/runtime/logs/kafka/info.log'
                ]
            ],
        ],
        //RBAC管理器
        'authManager' => [
            //            'class' => 'yii\rbac\PhpManager',//文件存储方式
            'class' => 'yii\rbac\DbManager',//数据库存储方式，更为安全
            //auth_item 角色和权限
            //auth_item_child   角色分配权限
            //auth_assignment   用户分配角色
            //auth_rule 验证的规则
            //可以用以下配置，指定到我们定义的表名
            'itemTable' => '{{%auth_item}}',
            'itemChildTable' => '{{%auth_item_child}}',
            'assignmentTable' => '{{%auth_assignment}}',
            'ruleTable' => '{{%auth_rule}}'
            //以上四张表我们可以通过YII2提供的方式自动创建，
            //可以参考：vendor\yiisoft\yii2\rbac\migrations\schema-mysql.sql
            //创建命令:通过根目录下的yii这个文件进行创建
            //创建之前需要配置下yii中的console.php配置文件
            //进入项目根目录可以使用./yii 进行命令查看
            //执行命令为：./yii migrate --migrationPath=@yii/rbac/migrations
        ],
        //自定义mailer组件
        'mailer' => [
            'class' => 'lvfk\mailerqueue\Mailer',
            'db' => 9,//设置redis使用库
            'key' => 'mails',//redis 列表名字
            'useFileTransport' => false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.163.com',
                'username' => 'lvfk@163.com',
                'password' => '123456',
                'port' => '465',
                'encryption' => 'ssl',
            ],
        ],
        //redis 操作组件配置
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => 'localhost',
            'port' => 6770,
            'database' => 9,
            'password'  => '123456',
        ],
        //配置kafka生产者
        'asyncLog' => [
            'class' => '\\app\\models\\Kafka',
            'broker_list' => '10.168.1.99:9092',
            'topic' => 'asynclog'
        ],
        'db' => $db,
    ],
    'params' => $params,
    /*
    'controllerMap' => [
        'fixture' => [ // Fixture generation command line.
            'class' => 'yii\faker\FixtureController',
        ],
    ],
    */
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
