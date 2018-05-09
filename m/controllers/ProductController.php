<?php

namespace m\controllers;

use Yii;
use m\models\WebPage;
use m\controllers\BaseController;
use common\functions\SearchProduct;
use common\functions\Functions;
use m\models\Mfunctions;
use yii\data\Pagination;
/**
 * Site controller
 */
class ProductController extends BaseController
{
    public function actions(){
        $this->GLOBALS['TOP_SHOW'] = 'product';
    }

    public function actionDetails($id)
    {
        //产品详情
        $webPage   = new WebPage();
        $productDetails = $webPage->getProductDetails($id);
        //var_dump($productDetails);die;
        //成分列表
        $componentList = array();
        if($productDetails['list']['id']){
            $componentList = $webPage->getComponentList($productDetails['list']['id']);
        }

        $s_title     = $productDetails['list']['product_name'];
        $s_desc      = "科学分析产品成分，只有我们知道它适不适合你。";
        $s_imgUrl    = $productDetails['list']['product_img'];

        $this->GLOBALS['OfficialAccounts']['share']['title'] = $s_title;
        $this->GLOBALS['OfficialAccounts']['share']['desc']  = $s_desc;
        $this->GLOBALS['OfficialAccounts']['share']['imgUrl']  = $this->GLOBALS['uploadsUrl'].$s_imgUrl;

        //标题修改
        $this->GLOBALS['title'] = $productDetails['list']['product_name'] . '怎么样_成分分析-颜究院';
        $this->GLOBALS['description'] = $productDetails['list']['product_explain'];
        $this->GLOBALS['keywords'] = $productDetails['list']['product_name'] . '，' . $productDetails['list']['product_name'] . '怎么样，' . $productDetails['list']['product_name'] . '成分分析';
        return $this->renderPartial('details103.htm', [
            'cate_id'           => $productDetails['list']['cate_id'],
            'productDetails'    => $productDetails['list'],
            'function_list'     => $productDetails['function_list'],
            'safe_list'         => $productDetails['safe_list'],
            'buy'               => $productDetails['buy'],
            'link_buy'          => $productDetails['link_buy'],
            'componentList'     => $componentList,
            'RecommendAsk'      => Mfunctions::getAskList('',$productDetails['list']['id'],'A.add_time desc','','3')['list'],
            'RecommendProduct'  => Mfunctions::getProductList($productDetails['list']['cate_id'],$id,'is_recommend DESC,recommend_time DESC')['list'],
            'ProductComment'    => Mfunctions::getProductComment($id),
            'GLOBALS'           => $this->GLOBALS,
        ]);
    }

    public function actionSearch()
    {
        $page       = Yii::$app->request->get('page','1');
        $pageSize   = Yii::$app->request->get('pageSize','20');
        $cateId     = Yii::$app->request->get('cateId','');
        $keyword    = Yii::$app->request->get('keyword','');
        $cateName   = Yii::$app->request->get('cateName','');
        $effectId   = Yii::$app->request->get('effectId',0);
        $param = [
            "page"      => $page,
            "search"    => $keyword,
            "categroyId" => $cateId,
            "effect"    => $effectId,
            "pageSize"  => $pageSize
        ];
        //产品列表
        $productList = SearchProduct::searchProduct($param);

        $productList['list'] = $productList['data'];
        $recommendList  = [];

        if(empty($productList['data'])){
            $param_empty = ["page"      => '1',"search"    => $keyword, "recommend" => 1,"pageSize"  => 20];
            $recommendList = SearchProduct::searchProduct($param_empty);
            $recommendList['list'] = $recommendList['data'];
            unset($recommendList['data']);
        }
        unset($productList['data']);

        
         // 查询总数
        $totalCount = $productList['pageTotal'];
        $pages = new Pagination(['totalCount' => $totalCount]);
        // 设置每页显示多少条
        $pages->PageSize = $pageSize;
        $pages->Page = $page-1;
        $cate_name = '';
        if($cateId){
            $sql = "SELECT cate_name FROM {{%product_category}} WHERE id = '$cateId'";
            $cate_name  = Yii::$app->db->createCommand($sql)->queryScalar();
        }

        //存入热搜表
        if(!empty($keyword)){
            $hot_key_sql  = "SELECT COUNT(*) FROM {{%hot_keyword}} WHERE keyword = '$keyword'"; 
            $hot_num      = Yii::$app->db->createCommand($hot_key_sql)->queryScalar();
            if($hot_num > 0){
                $sql = "UPDATE {{%hot_keyword}} SET num = num + 1 WHERE keyword = '$keyword'";
            }else{
                $sql = "INSERT INTO  {{%hot_keyword}} (keyword,num) VALUES ('$keyword',1)";
            }
            Yii::$app->db->createCommand($sql)->execute();
        }

        //分享参数
        $this->GLOBALS['OfficialAccounts']['share']['title'] = $cate_name.'产品成分分析';
        $this->GLOBALS['OfficialAccounts']['share']['desc']  = '科学分析产品成分，只有我们知道它适不适合你。';

        //头部信息修改
        if($keyword) {
            if (!empty($productList['list'])) {
                $this->GLOBALS['title'] = $keyword . '相关的护肤品-颜究院';
            } else {
                $this->GLOBALS['title'] = '找不到搜索词相关的护肤品-颜究院';
            }

        }else if($cateId  || $effectId){

            $data = '';

            if($effectId){
                $effectList = Functions::effectList();
                foreach($effectList as $val){
                    if($effectId == $val['effect_id']){
                        $data .= $val['effect_name'];
                        break;
                    }
                }
            }

            if($cateId){
                $data .= $cate_name;
            }

            $this->GLOBALS['title'] = $data . '怎么样_' . $data . '产品成分分析-颜究院';
            $this->GLOBALS['description'] = '提供' . $data . '怎么样、' . $data . '产品成分分析。了解更多可关注颜究院官方微博，官方微信和APP。';
            $this->GLOBALS['keywords'] = $data . '，' . $data . '怎么样，' . $data . '产品成分分析';

        }else{
            $this->GLOBALS['title'] = '护肤品成分查询_护肤品成分分析-颜究院';
            $this->GLOBALS['description'] = '颜究院提供专业的护肤品成分查询、护肤品成分分析。包括化妆水、精华、乳霜、眼霜、面膜等护肤品的成分查询，了解更多可关注颜究院官方微博，官方微信和APP。';
            $this->GLOBALS['keywords'] = '护肤品成分，护肤品成分查询，护肤品成分分析';
        }

        return $this->renderPartial('search103.htm', [
            'pages'             => Mfunctions::linkPager($pages),
            'RecommendAsk'      => Mfunctions::getAskList($cateId,'','A.add_time desc','','3')['list'],
            'RecommendArticle'  => Mfunctions::getArticleList('','','created_at DESC',$page = 1,$pageSize=5,$product_id = '',$product_cate_id = $cateId)['list'],
            'ProductCategory'   => Functions::getProductColumn(),
            'productList'       => $productList,
            'cate_name'         => $cate_name,
            'cate_id'         => $cateId,
            'recommendList'     => $recommendList,
            'GLOBALS'           => $this->GLOBALS,
        ]);
    }

    public function actionGetSearchData(){
        $page       = Yii::$app->request->get('page','1');
        $pageSize   = Yii::$app->request->get('pageSize','20');
        $cateId     = Yii::$app->request->get('cateId',0);
        $keyword    = Yii::$app->request->get('keyword','');
        $cateName   = Yii::$app->request->get('cateName','');
        $effectId   = Yii::$app->request->get('effectId',0);

        $param = [
            "page"      => $page,
            "search"    => $keyword,
            "categroyId" => $cateId,
            "effect"    => $effectId,
            "pageSize"  => $pageSize
        ];
        //产品列表
        $productList = SearchProduct::searchProduct($param);
        $productList['list'] = $productList['data'];
        unset($productList['data']);



        $result         = $this->renderPartial('data.htm', [
            'productList' => $productList,
            'GLOBALS' => $this->GLOBALS,
        ]);

        echo json_encode($result);

    }



}
