<?php

namespace common\functions;

use Yii;
use common\functions\Functions;
use common\functions\Skin;
use yii\sphinx\Query;
use yii\sphinx\MatchExpression;

class SearchProduct{
    /**
     * [searchProduct 搜索产品]
     * @return [type] [description]
     */ 
    PUBLIC STATIC function searchProduct($param){

        $search         =   Functions::checkStr($param['search']);
        $pc_brandId     =   isset($param['brandId'])    ? intval($param['brandId'])     : 0 ;
        $categroyId     =   isset($param['categroyId']) ? intval($param['categroyId'])  : 0 ;
        $star           =   isset($param['star'])       ? intval($param['star'])        : 0 ;
        $effect         =   isset($param['effect'])     ? Functions::checkStr($param['effect']) : '';
        $pageSize       =   isset($param['pageSize'])   ? intval($param['pageSize']) : 20 ;
        $page           =   isset($param['page'])       ? intval($param['page'])  : 1 ;
        $recommend      =   isset($param['recommend'])  ? intval($param['recommend'])  : 0 ; 
        $idtype         =   isset($param['idtype'])     ? intval($param['idtype'])     : 1;

        //先搜索字符
        $idStr          =   '';
        $brandId        =   '';
        $idArr          =   [];
        $componentArr   =   (object)[];
        $brandArr       =   (object)[];
        $query          =   new Query();
        $searchBrandId  = '';
        $orderBrandBy   = '';
        $brandIdArr     = '';

        if($search){
            //先匹配品牌
            $brandSql   = "SELECT id,name,img,hot FROM {{%brand}} WHERE status = '1' AND (name = '$search' || ename = '$search' || alias = '$search')";
            $brandArr   = Yii::$app->db->createCommand($brandSql)->queryOne(); 

            if($brandArr){
                $searchBrandId    = $brandArr['id'];
                $brandArr['img']  = Functions::get_image_path($brandArr['img'],1);
            }else{
                $brandArr   =  (object)[];
            }
            //匹配成份
            $componentSql   = "SELECT C.id,C.name,COUNT(*) AS num FROM {{%product_component}} C  
                            LEFT JOIN {{%product_relate}} R ON C.id = R.component_id
                            WHERE C.name = '$search'";
            $componentArr   = Yii::$app->db->createCommand($componentSql)->queryOne();
            $componentArr   = $componentArr['id']  ? $componentArr  : (object)[];
            //查询产品
            $rows       =   $query->select('id')->from('product')->match($search)->limit(1000)->all();
            //如果有子品牌还加子品牌--各种蛋疼
            if($searchBrandId){
                $cate_arr           = Functions::getBrandColumn($searchBrandId);
                if($cate_arr){
                    $brandIdArr     = Functions::getProductCateArr($cate_arr); 
                }
                $productBrandSql= Functions::db_create_in($searchBrandId.','. $brandIdArr,'brand_id');
                $pidSql         = "SELECT id FROM {{%product_details}} WHERE $productBrandSql";
                $pRows          = Yii::$app->db->createCommand($pidSql)->queryAll();
                foreach ($pRows as $key => $value) {
                    $idArr[] = $value['id'];
                } 
                $orderBrandBy = $productBrandSql.' DESC,';
            }
            // if(!$recommend){
            //     if(!$rows && !$brandId) return [ 'status' => 0,'data' => [] ,'pageTotal' => 0, 'page'=>$page ];
            foreach ($rows as $key => $value) {
                $idArr[] = $value['id'];
            }
            $idArr = array_unique($idArr);
            $idStr = Functions::db_create_in($idArr,'P.id');
            // $idStr = $brandId && $idStr ?  "(P.brand_id = '$brandId'  OR " . $idStr.")" : $idStr ; 
            // }  
        }
        //分类级别支持搜索下级
        $cateid_sql = '';
        if($categroyId){
            $cateid_ids = $categroyId;
            $cate_arr   = Functions::getProductColumn($categroyId);
            if($cate_arr){
                $cateid_ids.= Functions::getProductCateArr($cate_arr);
            }
            $cateid_sql  = Functions::db_create_in(explode(',',$cateid_ids),'P.cate_id');
        }
       
        //再搜索条件
        $whereStr   = 'P.status = 1';
        $whereStr  .= $idStr ? ' AND '.$idStr : '';
        $whereStr  .= $pc_brandId   ? " AND P.brand_id = '$pc_brandId' " : '';
        $whereStr  .= $star         ? " AND P.star = '$star' " : '';
        $whereStr  .= $cateid_sql   ? " AND ".$cateid_sql." " : '';
        $whereStr  .= $effect       ? ' AND FIND_IN_SET(\''.$effect.'\',P.effect_id) ' : '';
        $whereStr  .= $recommend    ? " AND is_recommend='$recommend'" : '';
        

        $orderBy   = $orderBrandBy ? $orderBrandBy.'P.is_top DESC,P.comment_num DESC,P.is_recommend DESC,P.is_complete DESC,P.has_img DESC,P.has_price DESC,P.has_brand DESC,P.created_at DESC,P.id DESC' : 'P.comment_num DESC,P.is_recommend DESC,P.is_complete DESC,P.has_img DESC,P.has_price DESC,P.has_brand DESC,P.created_at DESC,P.id DESC';

        /* 分页 */
        $sql        = "SELECT COUNT(*) FROM {{%product_details}} P WHERE $whereStr";

        $num        = Yii::$app->db->createCommand($sql)->queryScalar();

        //分页判断
        $maxPage = ceil($num/$pageSize) + 1;
        $page > $maxPage  ? $page = $maxPage  : '';
        $offset  = ($page - 1) * $pageSize;

        $productSql = "SELECT P.id, P.product_name,P.price,P.brand_id,P.form,P.star,P.product_img,P.cate_id,P.is_top,P.comment_num
                       FROM {{%product_details}} P 
                       WHERE $whereStr
                       ORDER BY $orderBy
                       LIMIT $offset,$pageSize";
        $rows       = Yii::$app->db->createCommand($productSql)->queryAll();

        if($rows){
            foreach ($rows as $key => $value) {
                $rows[$key]['product_img']  = Functions::get_image_path($value['product_img'],1);
                //标签处理
                $tagList                   = Functions::getProductTags($value['id'],$idtype);
                if($value['is_top'] && $value['brand_id']) array_unshift($tagList, ['itemid' => '1','tagname' => '品牌明星产品']);
                $rows[$key]['tags'] = $tagList;
                unset($tagList);
            }
        }
        return [ 'status' => 1,'data' => $rows ,'brand' => $brandArr,'component' => $componentArr,'pageTotal' => $num, 'page'=>$page ];
    }
    /**
     * [associative 联想搜索]
     * @param  [type]  $search [内容]
     * @param  integer $num    [数量]
     * @return [type]          [description]
     */
    PUBLIC STATIC function associative($search,$num = 5){
        $search     =   Functions::checkStr($search);
        $num        =   intval($num);
        //先搜索品牌
        $query      =   new Query();
        $productArr =  [];
        $idArr      =  [];
        //再搜索条件
    
        $brandSql   = "SELECT id,name,img
                    FROM {{%brand}}
                    WHERE status = 1 AND (name = '$search' || ename = '$search' || alias = '$search')
                    ORDER BY is_recommend DESC,hot DESC
                    LIMIT 1";

        $db_brandArr    = Yii::$app->db->createCommand($brandSql)->queryOne(); 
        $brandArr       = $db_brandArr ? $db_brandArr : (object)[];
        $searchBrandId  = !empty($db_brandArr) ? $db_brandArr['id'] : '';
        //如果有子品牌还加子品牌--各种蛋疼
        if($searchBrandId){
            $pidSql         = "SELECT id FROM {{%product_details}} WHERE brand_id = '$searchBrandId'";
            $pRows          = Yii::$app->db->createCommand($pidSql)->queryAll();
            foreach ($pRows as $key => $value) {
                $idArr[] = $value['id'];
            } 
        }
        //搜索产品
        $rows       =   $query->select('id')->from('product')->match($search)->all();
        if($rows){
            foreach ($rows as $key => $value) {
                    $idArr[] = $value['id'];
            }
        }
        if(!empty($idArr)){
            $idArr      = array_unique($idArr);
            $idStr      = Functions::db_create_in($idArr,'P.id');
            $whereStr   = 'P.status = 1';
            $whereStr  .= $idStr ? ' AND '.$idStr : '';
            $orderBy    = "P.comment_num DESC,P.is_recommend DESC,P.is_complete DESC,P.has_img DESC,P.has_price DESC,P.has_brand DESC,P.created_at DESC,P.id DESC";
            $productSql = "SELECT id, product_name 
                           FROM {{%product_details}} P
                           WHERE $whereStr
                           ORDER BY $orderBy
                           LIMIT $num";
            $productArr = Yii::$app->db->createCommand($productSql)->queryAll();
            $productArr = $productArr ? $productArr : [];
        }

        return ['status' => 1 ,'msg' => ['brand'=>$brandArr,'product'=>$productArr]];
    }
    /**
     * [associative 搜索品牌]
     * @param  [type]  $search [内容]
     * @param  [type]  $pageSize [每页数]
     * @param  [type]  $page [page]
     * @return [type]  [description]
     */
    PUBLIC STATIC function searchBrand($param){
        $search     =   Functions::checkStr($param['search']);
        $pageSize   =   isset($param['pageSize'])   ? intval($param['pageSize']) : 20 ;
        $page       =   isset($param['page'])       ? intval($param['page'])  : 1 ;
        //先搜索品牌
        $query      =   new Query();
        $brandRows  =   $query->select('id')->from('brand')->match($search)->limit(1000)->all();

        if(!$brandRows) return ['status' => 0 ,'data' => []];
        foreach ($brandRows as $key => $value) {
            $idArr[] = $value['id'];
        }
        $idStr      = Functions::db_create_in($idArr,'id');
        //查询条件
        $whereStr   = 'status = 1';
        $whereStr  .= $idStr ? ' AND '.$idStr : '';
        /* 分页 */
        $sql        = "SELECT COUNT(*) FROM {{%brand}}  WHERE $idStr";

        $num        = Yii::$app->db->createCommand($sql)->queryScalar();

        //分页判断
        $maxPage    = ceil($num/$pageSize) + 1;
        $page > $maxPage  ? $page = $maxPage  : '';
        $offset     = ($page - 1) * $pageSize;
        $idStr      = Functions::db_create_in($idArr,'id');

        $brandSql   = "SELECT *
                       FROM {{%brand}}
                       WHERE $whereStr
                       ORDER BY is_recommend DESC,hot DESC
                       LIMIT $offset,$pageSize";

        $brandArr   = Yii::$app->db->createCommand($brandSql)->queryAll();
        if($brandArr){
            foreach ($brandArr as $key => $value) {
                $brandArr[$key]['img']  = Functions::get_image_path($value['img'],1);
            }
        }
        return ['status' => 1 ,'data' => $brandArr,'page'=>$page,'pageTotal'=>$num];
    }
    /**
     * [associative 搜索文章]
     * @param  [type]  $search [内容]
     * @param  [type]  $pageSize [每页数]
     * @param  [type]  $page [page]
     * @return [type]  [description]
     */
    PUBLIC STATIC function searchArticle($param){
        $search     =   Functions::checkStr($param['search']);
        $pageSize   =   isset($param['pageSize'])   ? intval($param['pageSize']) : 20 ;
        $page       =   isset($param['page'])       ? intval($param['page'])  : 1 ;
        //先搜索字符
        $query          = new Query();
        $articleRows    = $query->select('id')->from('article')->match($search)->limit(1000)->all();

        if(!$articleRows) return ['status' => 0 ,'data' => [],'page'=>$page,'pageTotal'=>0];
        foreach ($articleRows as $key => $value) {
            $idArr[] = $value['id'];
        }
        $idStr      = Functions::db_create_in($idArr,'id');
        //查询条件
        $whereStr   = 'status = 1';
        $whereStr  .= $idStr ? ' AND '.$idStr : '';
        /* 分页 */
        $sql        = "SELECT COUNT(*) FROM {{%article}}  WHERE $idStr";

        $num        = Yii::$app->db->createCommand($sql)->queryScalar();
        //分页判断
        $maxPage    = ceil($num/$pageSize) + 1;
        $page > $maxPage  ? $page = $maxPage  : '';
        $offset     = ($page - 1) * $pageSize;

        $articleSql = "SELECT id,title,article_img,content,created_at
                       FROM {{%article}}
                       WHERE $whereStr
                       ORDER BY is_recommend DESC,created_at DESC
                       LIMIT $offset,$pageSize";

        $articleArr  = Yii::$app->db->createCommand($articleSql)->queryAll();
        if($articleArr){
            foreach ($articleArr as $key => $value) {
                $articleArr[$key]['article_img']  = Functions::get_image_path($value['article_img'],1);
                $articleArr[$key]['created_at']   = Tools::HtmlDate($value['created_at']);
            }
        }
        return ['status' => 1 ,'data' => $articleArr,'page'=>$page,'pageTotal'=>$num];
    }
}