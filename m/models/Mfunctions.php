<?php
namespace m\models;
use yii;
use yii\base\Model;
use common\functions\Functions;
use common\functions\Tools;
use common\models\Article;
use common\models\ArticleCategory;
use common\models\ProductDetails;
use common\models\ProductCategory;
use common\models\Ask;
use common\models\AskReply;
use common\models\Comment;
use yii\data\Pagination;
use yii\widgets\LinkPager;
use backend\models\CommonFun;
/**
 * 手机站点公共文件
 */
class Mfunctions extends Model
{
    /*
     * [getArticleList 获取推荐文章列表]
     * @param  string  $cate_id  [文章分类id]
     * @param  string  $id       [文章id]
     * @param  string  $orderBy  [排序条件]
     * @param  integer $page     [description]
     * @param  integer $pageSize [description]
     * @return [type]            [description]
     */
    public static function getArticleList($cate_id = '',$id = '',$orderBy = 'created_at Desc',$page = 1,$pageSize=5,$product_id = '',$product_cate_id = '',$need_parent = false){
        $page = !empty($page) ?  $page - 1 : 0;
        $pageSize = !empty($pageSize) ?  $pageSize : 5;
        $res = [];
        $model = Article::find()->where(['status'=>1]);
        if($need_parent) $model->andWhere(['is_recommend'=>1]);
        //产品分类
        if($product_cate_id){
            //找插入该分类产品的文章，若为一级，则找插入该一级下二级分类产品的文章
            $cate = ProductCategory::findOne($product_cate_id);
            //存在该分类
            if (!empty($cate)) {
                //一级分类
                if (empty($cate->parent_id)) {
                    $childCatesArr = ProductCategory::find()->where("parent_id = $cate->id")->asArray()->column();
                    if (!empty($childCatesArr)) {
                        $cateStr = '';
                        foreach ($childCatesArr as $key=>$val) {
                            if ($key == count($childCatesArr) - 1) {
                                $cateStr .= "FIND_IN_SET($val,product_cate_id)" ;
                            } else {
                                $cateStr .= "FIND_IN_SET($val,product_cate_id) OR " ;
                            }
                        }
                        $model->andWhere($cateStr);
                    }
                } else {
                    //二级分类
                    $model->andWhere("FIND_IN_SET($product_cate_id,product_cate_id)");
                }
            }
        }
        
        //文章详情
        if($cate_id){
            $cateid_sql = '';
            $cateid_ids = $cate_id;
            $cate_arr   = Functions::getArticleColumn($cate_id);
            if($cate_arr){
                $cateid_ids.= Functions::getProductCateArr($cate_arr);
            }
            $cateid_sql  = explode(',',$cateid_ids);
            
            $model->andWhere(['in','cate_id',$cateid_sql]);
        }
        if($id){
            $model->andWhere(['!=','id',$id]);
        }
        
        //问答详情
        if($product_id){
            $model->andWhere("FIND_IN_SET($product_id,product_id)");
        }

        // 查询总数
        $totalCount = $model->count();
        //var_dump($totalCount);die;
        $pages = new Pagination(['totalCount' => $totalCount]);
        // 设置每页显示多少条
        $pages->PageSize = $pageSize;
        $pages->Page = $page;

        $res = $model
                ->select('id,title,article_img,created_at,click_num')
                ->offset($pages->offset)
                ->orderBy($orderBy)
                ->limit($pages->limit)
                //->createCommand()->getRawSql();var_dump($res);die;
                ->asArray()->all();

        //文章详情
        if (!empty($cate_id) && $page == 0 && (count($res) < $pageSize)) {
            //若为二级，无产品，推荐一级
            $parenArr = ArticleCategory::find()->select("parent_id")->where("id = $cate_id")->asArray()->one();
            $limit_num = $pageSize-(count($res));
            $cate_id = $parenArr['parent_id'];
            if($cate_id)
            {
                $cateid_sql = '';
                $cateid_ids = $cate_id;
                $cate_arr   = Functions::getArticleColumn($cate_id);
                if($cate_arr){
                    $cateid_ids.= Functions::getProductCateArr($cate_arr);
                }
                $cateid_sql  = explode(',',$cateid_ids);

                $res_par = Article::find()
                    ->select('id,title,article_img,created_at,click_num')
                    ->andWhere(['status'=>1,'is_recommend'=>1])
                    ->andWhere(['in','cate_id',$cateid_sql])
                    ->orderBy($orderBy)
                    ->limit($limit_num)
                    ->asArray()->all();
                $res = array_merge($res,$res_par);
            }
            
        }
        
        //数量控制
        //问答详情
        if (!empty($product_id)) {
            if (empty($res)) {
                return self::getArticleList($cate_id = '',$id = '',$orderBy);
            } elseif (count($res) < $pageSize) {
                $res_ids = join(yii\helpers\ArrayHelper::map($res, 'id', 'id'),',');
                $res2 = Article::find()->select('id,title,article_img,created_at,click_num')->where("id NOT IN ($res_ids)")->orderBy($orderBy)->limit($pageSize-count($res))->asArray()->all();
                $res = array_merge($res,$res2);
            }            
        }
        //产品分类
        if (!empty($product_cate_id)) {
            if (empty($res)) {
                return self::getArticleList($cate_id = '',$id = '',$orderBy);
            } elseif (count($res) < $pageSize) {
                $res_ids = join(yii\helpers\ArrayHelper::map($res, 'id', 'id'),',');
                $res2 = Article::find()->select('id,title,article_img,created_at,click_num')->where("id NOT IN ($res_ids)")->orderBy($orderBy)->limit($pageSize-count($res))->asArray()->all();
                $res = array_merge($res,$res2);
            }
        }

        if($page == 0){
            $stick_res = Functions::getStickArticle('id,title,article_img,created_at,click_num');
            $res  = array_merge($stick_res,$res);
        }
        
        foreach ($res as $key => $value) {
            $res[$key]['article_img']  = Functions::get_image_path($value['article_img'],1);
            $res[$key]['created_at']  = Tools::HtmlDate($value['created_at'],true);
        }

        return  ['list'=>$res,'pages'=>$pages];
        
    }

    /*
     * [getProductList 获取推荐产品列表]
     * @param  string  $cate_id  [产品分类id]
     * @param  string  $id       [产品id]
     * @param  string  $orderBy  [排序条件]
     * @param  integer $page     [description]
     * @param  integer $pageSize [description]
     * @return [type]            [description]
     */
    public static function getProductList($cate_id = '',$id = '',$orderBy_par = 'created_at Desc',$page = 1,$pageSize=6){
        $page =$page - 1;
        $res = [];
        $orderBy = ' has_img desc, ' .$orderBy_par;
        $model = ProductDetails::find()->where(['status'=>1]);
        if($cate_id){
            $model->andWhere(['cate_id'=>$cate_id]);
        }

        if($id){
            $model->andWhere(['!=','id',$id]);
        }

        // 查询总数
        $totalCount = $model->count();
        $pages = new Pagination(['totalCount' => $totalCount]);
        // 设置每页显示多少条
        $pages->PageSize = $pageSize;
        $pages->Page = $page;

        $res = $model
                ->select('id, product_name,price,form,star,product_img')
                ->offset($pages->offset)
                ->orderBy($orderBy)
                ->limit($pages->limit)
                //->createCommand()->getRawSql();
                ->asArray()->all();
        //var_dump($res);die;
        if (empty($res)) {
            //若为二级，无产品，推荐一级        
            $parenArr = ProductCategory::find()->select("parent_id")->where("id = $cate_id")->asArray()->one();
            if(!empty($parenArr['parent_id'])){
                $cate_id = $parenArr['parent_id'];
                self::getProductList($cate_id,$id,$orderBy);
            }
        }
        foreach ($res as $key => $value) {
            $res[$key]['product_img']  = empty($res[$key]['product_img']) ? Functions::get_image_path('default.jpg',1) : Functions::get_image_path($value['product_img'],1);
        }

        return  ['list'=>$res,'pages'=>$pages];
        
    }

    /**
     * [getAskList 获取问答列表]
     * @param  string  $cate_id  [产品分类id]
     * @param  string  $product_id       [产品id]
     * @param  string  $orderBy  [排序条件]
     * @param  integer $page     [description]
     * @param  integer $pageSize [description]
     * @return [type]            [description]
     */
    public static function getAskList($cate_id = '',$product_id = '',$orderBy = 'A.add_time desc',$page = 1,$pageSize=5){
        $page =$page - 1;
        $res = [];
        $model = Ask::find()->Alias('A')->where(['A.status'=>1]);
        
        //产品大全
        if($cate_id){
            $model->andWhere(['P.cate_id'=>$cate_id]);
        }

        //产品详情
        if(empty($cate_id) && $product_id){
//             $product_info = ProductDetails::find()->select("cate_id")->where("id = $product_id")->asArray()->one();
//             $cate_id = $product_info['cate_id'];
            $model->andWhere(['A.product_id'=>$product_id]);
        }

        // 查询总数
        $totalCount = $model
                        ->innerjoin(AskReply::tablename().' AR','A.askid = AR.askid')
                        ->innerjoin(ProductDetails::tablename().' P','P.id = A.product_id')
                        ->groupBy('A.askid')
                        ->count();
                        //var_dump($totalCount);die;
        $pages = new Pagination(['totalCount' => $totalCount]);
        // 设置每页显示多少条
        $pages->PageSize = $pageSize;
        $pages->Page = $page;

        $res = $model
                //->from(Ask::tablename().' A')
                ->select('A.*,P.product_img,count(*) num,AR.reply')
                ->offset($pages->offset)
                ->groupBy('A.askid')
                ->orderBy($orderBy)
                ->limit($pages->limit)
//                 ->createCommand()->getRawSql();
                ->asArray()->all();

        //数量控制
        //产品大全
        if (!empty($cate_id)) {
            if (empty($res)) {
                return self::getAskList('','',$orderBy,'',$pageSize);
            } elseif (count($res) < $pageSize) {
                $res_ids = join(yii\helpers\ArrayHelper::map($res, 'askid', 'askid'),',');
                $res2 = Ask::find()->Alias('A')->select('A.*, P.product_img, count(*) num, AR.reply')->innerjoin(AskReply::tablename().' AR','A.askid = AR.askid')->innerjoin(ProductDetails::tablename().' P','P.id = A.product_id')->groupBy('A.askid')->orderBy($orderBy)->where("A.askid NOT IN ($res_ids) AND A.status = 1")->limit($pageSize-count($res))->asArray()->all();
                $res = array_merge($res,$res2);
            }
        }
        
        foreach ($res as $key => $value) {
            $res[$key]['product_img']  = empty($res[$key]['product_img']) ? Functions::get_image_path('default.jpg',1) : Functions::get_image_path($value['product_img'],1);
            $res[$key]['subject']       = nl2br(Tools::userTextDecode($value['subject']));
            $res[$key]['reply']         = $value['reply'] ? nl2br(Tools::userTextDecode($value['reply'])) : '';
        }

        return  ['list'=>$res,'pages'=>$pages];
        
    }
    /**
     * [getAskInfo 获取问答详情]
     * @param  [type] $askid   [问题id]
     * @param  string $orderBy [排序条件]
     * @return [type]          [description]
     */
    public static function getAskInfo($askid,$orderBy = 'add_time desc'){
        if(!$askid) return ;
        $res = [];
        $ask_info = Ask::find()->alias('A')
                    ->select('A.askid,A.subject,A.product_id,P.product_name,P.product_img,P.cate_id')
                    ->leftjoin(ProductDetails::tablename().' P','P.id = A.product_id')
                    ->where(['askid'=>$askid,'A.status'=>1])->asArray()->one();
        if(!$ask_info) return ;

        $res['ask_id'] = $ask_info['askid'];
        $res['subject'] = nl2br(Tools::userTextDecode($ask_info['subject']));
        $res['product_id'] = $ask_info['product_id'];
        $res['product_name'] = $ask_info['product_name'];
        $res['product_cate_id'] = $ask_info['cate_id'];
        $res['product_img']  = empty($ask_info['product_img']) ? Functions::get_image_path('default.jpg',1) : Functions::get_image_path($ask_info['product_img'],1);

        $reply_info = AskReply::find()
                ->select('username,user_id,reply,add_time')
                ->where(['askid'=>$askid])
                ->orderBy($orderBy)
                //->createCommand()->getRawSql();
                ->asArray()->all();
        
        $res['num'] = count($reply_info);
        
        foreach ($reply_info as $key => $value) {
            $reply_info[$key]['user_img']  = Functions::getUserInfo($value['user_id'])['img'];
            $reply_info[$key]['add_time']  = date('Y-m-d',$value['add_time']);
            $reply_info[$key]['username']  = Tools::userTextDecode($value['username']);
            $reply_info[$key]['reply']     = nl2br(Tools::userTextDecode($value['reply']));

        }
        $res['question_list'] = $reply_info;

        return  ['list'=>$res];
    }
    /**
     * [getProductComment description]
     * @param  [type]  $id  [产品id]
     * @param  integer $num [数量]
     * @return [type]       [description]
     */
    public static function getProductComment($id,$num = 3){
        $comments = Comment::find()
                ->select('id,user_id,user_type,author,comment,created_at')
                ->where(['post_id'=>$id,'type'=>1,'parent_id'=>0,'status'=>1])
                ->orderBy('is_digest DESC,created_at DESC')
                ->limit($num)
                ->asArray()->all();
        if($comments){
           foreach ($comments as $key => $value) {
                $comments[$key]['user_img']  = Functions::getUserInfo($value['user_id'])['img'];
                $comments[$key]['comment']  = nl2br(Tools::userTextDecode($value['comment']));
                $comments[$key]['author']  = Tools::userTextDecode($value['author']);
                $comments[$key]['sub'] = self::getSonComment($value['id'],1); 
            } 
        }
        
        return  $comments;
    }
    /**
     * [getSonComment 获取此评论id的下级评论]
     * @param  [type]  $pid [父级评论id]
     * @param  integer $num [默认条数]
     * @return [type]       [description]
     */
    public static function getSonComment($pid,$num=1){
        $son_comments = Comment::find()
                ->select('id,user_id,user_type,author,comment,created_at')
                ->where(['type'=>1,'parent_id'=>$pid,'status'=>1])
                ->orderBy('star DESC,created_at DESC')
                ->limit($num)
                ->asArray()->all();
        return $son_comments;
    }
    /**
     * [linkPager 翻页接口]
     * @param  [type] $arr [翻页类对象]
     * @return [type]      [description]
     */
    public static function linkPager($arr){
        if(empty($arr)){
            return '';
        }
        $ret = [
            'pagination'        => $arr,
            'maxButtonCount'    => 1,
            'nextPageLabel'     => '', 
            'prevPageLabel'     => '', 
            'firstPageLabel'    => '', 
            'lastPageLabel'     => '',
            'nextPageCssClass'  =>'next',
            'prevPageCssClass'  =>'prev',
            'firstPageCssClass' =>'first',
            'lastPageCssClass'  =>'last',
            'mPageCss'          => true,//注意：自己加的
            'options'           => ['class' => 'pagination'],
        ];
         
        $curPage = $arr->getPage();
        $maxPage            = ceil($arr->totalCount/$arr->getPageSize())-1;
        if($curPage == 0){ 
            $ret['prevPageLabel'] = false;
            $ret['firstPageLabel'] = false;
        }elseif($curPage == $maxPage){
            $ret['nextPageLabel'] = false;
            $ret['lastPageLabel'] = false;
        }

        return LinkPager::widget($ret);
    }

    
}
