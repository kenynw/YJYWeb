<?php

namespace frontend\modules\h5\controllers;

use Yii;
use yii\web\Controller;
use frontend\models\WebPage;
use common\functions\Tools;

/**
 * Site controller
 */
class SiteController extends BaseController
{
    public $layout = '@app/modules/h5/views/layouts/main.php';
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
        header("Content-type: text/html; charset=utf-8");
        $webPage   = new WebPage();

        //热门搜索列表
        $searchList = $webPage->getHotKeyword(10);

        //分类列表
        $cateList = $webPage->getProductCateList();

        //推荐产品列表
        $productList = $webPage->getProductList('1','8','','','1');

        //推荐文章列表
        $articleList = $webPage->getArticleList('1','8','','1');

        return $this->renderPartial('index.htm', [
            'searchList' => $searchList,
            'cateList' => $cateList,
            'productList' => $productList,
            'articleList' => $articleList,
            'GLOBALS' => $this->GLOBALS,
            'Tools' => new Tools,
        ]);
    }

    public function actionLogin()
    {
        echo 1;
        exit;
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }
}
