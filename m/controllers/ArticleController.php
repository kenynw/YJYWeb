<?php

namespace m\controllers;

use Yii;
use yii\base\Object;
use m\models\WebPage;
use m\models\Mfunctions;
use m\controllers\BaseController;
use common\functions\Huodong;
use common\models\Article;
use common\functions\Functions;
use frontend\controllers\ArticleController as fArticle;

class ArticleController extends BaseController
{ 
    public function actions(){
        $this->GLOBALS['TOP_SHOW'] = 'article';
    }

    public function actionIndex($id)
    {
        $id      = Yii::$app->request->get('id');
        $from    = Yii::$app->request->get('from','H5');
        $from    = strtoupper($from);

        $baseModel  = new WebPage();
        $model      = $baseModel->getArticleDetails($id);

        $p_cate_id = '';
        $p_id = '';
        if(!empty($model['product_id'])){
            $p_arr = explode(',',$model['product_id']);
            $pid = $p_arr['0'];
            //查询产品名
            $sql = "SELECT cate_id FROM {{%product_details}} WHERE id = '$pid'";
            $product_info  = Yii::$app->db->createCommand($sql)->queryOne();
            $p_cate_id = $product_info['cate_id'];
            $p_id = $pid;
        }
        $RecommendArticle = '';
        $ArticleProduct = '';
        if($model){
            $RecommendArticle   = Mfunctions::getArticleList($model['cate_id'],$id,'is_recommend DESC,retime DESC,created_at DESC','','','','',true)['list'];
            $ArticleProduct     = Mfunctions::getProductList($p_cate_id,$id,'is_recommend DESC,recommend_time DESC,created_at DESC')['list'];
        }
        //有登录记录UID
        $uid  = '';
        if (!Yii::$app->user->isGuest) {
            $uid  = Yii::$app->user->identity->id;
        }
        //不是APP使用另一套模板
        $fromArr  = ['IOS','ANDROID'];
        $template = !in_array($from,$fromArr) ? 'index103.htm' : 'index_old.htm';

        //H5的活动统计
        // if(!in_array($from,$fromArr)){
        //     Huodong::huodongCosmetics($id,'2',$uid,$from);
        // }
        
        $this->GLOBALS['statistics'] = ['id' => $id];
        //文章点击数
        Article::articleClick($id);

//         $this->GLOBALS['OfficialAccounts']['share'] = array(
//             "title" => $model['title'],
//             "desc" => "护肤不交智商税，颜究院帮你科学高效护肤",
//             "imgUrl"  => $this->GLOBALS['static_path'].'h5/images/logo.png',
//         );
        $this->GLOBALS['OfficialAccounts']['share']['title'] = $model['title'];
        $this->GLOBALS['OfficialAccounts']['share']['desc']  = "护肤不交智商税，颜究院帮你科学高效护肤";

        $this->GLOBALS['title'] = $model['title'].'-颜究院';
        $this->GLOBALS['description'] = mb_substr(strip_tags($model['content']),0,100,"utf-8");
        $this->GLOBALS['keywords'] = $model['title'].'-颜究院';
        return $this->renderPartial($template,[             
            'model'             =>  $model,
            'RecommendArticle'  =>  $RecommendArticle,
            'RecommendProduct'  =>  $ArticleProduct,
            'GLOBALS'           =>  $this->GLOBALS,
        ]);
    }

    public function actionList(){
        $cate_id    = Yii::$app->request->get('cate_id',0);
        $id         = Yii::$app->request->get('id',0);
        $page       = Yii::$app->request->get('page',1);
        $ArticleCategory    = Functions::getArticleColumn();
        $cate_id = $cate_id ? $cate_id : $ArticleCategory['0']['id'];
        $RecommendArticle   = Mfunctions::getArticleList($cate_id,$id,'created_at DESC',$page,20);
        $RecommendProduct   = Mfunctions::getProductList('','','recommend_time DESC')['list'];
        $RecommendAsk       = Mfunctions::getAskList('','','A.add_time desc','','3')['list'];
        if($cate_id){
            $header = fArticle::getHeaderList($cate_id);
            if (!empty($header)) {
                $this->GLOBALS['title'] = $header['title'];
                $this->GLOBALS['keywords'] = $header['keywords'];
            }
        }else{
            $this->GLOBALS['title'] = '科学护肤_健康护肤-颜究院';
            $this->GLOBALS['description'] = '科学护肤_健康护肤-颜究院';
        }
        $this->GLOBALS['OfficialAccounts']['share']['title'] = '这里有最全的护肤知识';
        $this->GLOBALS['OfficialAccounts']['share']['desc'] = '海量的护肤知识和技巧，让你美容护肤更加得心应手';
        
        return $this->renderPartial('list.htm',[  
            'cate_id'           =>  $cate_id,
            'ArticleCategory'   =>  $ArticleCategory,
            'RecommendArticle'  =>  $RecommendArticle['list'],
            'RecommendProduct'  =>  $RecommendProduct,
            'RecommendAsk'      =>  $RecommendAsk,
            'pages'             =>  Mfunctions::linkPager($RecommendArticle['pages']),
            'GLOBALS'           =>  $this->GLOBALS,
        ]);
    }
}
