<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-backend',
    'language' => 'zh-CN',
    'timeZone'=>'Asia/Chongqing',           //时区
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'backend\controllers',
    'params' => $params,
    'components' => [
        'user' => [
            'identityClass' => 'backend\models\User',
            'enableAutoLogin' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
        'urlManagerF' => [
            'class'     =>  'yii\web\urlManager',
            'baseUrl'   =>  $params['frontendUrl'],
            'suffix'    =>  '.html',
            'enablePrettyUrl'   => true,
            'showScriptName'    => false,
            'rules' => [
                'product/<id:\d+>' => 'product/details',
            ],
        ],
        "authManager" => [
            "class" => 'yii\rbac\DbManager', //这里记得用单引号而不是双引号        
            // "defaultRoles" => ["guest"],    
        ], 
//预览adminlte
//         'view' => [
//             'theme' => [
//                 'pathMap' => [
//                     '@app/views' => '@vendor/dmstr/yii2-adminlte-asset/example-views/yiisoft/yii2-app'
//                 ],
//             ],
//         ],
//         'assetManager' => [
//             'bundles' => [
//                 'dmstr\web\AdminLteAsset' => [
//                     'skin' => 'skin-black',//配置adminlte皮肤
//                 ],
//             ],
//         ],
//         'i18n' => [
//             'translations' => [
//                 '*' => [
//                     'class' => 'yii\i18n\PhpMessageSource',
//                     'basePath' => '@app/messages', // if advanced application, set @frontend/messages
//                     'sourceLanguage' => 'zh',//en zh cn
//                     'fileMap' => [
//                         //'main' => 'main.php',
//                     ],
//                 ],
//             ],
//         ],
    ],
    //配置yii2-admin
    // 'aliases' => [
    //     '@mdm/admin' => '$PATH\yii2-admin-1.0.3',
    // ],
    "aliases" => [    
        "@mdm/admin" => "@vendor/mdmsoft/yii2-admin",
    ],
    'modules' => [
        'admin' => [
            'class' => 'mdm\admin\Module',
            'layout' => 'left-menu', // it can be '@path/to/your/layout'.
            'controllerMap' => [
                 'assignment' => [
                     'class' => 'mdm\admin\controllers\AssignmentController',
                     'userClassName' => 'backend\models\User',
                     'idField' => 'id'
                 ]
             ],
            'menus' => [
                'assignment' => [
                    'label' => 'Grand Access' // change label
                ],
                //'route' => null, // disable menu route
            ]
        ],
        'debug' => [
            'class' => 'yii\debug\Module',
        ],
        'redactor' => [ 
            'class' => 'backend\components\RedactorModule', //yii\redactor\RedactorModule
            'uploadDir' => '@frontend/web/uploads/article',
            'uploadUrl' => $params['frontendUrl'].'uploads/article',
            'imageAllowExtensions'=>[],
        ], 
    ],
    'as access' => [
        'class' => 'mdm\admin\components\AccessControl',
        'allowActions' => [
            // 'site/*',
            'site/login',
            'site/logout',
            // 'site/*',//允许访问的节点，可自行添加
            // 'admin/*',//允许所有人访问admin节点及其子节点
            'some-controller/some-action',
            // The actions listed here will be allowed to everyone including guests.
            // So, 'admin/*' should not appear here in the production, of course.
            // But in the earlier stages of your development, you may probably want to
            // add a lot of actions here until you finally completed setting up rbac,
            // otherwise you may not even take a first step.
        ]
    ],
    'on beforeRequest' => function($event) {
        \yii\base\Event::on(\yii\db\BaseActiveRecord::className(), \yii\db\BaseActiveRecord::EVENT_AFTER_INSERT, ['backend\components\AdminLog', 'write']);
        \yii\base\Event::on(\yii\db\BaseActiveRecord::className(), \yii\db\BaseActiveRecord::EVENT_AFTER_UPDATE, ['backend\components\AdminLog', 'write']);
        \yii\base\Event::on(\yii\db\BaseActiveRecord::className(), \yii\db\BaseActiveRecord::EVENT_AFTER_DELETE, ['backend\components\AdminLog', 'write']);
    },
    //end
];

