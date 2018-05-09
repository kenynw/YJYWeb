<?php

namespace frontend\controllers;

use Yii;
use frontend\controllers\BaseController;
use frontend\models\WebPage;

class BrandController extends BaseController
{
    public function actionIndex($page = '1',$cateId = '')
    {
        $webPage   = new WebPage();

        //品牌分类
        $brandCate = $webPage->getBrandCateList();
        //品牌列表
        $brandList = $webPage->getBrandList($page,$pageSize = '30',$cateId,$recommend = '',$orderBy = 'hot desc');

        //推荐产品
        $recommendProduct = $webPage->getRecommendProduct($page = '1',$pageSize = "5");
        //推荐文章
        $hotArticle = $webPage->getHotArticle($page = '1',$pageSize = "5",'','1');

        //广告列表
        $advertisementList = [
            "main1" => $webPage->getAdList($type = "brand/index",$position = "main",$sort = "1"),
            "left1" => $webPage->getAdList($type = "brand/index",$position = "left",$sort = "1"),
        ];


        //标题修改
        $this->GLOBALS['title'] = '护肤品品牌大全_欧美_日韩_国产护肤品品牌列表-颜究院';
        $this->GLOBALS['keywords'] = '护肤品品牌，护肤品品牌大全，欧美护肤品品牌，日韩护肤品品牌，国产护肤品品牌';

        if($cateId){
            foreach($brandCate as $key=>$val){
                if($val['id'] == $cateId){
                    $this->GLOBALS['title'] = $val['name'] . '护肤品品牌大全-颜究院';
                    $this->GLOBALS['keywords'] = $val['name'] . '护肤品品牌，' . $val['name'] . '护肤品品牌大全';
                    break;
                }
            }
        }

        return $this->renderPartial('index.htm', [
            'brandCate' => $brandCate,
            'brandList' => $brandList,
            'recommendProduct' => $recommendProduct,
            'hotArticle' => $hotArticle,
            'advertisementList' => $advertisementList,
            'GLOBALS' => $this->GLOBALS,
        ]);
    }

    public function actionDetails($id)
    {
        $webPage = new WebPage();
        //品牌详情
        $details = $webPage->getBrandDetails($id);

        //产品排行榜
        $brandList = $webPage->getProductList($page = '1',$pageSize = '10',$cateId = '',$brandId = $id,$keyword = '',$recommend = '',$top='1',$orderBy = 'comment_num desc,star desc');

        //评论
        if($brandList['list']){
            foreach($brandList['list'] as $key=>$val){
                if(empty($val['product_explain'])){
                    $sql = "SELECT c.*,u.img FROM {{%comment}} c LEFT JOIN {{%user}} u ON c.user_id = u.id
                            WHERE c.post_id = '{$val['id']}' AND c.type=1 AND c.status = 1 ORDER BY c.is_digest DESC ,c.created_at DESC limit 1";
                    $brandList['list'] [$key]['comment']  = Yii::$app->db->createCommand($sql)->queryOne();
                }
            }
        }

        //品牌产品
        $brandProduct = $webPage->getProductList($page = '1',$pageSize = '16',$cateId = '',$brandId = $id,$keyword = '',$recommend = '',$top='',$orderBy = 'is_recommend desc,comment_num desc,star desc');

        //其他推荐品牌
        $recommendBrand = $webPage->getRecommendBrand(12,$id);

        //品牌文章
        $brandArticle = $webPage->getBrandArticle($page = '1',$pageSize = '5',$id);

        //广告列表
        $advertisementList = [
            "main1" => $webPage->getAdList($type = "brand/details",$position = "main",$sort = "1"),
            "main2" => $webPage->getAdList($type = "brand/details",$position = "main",$sort = "2"),
        ];

        //标题修改
        $this->GLOBALS['title'] = $details['ename'] . $details['name'] .'护肤品排行_' . $details['name'] . '护肤品推荐_' . $details['name'] . '价格表-颜究院';
        $this->GLOBALS['description'] = mb_substr(strip_tags($details['description']),0,100,"utf-8");
        $this->GLOBALS['keywords'] = $details['name'] . '护肤品排行，' . $details['name'] . '护肤品价格表，' . $details['name'] . '护肤品推荐';

        return $this->renderPartial('details.htm',[
            'details' => $details,
            'brandList' => $brandList,
            'brandProduct' => $brandProduct,
            'recommendBrand' => $recommendBrand,
            'brandArticle' => $brandArticle,
            'advertisementList' => $advertisementList,
            'GLOBALS' => $this->GLOBALS,
            'equipment' => $this->GLOBALS['equipment']
        ]);
    }

    //ajax返回数据
    public function actionAjaxIndexData($id,$page,$type)
    {
        $webPage   = new WebPage();
        if($type == 'product'){
            //品牌产品
            $list = $webPage->getProductList($page,$pageSize = '16',$cateId = '',$brandId = $id,$keyword = '',$recommend = '',$top='',$orderBy = 'is_recommend desc,comment_num desc,star desc');
        }else if($type == 'article'){
            //品牌文章
            $list = $webPage->getBrandArticle($page,$pageSize = '5',$id);
        }


        $result = $this->renderPartial('data.htm', [
            'list' => $list,
            'type' => $type,
            'GLOBALS' => $this->GLOBALS,
        ]);

        echo json_encode($result);
    }

}
