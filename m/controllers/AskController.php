<?php

namespace m\controllers;

use Yii;
use yii\base\Object;
use m\models\WebPage;
use m\controllers\BaseController;
use common\models\ProductDetails;
use m\models\Mfunctions;

class AskController extends BaseController
{ 
    public function actions(){
        $this->GLOBALS['TOP_SHOW'] = 'ask';
    }
    
    public function actionIndex($page = '1')
    {        
        //问答主页列表
        $askList = Mfunctions::getAskList($cate_id = '',$product_id = '',$orderBy = 'A.add_time desc',$page = $page,$pageSize=25); 
        Mfunctions::linkPager($askList['pages']);
        $this->GLOBALS['title'] = '护肤品常见问题解析-颜究院';
        
        $this->GLOBALS['OfficialAccounts']['share']['title'] = '这里有最常见的护肤问题解答';
        $this->GLOBALS['OfficialAccounts']['share']['desc']  = '解决你的护肤难题';

        return $this->renderPartial('index.htm',[             
            'askList'    => $askList['list'],
            'pages'      => Mfunctions::linkPager($askList['pages']),
            'GLOBALS'    => $this->GLOBALS,
        ]);
    }
    
    public function actionDetails($id)
    {
        //问答详情
        $askReplyList = Mfunctions::getAskInfo($id,$orderBy = 'add_time desc');
        
        //产品分类
        $cateId = ProductDetails::findOne($askReplyList['list']['product_id'])->cate_id;
        
        //热门产品（与问答产品同个分类下最新设置为推荐的6个产品）
        $RecommendProduct = Mfunctions::getProductList($cate_id = $cateId,$id = '',$orderBy = 'is_recommend desc,recommend_time Desc',$page = 1,$pageSize=6);
        
        //热门文章（匹配与问答产品相关的文章，按时间倒序排序，若无相关文章，则展示最新发布的5篇文章相关文章的匹配规则：文章中有插入该产品的）
        $RecommendArticle = Mfunctions::getArticleList($cate_id = '',$id = '',$orderBy = 'created_at Desc',$page = 1,$pageSize=5,$product_id = $askReplyList['list']['product_id']);
        
        $cate_id = $askReplyList['list']['product_cate_id'];
        
        $this->GLOBALS['title'] = $askReplyList['list']['product_name'].$askReplyList['list']['subject'] . "-颜究院";

        $this->GLOBALS['OfficialAccounts']['share']['title'] = $askReplyList['list']['subject'];
        $this->GLOBALS['OfficialAccounts']['share']['desc']  = '解决你的护肤难题';
        $this->GLOBALS['OfficialAccounts']['share']['imgUrl']  = $askReplyList['list']['product_img'];
        
        return $this->renderPartial('details.htm',[
            'askReplyList'     => $askReplyList['list'],
            'RecommendProduct'      => $RecommendProduct['list'],
            'RecommendArticle'      => $RecommendArticle['list'],
            'GLOBALS'          => $this->GLOBALS,
            'cate_id'          => $cate_id,
        ]);
        
    }

}
