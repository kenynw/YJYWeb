<?php

namespace frontend\controllers;

use Yii;
use frontend\controllers\BaseController;
use frontend\models\WebPage;
use common\functions\Tools;
use common\models\Comment;
use common\models\User;
use common\models\ProductDetails;
use common\functions\SearchProduct;
use common\functions\Efficacy;
use common\functions\Functions;
use common\components\OssUpload;
use common\models\Attachment;
/**
 * Site controller
 */
class ProductController extends BaseController
{
    public function actionIndex($page = '1',$keyword = '',$brandId = '',$cateId = '',$effectId = '',$star = '')
    {
        $param = [
            "page"      => $page,
            "search"    => $keyword,
            "brandId"   => $brandId,
            "categroyId" => $cateId,
            "star"      => $star,
            "effect"    => $effectId,
            "pageSize"  => 24
        ];
        //产品列表
        $productList = SearchProduct::searchProduct($param);

        if($productList){
            $productList = self::getProductList($productList);
        }

        //判断是否显示品牌列表
        $check = "";
        if($keyword){
            $count = "SELECT count(id)  FROM {{%brand}} WHERE name = '$keyword'";
            $check    = Yii::$app->db->createCommand($count)->queryScalar();
        }

        $webPage   = new WebPage();

        $recommendProduct = array();
        if(empty($productList['list'])){
            //推荐产品列表(无搜索结果)
            $recommendProduct = $webPage->getProductList($page = '1',$pageSize = '8','','','',$recommend = '1',$top='',$orderBy = 'recommend_time desc');
        }

        //品牌列表
        $sql = "SELECT id,name,ename FROM {{%brand}} ORDER BY hot DESC limit 30";
        $brandList = Yii::$app->db->createCommand($sql)->queryAll();

        //产品分类列表
        $productCate = $webPage->getProductCateList();

        //功效列表
        $sql = "SELECT * FROM {{%product_effect}}";
        $effectList = Yii::$app->db->createCommand($sql)->queryAll();

        //推荐品牌
        $recommendBrand = $webPage->getRecommendBrand(6);

        //推荐文章
        $hotArticle = $webPage->getHotArticle($page = '1',$pageSize = "5",'','1');

        //广告列表
        $advertisementList = [
            "main1" => $webPage->getAdList($type = "product/index",$position = "main",$sort = "1"),
            "left1" => $webPage->getAdList($type = "product/index",$position = "left",$sort = "1"),
        ];

        //头部信息修改
        if($keyword) {
            if (!empty($productList['list'])) {
                $this->GLOBALS['title'] = $keyword . '相关的护肤品-颜究院';
            } else {
                $this->GLOBALS['title'] = '找不到搜索词相关的护肤品-颜究院';
            }

        }else if($cateId || $brandId || $effectId){

            $data = '';
            if($brandId){
                foreach($brandList as $val){
                    if($brandId == $val['id']){
                        $data .= $val['name'] ? $val['name'] : $val['ename'];
                        break;
                    }
                }
            }

            if($effectId){
                foreach($effectList as $val){
                    if($effectId == $val['effect_id']){
                        $data .= $val['effect_name'];
                        break;
                    }
                }
            }

            if($cateId){
                foreach($productCate as $val){
                    if($cateId == $val['id']){
                        $data .= $val['cate_name'];
                        break;
                    }
                }
            }

            $this->GLOBALS['title'] = $data . '怎么样_' . $data . '产品成分分析-颜究院';
            $this->GLOBALS['description'] = '提供' . $data . '怎么样、' . $data . '产品成分分析。了解更多可关注颜究院官方微博，官方微信和APP。';
            $this->GLOBALS['keywords'] = $data . '，' . $data . '怎么样，' . $data . '产品成分分析';

        }else{
            $this->GLOBALS['title'] = '护肤品成分查询_护肤品成分分析-颜究院';
            $this->GLOBALS['description'] = '颜究院提供专业的护肤品成分查询、护肤品成分分析。包括化妆水、精华、乳霜、眼霜、面膜等护肤品的成分查询，了解更多可关注颜究院官方微博，官方微信和APP。';
            $this->GLOBALS['keywords'] = '护肤品成分，护肤品成分查询，护肤品成分分析';
        }

        return $this->renderPartial('index.htm', [
            'param'         => $param,
            'productList'   => $productList,
            'recommendProduct' => $recommendProduct,
            'brandList'     => $brandList,
            'productCate'   => $productCate,
            'effectList'    => $effectList,
            'recommendBrand' => $recommendBrand,
            'hotArticle'    => $hotArticle,
            'advertisementList' => $advertisementList,
            'check'         => $check,
            'GLOBALS'       => $this->GLOBALS,
        ]);

    }

    //获取产品信息
    static function getProductList($data){

        $product_list   = $data['data'];
        $num            = $data['pageTotal'];
        $pageSize       = "24";
        $page           = $data['page'];

        $idArr          = [];
        $tagArr         = [];
        $cateArr        = [];
        $cidArr         = [];
        $newIdArr       = [];
        $newComponent   = [];
        //特征标签
        foreach($product_list as $key => $val){
            $idArr[]  = $val['id'];
            $cidArr[] = $val['cate_id'];
        }

        $idStr  = Functions::db_create_in($idArr,'itemid');

        $sql    = " SELECT cti.itemid,ct.tagname 
                    FROM {{%common_tagitem}} cti 
                    LEFT JOIN {{%common_tag}} ct ON cti.tagid = ct.tagid  
                    WHERE cti.idtype = 1 AND $idStr";

        $taglist= Yii::$app->db->createCommand($sql)->queryAll();

        foreach ($taglist as $k => $v) {
            $tagArr[$v['itemid']][] = $v['tagname'];
        }
        //去重
        foreach ($idArr as $idkey => $idVal) {
            if(!array_key_exists($idVal,$tagArr)){
                $newIdArr[] = $idVal;
            }
        }
        $newIdStr  = Functions::db_create_in($newIdArr,'product_id');
        //查询关联的成分id
        $newSql = "SELECT pc.id,pr.product_id,pc.name,pc.id,pc.component_action,pc.risk_grade FROM {{%product_relate}} pr 
                LEFT JOIN {{%product_component}} pc ON pr.component_id = pc.id  
                WHERE $newIdStr";
        $componentList  = Yii::$app->db->createCommand($newSql)->queryAll();

        foreach ($componentList as $compKey => $compVal) {
            $newComponent[$compVal['product_id']][] = $compVal;
        }
        $idCatStr  = Functions::db_create_in($cidArr,'id');
        $cateSql   = "SELECT id,cate_name FROM {{%product_category}}  WHERE $idCatStr";
        $cateList  = Yii::$app->db->createCommand($cateSql)->queryAll();

        foreach ($cateList as $cateKey => $cateVal) {
            $cateArr[$cateVal['id']] = $cateVal['cate_name'];
        }

        //没有特征标签用成份标签
        foreach($product_list as $prodKey => $prodVal){
            $product_list[$prodKey]['taglist'] = [];

            if(isset($tagArr[$prodVal['id']])) {
                $product_list[$prodKey]['taglist'] = $tagArr[$prodVal['id']];
            }elseif(isset($newComponent[$prodVal['id']])){
                //查询关联的成分id
                $componentArr = $newComponent[$prodVal['id']];
                $cate_name    = isset($cateArr[$prodVal['cate_id']]) ? $cateArr[$prodVal['cate_id']] : '';
                //获取功效列表
                $efficacyList = Efficacy::getEfficacyList($componentArr,$cate_name);
                //功效特征
                $list = [];
                if($efficacyList['function_list']){
                    foreach($efficacyList['function_list'] as $k=>$v){
                        if(count($v) > 0){
                            $list[$k] = count($v);
                        }
                    }
                }
                //(成分数最多的3个功效名)
                arsort($list);
                $list = array_keys($list);
                $list = array_slice($list,0,3);
                $product_list[$prodKey]['taglist'] = $list;    
            }
        }

        //计算分页
        $pageCount = ceil($num/$pageSize);
        if( ($page-2)/5 > 1){
            $a = ($page-2)%5;
            $b = floor(($page-2)/5);
            $max = ($b+1)*5 + $a;
        }else{
            $max = 10;
        }
        $max_page = $max>$pageCount ? $pageCount : $max;
        $min_page = ($max_page-9) > 0 ? ($max_page-9) : 1;

        $data = ['list' => $product_list , 'pageTotal' => $num , 'page'=> $page , 'pageSize'=> $pageSize, 'pageCount'=>$pageCount,'max_page'=>$max_page,'min_page'=>$min_page];
        return  $data;
    }

    public function actionDetails($id)
    {
        //产品详情
        $webPage   = new WebPage();
        $productInfo = $webPage->getProductDetails($id);

        $ProductParentColumn = Functions::getProductParentColumn($productInfo['list']['cate_id']);
        $cateIsHide = Functions::cateIsHide($ProductParentColumn);
        if($cateIsHide){
            $productInfo['function_list'] = [];
        }

        //成分列表
        $componentList = array();
        if($productInfo['list']['id']){
            $componentList = $webPage->getComponentList($productInfo['list']['id']);
        }

		//获取用户信息
        $userId = !Yii::$app->user->isGuest ? Yii::$app->user->identity->getId() : "";
        $userinfo = array();
        if($userId){
            $userinfo = User::findOne($userId);
        }
		
        //产品评论列表
        $commentList = $webPage->getProductComment($id);

        $brandInfo = array();
        $recommendProduct = array();
        if($brandId = $productInfo['list']['brand_id']){
            //同品牌推荐产品
            $recommendProduct['brand'] = $webPage->getProductList($page = '1',$pageSize = '8',$cateId = '',$brandId,$keyword = '',$recommend = '',$top='',$orderBy = 'recommend_time desc,is_top desc,comment_num desc,star desc',$id);

            //品牌详情
            $brandInfo = $webPage->getBrandDetails($brandId);
        }

        if($cateId = $productInfo['list']['cate_id']){
            //同分类推荐产品
            $recommendProduct['cate'] = $webPage->getProductList($page = '1',$pageSize = '8',$cateId,$brandId = '',$keyword = '',$recommend = '',$top='',$orderBy = 'recommend_time desc,is_top desc,comment_num desc,star desc',$id);
        }


        //推荐文章
        $hotArticle = $webPage->getHotArticle($page = '1',$pageSize = "5",$id,'1');

        //文章热词列表
        $articleWord = $webPage->getHotwordList(20);

        //TAG
        $taglist = array();
        if($productInfo['safe_list']){
            foreach($productInfo['safe_list'] as $key=>$val){
                if( $key == "孕妇慎用" && count($val) == 0){
                    $taglist[] = "孕妇适用";
                }else if( $key == "香精" && count($val) == 0){
                    $taglist[] = "无香精";
                }else if( $key == "防腐剂" && count($val) == 0){
                    $taglist[] = "无防腐剂";
                }else if( $key == "风险" && count($val) == 0){
                    $taglist[] = "无风险";
                }
            }
        }

        //广告列表
        $advertisementList = [
            "left1" => $webPage->getAdList($type = "product/details",$position = "left",$sort = "1"),
        ];

        //上榜商品
        $position = "";
        if($productInfo['list']['brand_id']){
            $sql = "SELECT id,product_name,alias,product_img FROM {{%product_details}} WHERE status = 1 AND brand_id = '{$productInfo['list']['brand_id']}' AND is_top = 1 ORDER BY has_img DESC,(CASE WHEN price !='' THEN '1' ELSE '0' END) DESC, comment_num desc,star desc LIMIT 10";
            $top_list  = Yii::$app->db->createCommand($sql)->queryAll();
            $top_list = array_column($top_list,'id');

            if($top_list && (array_search($productInfo['list']['id'],$top_list) !== false) ){
                $position = array_search($productInfo['list']['id'],$top_list) + 1;
            }
        }

        //标题修改
        $this->GLOBALS['title'] = $productInfo['list']['product_name'] . '怎么样_成分分析-颜究院';
        $this->GLOBALS['description'] = $productInfo['list']['product_explain'];
        $this->GLOBALS['keywords'] = $productInfo['list']['product_name'] . '，' . $productInfo['list']['product_name'] . '怎么样，' . $productInfo['list']['product_name'] . '成分分析';

        return $this->renderPartial('details.htm', [
            'productInfo' => $productInfo['list'],
            'function_list' => $productInfo['function_list'],
            'safe_list' => $productInfo['safe_list'],
            'commentList' => $commentList,
            'recommendProduct' => $recommendProduct,
            'brandInfo' => $brandInfo,
            'hotArticle' => $hotArticle,
            'articleWord' => $articleWord,
            'componentList' => $componentList,
            'position' => $position,
            'userinfo' => $userinfo,
            'taglist' => $taglist,
            'advertisementList' => $advertisementList,
            'GLOBALS' => $this->GLOBALS,
            'Tools' => new Tools,
        ]);
    }

    //ajax返回数据
    public function actionAjaxIndexData()
    {
        $webPage   = new WebPage();

        $user_id = !Yii::$app->user->isGuest ? Yii::$app->user->identity->getId() : "";
        $type = isset($_GET['type']) ? trim($_GET['type']) : '';
        $post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : '';
        $comment = isset($_GET['comment']) ? trim($_GET['comment']) : "";
        $image = isset($_GET['image']) ? trim($_GET['image']) : "";

        if(Yii::$app->request->isAjax){
            $commentList = [];
            if($type == 'comment'){
                //添加评论
                self::addComment($user_id,$post_id,$comment,$image);
                //返回评论列表
                $commentList = $webPage->getProductComment($post_id);
            }else if($type == 'login'){
                $comment_list = array(
                    'post_id' => $post_id,
                    'comment' => $comment,
                    'image' => $image,
                );
                //记录评论信息
                $session = \Yii::$app->session;
                $session->set('comment_list',$comment_list);
            }

            $result = $this->renderPartial('data.htm', [
                'commentList' => $commentList,
                'type' => $type,
                'GLOBALS' => $this->GLOBALS,
                'Tools' => new Tools,
            ]);

            echo json_encode($result);
        }
    }

    //上传图片
    public function actionAjaxUploadImage(){

        $file = $_FILES['inpfile'];

        $ext = explode('.', $file['name']);
        $ext = end($ext);

        $image = date("Ymd") . rand(1,10000) . "." . $ext;
        $fullname = Yii::$app->params['environment'] == "Development" ? "cs/uploads/" : "uploads/";
        $path = "comment_img/" . date("Ymd") ."/" .$image;

        //上传到OSS
        $upload = new OssUpload();
        $upload->upload($file["tmp_name"],$fullname.$path);

        return $path;
    }

    //添加评论
    static function addComment($user_id,$post_id,$comment,$image){

        $model = new Comment();
        $model->user_id = $user_id;
        $model->post_id = $post_id;
        $model->type = "1";
        $model->comment = $comment;
        $model->user_type = '0';
        $model->referer = 'h5';
        if($model->save(false)){
            //用户名同步
            $model = Comment::findOne($model->id);
            $userInfo = User::findOne($model->user_id);
            $model->author = $userInfo->username;
            $model->save(false);

            //图片附件
            if($image){
                $attach = new Attachment();
                $attach->cid = $model->id;
                $attach->uid = $user_id;
                $attach->attachment = $image;
                $attach->dateline = time();
                $attach->save(false);
            }

            //更新评论数
            $updateSql  = " UPDATE {{%product_details}} P SET P.comment_num = (SELECT COUNT(id) FROM {{%comment}}  WHERE type = '1' AND post_id = '$post_id' AND status = 1)  WHERE P.id = '$post_id'";
            $return = Yii::$app->db->createCommand($updateSql)->execute();
        }
    }

    //处理网页keywords字段
    static function getKeywords($list,$componentList){
        //'产品名，备案号，生产企业（中）生产企业（英），所属分类，规格/参考价，含有成分（字数在100字以内）
        $str = "";
        $str .= $list['product_name'] ? $list['product_name']."，" : "";
        $str .= $list['standard_number'] ? $list['standard_number']."，" : "";
        $str .= $list['product_company'] ? $list['product_company']."，" : "";
        $str .= $list['en_product_company'] ? $list['en_product_company']."，" : "";
        $str .= $list['cate_name'] ? $list['cate_name']."，" : "";
        $str .= $list['form'] && $list['price'] ? $list['form']."/".$list['price']."，" : "";

        if($componentList){
            foreach($componentList as $val){
                $str .= $val['name'].'、';
            }
        }
        return mb_substr($str,0,100,"utf-8");
    }

}
