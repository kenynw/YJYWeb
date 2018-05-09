<?php
namespace m\models;
use yii;
use yii\base\Model;
use common\models\User;
use common\functions\Tools;
use common\functions\Efficacy;
use common\models\ArticleCategory;
use common\models\Article;
use common\models\ProductCategory;
use m\controllers\BaseController;
use common\functions\Functions;
/**
 * Signup form
 */
class WebPage extends Model
{
    //产品列表
    public function getProductList($page = '1',$pageSize = '20',$cateId = '',$keyword = '',$recommend = '',$orderBy = 'id',$sort = 'desc'){

        $pageMin = ($page - 1) * $pageSize;
        $whereStr = "status = 1";
        $whereStr .= $cateId ? " AND cate_id='$cateId'" : "";
        $whereStr .= $recommend != "" ? " AND is_recommend='$recommend'" : "";

        if($keyword){
            $keyword = str_replace("sk2","sk-II",$keyword);

            $this::addKeyword($keyword);
            $whereStr .= " AND product_name like '%$keyword%'";
        }

        $count = "SELECT count(id)  FROM {{%product_details}} WHERE $whereStr";
        $num    = Yii::$app->db->createCommand($count)->queryScalar();

        $pageCount = ceil($num/$pageSize);
        $sql = "SELECT * FROM {{%product_details}} WHERE $whereStr ORDER BY has_img desc,(case when price is null or price='' then 1 else 0 end) ,star DESC,page DESC LIMIT $pageMin,$pageSize";
        $list  = Yii::$app->db->createCommand($sql)->queryAll();

        $data = ['list' => $list , 'pageTotal' => $num , 'page'=> $page , 'pageSize'=> $pageSize, 'pageCount'=>$pageCount];
        return  $data;
    }

    /**
     * [getRecommendProduct description]
     * @param  integer $num [description]
     * @return [type]       [description]
     */
    PUBLIC FUNCTION getRecommendProduct($num = 10){
        //参数
        $num        = intval($num);
        //条件
        $whereStr   = "P.status = '1' AND is_recommend = '1'";
        $orderBy    = "P.has_img AND P.price DESC ,P.is_recommend DESC,P.recommend_time DESC,P.is_top DESC,P.has_img DESC,P.comment_num DESC,P.star DESC";

        $productSql = " SELECT P.id, P.product_name,P.price,P.form,P.star,P.product_img
                        FROM {{%product_details}} P 
                        WHERE $whereStr
                        ORDER BY $orderBy
                        LIMIT $num";
        $rows       =  Yii::$app->db->createCommand($productSql)->queryAll();

        if($rows){
            foreach ($rows as $key => $value) {
                $rows[$key]['product_img']  = $rows[$key]['product_img'] ? $rows[$key]['product_img'] : 'default.jpg';
                $rows[$key]['product_img']  = Functions::get_image_path($value['product_img'],1);
            }
        }
        return  $rows;
    }

    //文章列表
    public function getArticleList($page = '1',$pageSize = '20',$cateId = '',$recommend = '',$orderBy = 'id',$sort = 'desc'){
        $pageMin    = ($page - 1) * $pageSize;
        $whereStr = "status = 1";
        $whereStr .= $cateId ? " AND cate_id='$cateId'" : "";
        $whereStr .= $recommend != "" ? " AND is_recommend='$recommend'" : "";
        
        $count = "SELECT count(id)  FROM {{%article}} WHERE $whereStr ORDER BY $orderBy $sort";
        $num    = Yii::$app->db->createCommand($count)->queryScalar();
        $pageCount = ceil($num/$pageSize);

        //有无传cateId分类列表
        if (!empty($cateId)) {
            $model = ArticleCategory::findOne($cateId);
            //二级分类列表
            if ($model->parent_id == 0) {
                //一级分类列表
                $cateSql = "SELECT id FROM {{%article_category}} WHERE status = 1 AND parent_id = $cateId";
                if (!empty(Yii::$app->db->createCommand($cateSql)->queryColumn())) {
                    $cate_ids = join(',', Yii::$app->db->createCommand($cateSql)->queryColumn());
                    $sql = "SELECT id,title,article_img,created_at FROM {{%article}} WHERE status = 1 AND cate_id in ($cate_ids) ORDER BY $orderBy $sort limit $pageMin,$pageSize";
                    
                    $count = "SELECT count(id)  FROM {{%article}} WHERE status = 1 AND cate_id in ($cate_ids)";
                    $num    = Yii::$app->db->createCommand($count)->queryScalar();
                    $pageCount = ceil($num/$pageSize);
                    
                    $list  = Yii::$app->db->createCommand($sql)->queryAll();
                } else {
                    //无二级分类
                    $sql = "SELECT id,title,article_img,created_at FROM {{%article}} WHERE status = 1 AND cate_id = $cateId ORDER BY $orderBy $sort limit $pageMin,$pageSize";
                    
                    $count = "SELECT count(id)  FROM {{%article}} WHERE status = 1 AND cate_id = $cateId ORDER BY $orderBy $sort";
                    $num    = Yii::$app->db->createCommand($count)->queryScalar();
                    $pageCount = ceil($num/$pageSize);
                    
                    $list  = Yii::$app->db->createCommand($sql)->queryAll();                   
                }
            } else {
                $sql = "SELECT id,title,article_img,created_at FROM {{%article}} WHERE $whereStr ORDER BY $orderBy $sort limit $pageMin,$pageSize";                                
                $list  = Yii::$app->db->createCommand($sql)->queryAll();
            }
        } else {
            $sql = "SELECT id,title,article_img,created_at FROM {{%article}} WHERE $whereStr ORDER BY $orderBy $sort limit $pageMin,$pageSize";
            $list  = Yii::$app->db->createCommand($sql)->queryAll();
        }

        $data = ['list' => $list , 'pageTotal' => $num , 'page'=> $page , 'pageSize'=> $pageSize,'pageCount'=>$pageCount];
        return  $data;
    }

    //推荐文章列表
    public function getRecommendArticle($product_id = '',$orderBy = 'id',$sort = 'desc',$type = 'product',$cate_id = '',$id = ''){

    if ($type == 'product') {
        $sql = "SELECT id,title,article_img,created_at,cate_id FROM {{%article}} WHERE status = 1 AND FIND_IN_SET('$product_id',product_id) ORDER BY $orderBy $sort,id DESC LIMIT 5";
        $list1  = Yii::$app->db->createCommand($sql)->queryAll();

        if(!empty($list1)){
            $list = $list1;

            if(count($list1) < 5){
                $limit = 5 - count($list1);

                $cate_list = array();
                foreach($list1 as $val){
                    $cate_list[] = $val['cate_id'];
                }

                if($cate_list){
                    $cate_list = array_unique($cate_list);
                    $cate_str = join(",",$cate_list);

                    $sql = "SELECT id,title,article_img,created_at FROM {{%article}} WHERE status = 1 AND cate_id in ($cate_str) ORDER BY $orderBy $sort,id DESC LIMIT $limit";
                    $list2  = Yii::$app->db->createCommand($sql)->queryAll();

                    if($list2){
                        $list = array_merge($list1,$list2);
                    }
                }
            }
        }else{
            $sql = "SELECT id,title,article_img,created_at FROM {{%article}} WHERE status = 1 ORDER BY $orderBy $sort,id DESC LIMIT 5";
            $list  = Yii::$app->db->createCommand($sql)->queryAll();
        }

    } else {
        //是二级推荐二级，一级推荐一级
        $sql = "SELECT id,title,article_img,created_at FROM {{%article}} WHERE status = 1 AND cate_id = $cate_id AND id !=$id ORDER BY $orderBy $sort,id DESC LIMIT 5";
        $list  = Yii::$app->db->createCommand($sql)->queryAll();
        
        //若为二级，无产品，推荐一级        
//         $parenArr = ArticleCategory::find()->select("parent_id")->where("id = $cate_id")->asArray()->one();
//         if (empty($list) && !empty($parenArr['parent_id'])) {var_dump(1);
//             $cate_id = $parenArr['parent_id'];
//             $sql = "SELECT id,title,article_img,created_at FROM {{%article}} WHERE status = 1 AND cate_id = $cate_id AND id !=$id ORDER BY $orderBy $sort,id DESC LIMIT 5";
//             $list  = Yii::$app->db->createCommand($sql)->queryAll();
//         }
    }
        return  $list;
    }

    //产品分类列表
    public function getProductCateList(){
        $sql = "SELECT * FROM {{%product_category}} WHERE status = 1 ORDER BY sort DESC LIMIT 8";
        $list  = Yii::$app->db->createCommand($sql)->queryAll();
        return  $list;
    }

    //文章分类列表
    public function getArticleCateList(){
        $sql = "SELECT * FROM {{%article_category}} WHERE status = 1 AND parent_id = 0 ORDER BY id DESC";
        $list  = Yii::$app->db->createCommand($sql)->queryAll();
        foreach ($list as $key=>$val) {
            $id = $val['id'];
            $kid = "SELECT * FROM {{%article_category}} WHERE status = 1 AND parent_id = $id ORDER BY id DESC";
            $kidlist = Yii::$app->db->createCommand($kid)->queryAll();
            $list[$key]['kid'] = $kidlist;           
        }
        return  $list;
    }

    //关键词列表
    public function getHotKeyword($num){
        $sql = "SELECT keyword FROM {{%hot_keyword}} ORDER BY num DESC LIMIT $num";
        $list  = Yii::$app->db->createCommand($sql)->queryColumn();
        return  $list;
    }

    //产品成分详情
    public function getComponentList($id = '0'){
        $list = array();
        if($id){
            $sql = "SELECT pc.* FROM {{%product_relate}} pr LEFT JOIN {{%product_component}} pc on pr.component_id=pc.id  WHERE product_id = '$id' ORDER BY pr.id ASC";
            $list  = Yii::$app->db->createCommand($sql)->queryAll();
            if (BaseController::getEquipment() != 'PC') {
                //取第一个成分
                foreach ($list as $key=>$val) {
                    $action = explode('，',$list[$key]['component_action']);
                    $component_action = $action['0'];
                    $list[$key]['component_action'] = $component_action;
                }
            }
        }

        return  $list;
    }

    //产品详情
    public function getProductDetails($id = '0'){

        $product_list = array();
        if($id){
            //查询关联的成分id
            $sql = "SELECT pc.* FROM {{%product_relate}} pr LEFT JOIN {{%product_component}} pc on pr.component_id=pc.id  WHERE product_id = '$id'";
            $component_list  = Yii::$app->db->createCommand($sql)->queryAll();

            //查询分类名
            $sql = "SELECT pc.cate_name,pd.*,b.img as brand_img,b.name as brand_name,b.link_tb,b.link_jd FROM {{%product_details}} pd 
                    LEFT JOIN {{%product_category}} pc ON pd.cate_id=pc.id 
                    LEFT JOIN {{%brand}} b ON pd.brand_id=b.id 
                    WHERE pd.id = '$id'";
            $product_list  = Yii::$app->db->createCommand($sql)->queryOne();
        }

        //获取功效列表
        $efficacyList = Efficacy::getEfficacyList($component_list,$product_list['cate_name']);

        //三个平台下链接
        $buy    = [
            'taobao' => 'https://ai.taobao.com/search/index.htm?key='.$product_list['product_name'].'&pid=mm_124287267_25890794_99532920',
            'jd'     => 'http://api.yjyapp.com/oauth/get-jd-url?u=https://so.m.jd.com/ware/search.action?keyword='.$product_list['product_name'],
            'amazon' => 'https://www.amazon.cn/gp/search?ie=UTF8&camp=536&creative=3200&index=aps&keywords='.$product_list['product_name'].'&linkCode=ur2&tag=865230-23',
        ];

        $extend_sql     = "SELECT type,url,link_price,tb_goods_id FROM {{%product_link}} WHERE product_id = $id";
        $extend_info    = Yii::$app->db->createCommand($extend_sql)->queryAll();
        $link_buy = [];
        if($extend_info){
            foreach ($extend_info as $key => $value) {
                if($value['type'] == 2){
                    $LinkPrice = Functions::getLinkPrice($value['tb_goods_id'],$value['type']);
                    $value['link_price'] = isset($LinkPrice[$value['tb_goods_id']]) ? $LinkPrice[$value['tb_goods_id']] : '';
                    unset($value['tb_goods_id']);
                    if($value['link_price'] == 0){
                        switch ($value['type']) {
                            case '1':
                                $link_type = 'link_tb';
                                break;
                            case '2':
                                $link_type = 'link_jd';
                                break;
                        }
                        if(!empty($product_list[$link_type])){
                            $value['url'] = $product_list[$link_type];
                            $link_buy[] = $value;
                            unset($rows[$link_type]);
                        } 
                    }else{
                        $link_buy[] = $value;
                    }
                }
            }
        }else{
            $res_arr['link_price'] = '';
            if(!empty($product_list['link_jd'])){
                $res_arr['type'] = 2;
                $res_arr['url'] = $product_list['link_jd'];
                $link_buy[] = $res_arr;
            }
            
        }


        $data = array(
            'list'          => $product_list,
            'function_list' => $efficacyList['function_list'],
            'safe_list'     => $efficacyList['safe_list'],
            'buy'           => $buy,
            'link_buy'      => $link_buy,
        );

        return  $data;
    }

    //文章详情
    public function getArticleDetails($id = '0'){

        $list = array();
        if($id){           
            $sql = "SELECT * FROM {{%article}} WHERE id = '$id'";
            $list  = Yii::$app->db->createCommand($sql)->queryOne();
            if(empty($list)) return ;
            
            //获取分类信息
            $cate_name = ArticleCategory::findOne($list['cate_id']);
            $parent_cate_info = Article::getParent($list['cate_id']);
            
            $list['cate_name'] = $cate_name->cate_name;
            $list['parent_cate_name'] = $parent_cate_info['cate_name'];
            $list['parent_cate_id'] = $parent_cate_info['id'];

            //处理表情
            $list['content'] = Tools::userTextDecode($list['content']);
        }

        return  $list;
    }

    //添加关键词
    static function addKeyword($keyword = ""){

        //查询是否存在
        $sql    = "SELECT id FROM {{%hot_keyword}} WHERE keyword = '$keyword'";
        $check    = Yii::$app->db->createCommand($sql)->queryScalar();

        if(!empty($check)){
            $sql  = "UPDATE {{%hot_keyword}} SET num = num + 1 WHERE keyword = '$keyword'";
        }else{
            $sql    = "INSERT INTO {{%hot_keyword}} (`keyword`,`num`) VALUES ('$keyword','1')";
        }
        $return = Yii::$app->db->createCommand($sql)->execute();
    }

    //获取文章关联商品
    public function getArticleProduct($id_str = '',$limit = 6){

        if(empty($id_str)){
            $sql = "SELECT * FROM {{%product_details}} WHERE status = 1 AND is_recommend = 1 ORDER BY has_img AND price DESC ,is_recommend DESC,recommend_time DESC,is_top DESC,has_img DESC,comment_num DESC,star DESC limit $limit";
        }else{
            $ids = explode(',',$id_str);
            $sql = "SELECT P1.* FROM {{%product_details}} AS P
                    LEFT JOIN {{%product_details}} AS P1 ON P1.cate_id = P.cate_id AND P1.status = 1 
                    WHERE P.id = '$ids[0]' ORDER BY P1.has_img AND P1.price DESC,P.is_recommend DESC,P1.is_top DESC,P1.comment_num DESC,P1.star DESC limit $limit";
        }     

        $list  = Yii::$app->db->createCommand($sql)->queryAll();
        if($list){
            foreach ($list as $key => $value) {
                $list[$key]['product_img'] = Functions::get_image_path($value['product_img'],1);
            }
        }
        
        return  $list;
    }
}
