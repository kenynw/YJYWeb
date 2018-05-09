<?php

namespace frontend\modules\h5\controllers;

use Yii;
use yii\web\Controller;
use frontend\models\WebPage;
use common\functions\Tools;

/**
 * Site controller
 */
class ProductController extends BaseController
{
    public $layout = '@app/modules/h5/views/layouts/main.php';

    public function actionDetails($id)
    {
        $webPage   = new WebPage();
        $productDetails = $webPage->getProductDetails($id);

        $componentList = array();
        if($productDetails['list']['component_id']){
            $componentList = $webPage->getComponentList($productDetails['list']['component_id']);
        }

        return $this->renderPartial('details.htm', [
            'productDetails' => $productDetails['list'],
            'function_list' => $productDetails['function_list'],
            'safe_list' => $productDetails['safe_list'],
            'componentList' => $componentList,
            'GLOBALS' => $this->GLOBALS,
        ]);
    }

    public function actionSearch($page = '1',$pageSize = '20',$cateId = '0',$keyword = "")
    {
        $webPage   = new WebPage();
        $productList = $webPage->getProductList('1','20',$cateId,$keyword,'0');

        $recommendList = array();
        if(empty($productList['list'])){
            $recommendList = $webPage->getProductList('1','8','','','1');
        }

        return $this->renderPartial('search.htm', [
            'productList' => $productList,
            'recommendList' => $recommendList,
            'GLOBALS' => $this->GLOBALS,
        ]);
    }

}
