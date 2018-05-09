<?php

namespace frontend\modules\h5;

use Yii;
use yii\filters\AccessControl;
/**
 * h5 module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'frontend\modules\h5\controllers';

    //权限判断（是否登录）
//    public function behaviors()
//    {
//        return [
//            'access' => [
//                'class' => AccessControl::className(),
//                'denyCallback' => function($rule, $action){
//                    if (\Yii::$app->user->isGuest)
//                    {
//                        \Yii::$app->user->loginUrl = ['h5/site/login'];
//                        \Yii::$app->user->loginRequired();//游客跳转到登陆界面
//                    }
//                    else
//                        throw new ForbiddenHttpException("您没有权限访问这个网页");//普通用户提示"您没有权限访问这个网页"
//                },
//                'rules' => [
//                    [
//                        'allow' => true,
//                        'actions' => ['login'],
//                        'roles' => ['?'],
//                    ],
//                    [
//                        'allow' => true,
//                        'roles' => ['@'],
//                    ],
//                ],
//            ],
//        ];
//    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    }
}
