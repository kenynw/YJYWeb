<?php

namespace frontend\controllers;

use Yii;
use frontend\controllers\BaseController;
use frontend\models\WebPage;

class ComponentController extends BaseController
{

    public function actionDetails($id,$page='1')
    {
        $webPage = new WebPage();

        //成分详情
        $details = $webPage->getComponentDetails($id);

        //含有成分产品列表
        $productList = $webPage->getCompomentProductList($page,$pageSize = '16',$component_id = $id,$orderBy = '');
        //相似成分列表
        $compomentList = $webPage->getSimilarCompoment($id,$details['component_action']);

        //广告列表
        $advertisementList = [
            "main1" => $webPage->getAdList($type = "component/index",$position = "main",$sort = "1"),
            "left1" => $webPage->getAdList($type = "component/index",$position = "left",$sort = "1"),
        ];

        //标题修改
        $this->GLOBALS['title'] = $details['name'] . '简介_同成分产品-颜究院';
        $this->GLOBALS['description'] = $details['description'];
        $this->GLOBALS['keywords'] = $details['name'] . '，' . $details['name'] . '作用，' . $details['name'] . '有害吗';

        return $this->renderPartial('details.htm',[
            'details' => $details,
            'productList' => $productList,
            'compomentList' => $compomentList,
            'advertisementList' => $advertisementList,
            'GLOBALS' => $this->GLOBALS,
        ]);
    }

}
