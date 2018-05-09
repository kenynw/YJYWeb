<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use backend\models\LoginForm;
use common\models\ProductDetails;
use common\models\ProductDetailsSearch;
use common\models\BrandProductDetailsSearch;
use backend\models\CommonFun;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    // public function behaviors()
    // {
    //     return [
    //         'access' => [
    //             'class' => AccessControl::className(),
    //             'rules' => [
    //                 [
    //                     'actions' => ['login', 'error'],
    //                     'allow' => true,
    //                 ],
    //                 [
    //                     'actions' => ['logout', 'index','test1'],
    //                     'allow' => true,
    //                     'roles' => ['@'],
    //                 ],
    //             ],
    //         ],
    //         'verbs' => [
    //             'class' => VerbFilter::className(),
    //             'actions' => [
    //                 'logout' => ['post'],
    //             ],
    //         ],
    //     ];
    // }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new ProductDetailsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        
        //品牌添加产品弹窗
        $searchModel2 = new BrandProductDetailsSearch();
        $dataProvider2 = $searchModel2->search(Yii::$app->request->queryParams);
        
        //分类
        $cateList = CommonFun::getCateList($where = 'parent_id <> 0');

        return $this->render('../product-details/index', [
            'cateList' => $cateList,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'searchModel2' => $searchModel2,
            'dataProvider2' => $dataProvider2,
        ]);

        //return $this->render('index');
    }

    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
                'msg' => $model->load(Yii::$app->request->post()) ? "账号或密码错误" : ""
            ]);
        }
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }
}
