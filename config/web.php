<?php

$params = require(__DIR__ . '/params.php');
$db = require(__DIR__ . '/db.php');
$adminmenu = require(__DIR__ . '/adminmenu.php');

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'defaultRoute' => 'index',
    'language' => 'zh-CN',
    'timeZone' => 'Asia/Shanghai',
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => '5X1P_jMXUPo5iW8zo7mAecFvp0-6XdzQ',
        ],
        //缓存配置
//        'cache' => [
//            'class' => 'yii\caching\FileCache',
//        ],
        //使用redis配置缓存
        'cache' => [
            'class' => 'yii\redis\Cache',
            'redis' => [
                'hostname' => 'localhost',
                'port' => 6770,
                'database' => '8',
                'password' => '123456'
              ]
        ],
        //使用redis配置session
        'session' => [
            'class' => 'yii\redis\Session',
            'redis' => [
                'hostname' => 'localhost',
                'port' => 6770,
                'database' => '7',
                'password' => '123456'
            ],
            'keyPrefix' => 'lvfk_sess_',//设置前缀
        ],
        //以下分别对前后台用户认证配置，并改写前后台认证后cookie和session的键值名idParam、identityCookie
        'user' => [//前台用户认证配置
            'loginUrl' => ['member/auth'],
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
            'enableSession' => true,
            'idParam' => '__user',//session aliases
            'identityCookie' => ['name'=>'__user_identity', 'httpOnly'=>true]
        ],
        'admin' => [//后台用户认证配置
            'class' => 'yii\web\User',
            'identityClass' => 'app\modules\models\Admin',
            'loginUrl' => ['admin/public/login'],
            'enableAutoLogin' => true,
            'idParam' => '__admin',//session aliases
            'identityCookie' => ['name'=>'__admin_identity', 'httpOnly'=>true]
        ],
        'errorHandler' => [
            'errorAction' => 'index/error',
        ],
//        'mailer' => [
//            'class' => 'yii\swiftmailer\Mailer',
//            // send all mails to a file by default. You have to set
//            // 'useFileTransport' to false and configure a transport
//            // for the mailer to send real emails.
//            'useFileTransport' => false,
//            'transport' => [
//              'class' => 'Swift_SmtpTransport',
//              'host' => 'smtp.163.com',
//              'username' => 'just_shunjian@163.com',
//              'password' => 'Lv,niuren01',
//              'port' => '465',
//              'encryption' => 'ssl',
//          ],
//        ],
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
        //日志配置，分类存储
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    'logFile' => '@app/runtime/logs/shop/application.log',//指定error和warning存储的文件
                    'categories' => ['lvfk'],//如果不指定，日志中会包含yii框架的自带的部分日志,在 \Yii::info第二个参数
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info', 'trace'],
                    'logFile' => '@app/runtime/logs/shop/info.log',
                    'categories' => ['lvfk'],//如果不指定，日志中会包含yii框架的自带的部分日志，在 \Yii::warning第二个参数
                    //'logVars' => ['_SERVER','_SESSION','_COOKIE'],//日志只包含SERVER、_SESSION、_COOKIE
                    'logVars' => [],//不包含其他任何信息，只保留我们的日志
                ],
//                [//此配置会影响用户等待发送邮件的的时间，体验不好，不应当使用
//                    'class' => 'yii\log\EmailTarget',
//                    'mailer' => 'mailer',//指定上面配置的mailer组件名字
//                    'levels' => ['error', 'warning'],
//                    'message' => [
//                        'from' => 'lvfk@163.com',
//                        'to' => 'test@qq.com',
//                        'subject' => 'shop的日志'
//                    ]
//                ]
            ],
        ],
        //使用sentry （https://github.com/hellowearemito/yii2-sentry）代替yii2系统的日志服务
//        'sentry' => [
//            'class' => 'mito\sentry\Component',
//            'dsn' => 'https://df78454006e540239cf874cd88b6b918:defc671879d6457eb1452101780492b7@sentry.io/283615', // private DSN
//            'publicDsn' => 'https://df78454006e540239cf874cd88b6b918@sentry.io/283615',
//            'environment' => 'staging', // if not set, the default is `production`
//            'jsNotifier' => true, // to collect JS errors. Default value is `false`
//            'jsOptions' => [ // raven-js config parameter
//                'whitelistUrls' => [ // collect JS errors from these urls
////                    //默认不配置，代表收集所有JS的问题
//                ],
//            ],
//        ],
//        'log' => [
//            'targets' => [
//                [
//                    'class' => 'mito\sentry\Target',
//                    'levels' => ['error', 'warning'],
//                    'except' => [//除了404异常不处理，其他都处理
//                        'yii\web\HttpException:404',
//                    ],
//                ],
//            ],
//        ],

        'db' => $db,
        //要使用urlManager美化URL，需要配置Nginx
        /*
         * Nginx
         * location / {
         *      try_files $uri $uri/ /index.php?$args;
         * }
         *
         */
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'suffix' => '.html',
            'rules' => [
                //http://shop.com/cart.html =》 http://shop.com/cart/index.html
                '<controller:(index|cart|order)>' => '<controller>/index',
                'auth' => 'member/auth',
                'product-category-<cateid:\d+>' => 'product/index',
                'product-<productid:\d+>' => 'product/detail',
                'order-check-<orderid:\d+>' => 'order/check',
                [
                    'pattern' => 'back',
                    'route' => '/admin/default/index',
                    'suffix' => '.html'
                ]
            ],
        ],

        //资源文件压缩
        'assertManager' => [
            'class' => 'yii\web\AssertManager',
            'bundle' => [
                'yii\web\JqueryAssert' => [
                    'js' => [
                        YII_ENV_DEV ? 'jquery.js' : 'jquery.min.js'
                    ]
                ],
                'yii\web\BootstrapAssert' =>[
                    'css' => [
                        YII_ENV_DEV ? 'css/bootstrap.css' : 'css/bootstrap.min.css'
                    ]
                ],
                'yii\web\BootstrapPluginAssert' =>[
                    'js' => [
                        YII_ENV_DEV ? 'js/bootstrap.js' : 'js/bootstrap.min.js'
                    ]
                ]

            ]
        ],
        //RBAC管理器
        'authManager' => [
            //            'class' => 'yii\rbac\PhpManager',//文件存储方式
            'class' => 'yii\rbac\DbManager',//数据库存储方式，更为安全
            //设置默认权限为游客角色
            'defaultRoles' => ['Guest'],
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
        //elasticsearch搜索服务器,在YII2中使用组件访问es,https://github.com/yiisoft/yii2-elasticsearch
        'elasticsearch' => [
            'class' => 'yii\elasticsearch\Connection',
            'nodes' => [
                ['http_address' => '10.168.1.216:9200'],
                // configure more hosts if you have a cluster
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
        ]
    ],
    'params' => array_merge($params, ['adminmenu'=>$adminmenu]),
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['10.168.1.216'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

}
$config['modules']['admin'] = [
    'class' => 'app\modules\admin',
];
return $config;
