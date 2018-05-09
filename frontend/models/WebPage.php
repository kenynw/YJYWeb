<?php
namespace frontend\models;
use yii;
use yii\base\Model;
use common\models\User;
use common\functions\Tools;
use common\functions\Efficacy;
use common\models\ArticleCategory;
use common\models\Article;
use common\models\ProductDetails;
use common\models\ProductCategory;
use frontend\controllers\BaseController;
use common\functions\Functions;

/**
 * Signup form
 */
class WebPage extends Model
{
    //----------------------------------------------------------------产品-------------------------------------------------------------------------------

    //产品列表
    public function getProductList($page = '1',$pageSize = '20',$cateId = '',$brandId = '',$keyword = '',$recommend = '',$top='',$orderBy='',$productId =''){

        $pageMin = ($page - 1) * $pageSize;
        $whereStr = "P.status = 1";
        $whereStr .= $cateId ? " AND P.cate_id='$cateId'" : "";                        //分类id
        $whereStr .= $brandId ? " AND P.brand_id='$brandId'" : "";                     //品牌id
        $whereStr .= $recommend != "" ? " AND P.is_recommend='$recommend'" : "";      //是否推荐
        $whereStr .= $top != "" ? " AND P.is_top='$top'" : "";                         //是否上榜

        if($keyword){
            $keyword = str_replace("sk2","sk-II",$keyword);

            $this::addKeyword($keyword);
            $whereStr .= " AND P.product_name like '%$keyword%'";
        }

        //排除id
        if($productId){
            $whereStr .= " AND P.id != '$productId'";
        }
        $orderBy = $orderBy ? $orderBy :'P.comment_num DESC,P.is_recommend DESC,P.is_complete DESC,P.has_img DESC,P.has_price DESC,P.has_brand DESC,P.created_at DESC,P.id DESC';
        $count  = "SELECT count(id)  FROM {{%product_details}} P WHERE $whereStr";
        $num    = Yii::$app->db->createCommand($count)->queryScalar();

        $sql = "SELECT pc.cate_name,P.* FROM {{%product_details}} P LEFT JOIN {{%product_category}} pc ON P.cate_id=pc.id 
                WHERE $whereStr ORDER BY $orderBy LIMIT $pageMin,$pageSize";
        $product_list  = Yii::$app->db->createCommand($sql)->queryAll();

        //特征标签
        foreach($product_list as $key=>$val){
            //产品图地址处理
            $product_list[$key]['product_img']  = Functions::get_image_path($val['product_img'],1);

            $sql = "SELECT ct.tagname FROM {{%common_tagitem}} cti LEFT JOIN {{%common_tag}} ct ON (cti.tagid = ct.tagid AND idtype = 1) WHERE itemid = {$val['id']} LIMIT 3";
            $taglist  = Yii::$app->db->createCommand($sql)->queryColumn();

            //查询关联的成分id
            $sql = "SELECT pc.* FROM {{%product_relate}} pr LEFT JOIN {{%product_component}} pc on pr.component_id=pc.id  WHERE product_id = {$val['id']}";
            $component_list  = Yii::$app->db->createCommand($sql)->queryAll();
            //获取功效列表
            $efficacyList = Efficacy::getEfficacyList($component_list,$val['cate_name']);

            //TAG
            $tag = [];
            if($efficacyList['safe_list']){
                foreach($efficacyList['safe_list'] as $ke=>$va){
                    if( $ke == "孕妇慎用" && count($va) == 0){
                        $tag[] = "孕妇适用";
                    }else if( $ke == "香精" && count($va) == 0){
                        $tag[] = "无香精";
                    }else if( $ke == "防腐剂" && count($va) == 0){
                        $tag[] = "无防腐剂";
                    }else if( $ke == "风险" && count($va) == 0){
                        $tag[] = "无风险";
                    }
                }
            }
            $product_list[$key]['tag'] = $tag;

            if($taglist){
                $product_list[$key]['taglist'] = $taglist;
            }else{
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

                $product_list[$key]['taglist'] = $list;
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
    /**
     * [getProductList 产品列表]
     * @param  string $page      [当前页数]
     * @param  string $pageSize  [每页条数]
     * @param  string $cateId    [栏目ID]
     * @param  string $brandId   [品牌ID]
     * @param  string $recommend [是否推荐]
     * @param  string $top       [是否上榜]
     * @return [type]            [description]
     */
    public function newProductList($params){

        $idArr          = [];
        $tagArr         = [];
        $cateArr        = [];
        $cidArr         = [];
        $newIdArr       = [];
        $newComponent   = [];

        $cateId     = isset($params['cateId']) ? intval($params['cateId']) : '';
        $brandId    = isset($params['brandId']) ? intval($params['brandId']) : '';
        $recommend  = isset($params['recommend']) ? intval($params['recommend']) : '';
        $top        = isset($params['top']) ? intval($params['top']) : '';
        $page       = isset($params['page']) ? intval($params['page']) : '1';
        $pageSize   = isset($params['pageSize']) ? intval($params['pageSize']) : '20';

        $whereStr  = "P.status = 1";
        $whereStr .= $cateId ? " AND P.cate_id='$cateId'" : "";                       
        $whereStr .= $brandId ? " AND P.brand_id='$brandId'" : "";                     
        $whereStr .= $recommend != "" ? " AND P.is_recommend='$recommend'" : "";      
        $whereStr .= $top != "" ? " AND  P.is_top='$top'" : ""; 
                                
        $orderBy   = "P.is_complete DESC ,P.is_recommend DESC,P.recommend_time DESC,P.is_top DESC,P.has_img DESC,P.comment_num DESC,P.star DESC";

        $countSql  = "SELECT count(id)  FROM {{%product_details}} P WHERE $whereStr";
        $num       = Yii::$app->db->createCommand($countSql)->queryScalar();

        $pageMin   = ($page - 1) * $pageSize;
        $sql       = "  SELECT P.id,P.product_name,P.cate_id,P.price,P.form,P.star,P.product_img,P.is_top
                        FROM {{%product_details}} P
                        WHERE $whereStr
                        ORDER BY $orderBy
                        LIMIT $pageMin,$pageSize";
        $product_list  = Yii::$app->db->createCommand($sql)->queryAll();  

        //特征标签
        foreach($product_list as $key => $val){
            $idArr[]  = $val['id'];
            $cidArr[] = $val['cate_id'];
            $product_list[$key]['product_img'] = Functions::get_image_path($val['product_img'],1);
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
            if(isset($tagArr[$prodVal['id']])) {
                $product_list[$prodKey]['taglist'] = $tagArr[$prodVal['id']];
            }elseif(isset($newComponent[$prodVal['id']])){
                //查询关联的成分id
                $componentArr = $newComponent[$prodVal['id']];
                $cate_name    = isset($cateArr[$prodVal['cate_id']]) ? $cateArr[$prodVal['cate_id']] : "";
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
    //产品联想词列表
    public function getProductWord($keyword,$type='0'){

        if($type == "1"){
            $sql = "SELECT id,name,img FROM {{%brand}} WHERE name = '$keyword' LIMIT 1";
            $list1  = Yii::$app->db->createCommand($sql)->queryAll();

            $sql = "SELECT id,product_name AS name,CAST('' AS char) img FROM {{%product_details}} WHERE status = 1 AND product_name like '%$keyword%' ORDER BY has_img DESC,is_recommend DESC,is_top DESC,comment_num DESC,star DESC LIMIT 5";
            $list2  = Yii::$app->db->createCommand($sql)->queryAll();

            $list = array_merge($list1,$list2);
        }else{
            $sql = "SELECT id,product_name AS name,CAST('' AS char) img FROM {{%product_details}} WHERE status = 1 AND product_name like '%$keyword%' ORDER BY has_img DESC,is_recommend DESC,is_top DESC,comment_num DESC,star DESC LIMIT 5";
            $list  = Yii::$app->db->createCommand($sql)->queryAll();
        }

        return $list;
    }

    //推荐产品（右侧栏）
    public function getRecommendProduct($page = '1',$pageSize = "5"){
        $pageMin = ($page - 1) * $pageSize;
        $sql = "SELECT * FROM {{%product_details}} WHERE is_recommend =1 ORDER BY recommend_time DESC,id DESC LIMIT $pageMin,$pageSize";
        $list  = Yii::$app->db->createCommand($sql)->queryAll();

        $count = "SELECT count(id)  FROM {{%product_details}} WHERE is_recommend =1";
        $num    = Yii::$app->db->createCommand($count)->queryScalar();
        //计算分页
        $pageCount = ceil($num/$pageSize);

        $data = ['list' => $list , 'pageTotal' => $num , 'page'=> $page , 'pageSize'=> $pageSize, 'pageCount'=>$pageCount];
        return  $data;
    }

    //产品分类列表
    public function getProductCateList(){
        $sql = "SELECT * FROM {{%product_category}} WHERE status = 1 AND is_old = 1 ORDER BY sort ASC LIMIT 8";
        $list  = Yii::$app->db->createCommand($sql)->queryAll();
        return  $list;
    }

    //产品成分详情
    public function getComponentList($id = '0'){
        $list = array();
        if($id){
            $sql = "SELECT pc.* FROM {{%product_relate}} pr LEFT JOIN {{%product_component}} pc on pr.component_id=pc.id  WHERE product_id = '$id' AND pc.name != '' ORDER BY pr.id ASC";
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
            $sql = "SELECT pc.cate_name,pd.* FROM {{%product_details}} pd LEFT JOIN {{%product_category}} pc ON pd.cate_id=pc.id WHERE pd.id = '$id'";
            $product_list  = Yii::$app->db->createCommand($sql)->queryOne();
        }

        //获取功效列表
        $efficacyList = Efficacy::getEfficacyList($component_list,$product_list['cate_name']);
        $product_list['taglist'] = $efficacyList;

        $data = array(
            'list' => $product_list,
            'function_list' => $efficacyList['function_list'],
            'safe_list' => $efficacyList['safe_list'],
        );

        return  $data;
    }

    //产品评论列表
    public function getProductComment($product_id = '',$num = '3'){
        //参数
        $id         = intval($product_id);
        $pageMin    = $num;

        //排序
        $whereStr   = " C.type = '1' AND C.first_id = '0' AND C.post_id = '$id' AND C.status = '1' ";
        $rows       = [];

        /* 分页 */
        $num        = 0;
        $fieldStr   = 'C.is_digest DESC,C.like_num DESC ';
        $sql        = "SELECT COUNT(*) AS num  FROM {{%comment}}  C  WHERE $whereStr";
        $num        = Yii::$app->db->createCommand($sql)->queryScalar();


        $sql    = "SELECT C.id,C.user_id,C.comment,C.like_num,C.is_digest FROM {{%comment}} C 
                   WHERE $whereStr ORDER BY  $fieldStr ,C.created_at DESC LIMIT $pageMin";
        $commentList   = Yii::$app->db->createCommand($sql)->queryAll();

        if(!empty($commentList)){
            foreach ($commentList as $key => $value) {
                $rows[$key]    = Functions::getCommentInfo($value['id'],'0');
                $reply         = Functions::getCommentReplyList($value['id']);
                if($reply) $rows[$key]['reply'] =  $reply;
                unset($reply);
            }
        }
        return $rows;
    }

    //产品评论列表
    public function getProductComment1($page = '1',$pageSize = '10',$product_id = '',$user_id = ''){

        $time = time() - 60*2;
        $pageMin = ($page - 1) * $pageSize;

        $count = "SELECT count(id) FROM {{%comment}} WHERE post_id = '$product_id' AND type=1 AND status = 1";
        $num    = Yii::$app->db->createCommand($count)->queryScalar();
        $pageCount = ceil($num/$pageSize);

        $list1 = array();
        $whereStr = "";
        if($user_id){
            $sql = "SELECT * FROM {{%comment}} WHERE post_id = '$product_id' AND user_id = '$user_id' AND created_at > '$time' AND type=1 AND status = 1 ORDER BY created_at DESC LIMIT 1";
            $list1  = Yii::$app->db->createCommand($sql)->queryAll();

			if($list1){
				$pageSize = $pageSize - 1;
				$whereStr .= " AND id != '{$list1[0]['id']}'";
			}
        }

        $sql = "SELECT * FROM {{%comment}} WHERE post_id = '$product_id' AND type=1 AND status = 1 $whereStr ORDER BY is_digest DESC ,created_at DESC  LIMIT $pageMin,$pageSize";
        $list  = Yii::$app->db->createCommand($sql)->queryAll();

        $list = $list1 ? array_merge($list1,$list) : $list;

        if($list){
            foreach($list as $key=>$val){
                $userinfo = User::findOne($val['user_id']);
                $list[$key]['img'] = isset($userinfo->img) ? $userinfo->img : "";

                //表情处理
                $list[$key]['comment'] = Tools::userTextDecode($val['comment']);
            }
        }

        //计算分页
        if( ($page-2)/5 > 1){
            $a = ($page-2)%5;
            $b = floor(($page-2)/5);
            $max = ($b+1)*5 + $a;
        }else{
            $max = 10;
        }

        $max_page = $max>$pageCount ? $pageCount : $max;
        $min_page = ($max_page-9) > 0 ? ($max_page-9) : 1;

        $data = ['list' => $list , 'pageTotal' => $num , 'page'=> $page , 'pageSize'=> $pageSize,'pageCount'=>$pageCount,'max_page'=>$max_page,'min_page'=>$min_page];
        return  $data;
    }

    //----------------------------------------------------------------文章-------------------------------------------------------------------------------

    //文章列表
    public function getArticleList($page = '1',$pageSize = '20',$cateId = '',$keyword = '',$hotId = '',$recommend = '',$orderBy = 'id desc'){

        $pageMin    = ($page - 1) * $pageSize;
        $whereStr = "status = 1 AND stick = 0 ";
        $whereStr .= $recommend != "" ? " AND is_recommend='$recommend'" : "";

        //标题搜索
        if($keyword){
            $keywords = explode(" ",$keyword);
            if(count($keywords) > 1){
                foreach($keywords as $val){
                    $whereStr .= " AND title like '%$val%'";
                }
            }else{
                $whereStr .= " AND title like '%$keyword%'";
            }
        }

        //热词搜索
        if($hotId){
            $sql = "SELECT itemid FROM {{%common_tagitem}} WHERE tagid = '$hotId' AND idtype = 2";
            if(Yii::$app->db->createCommand($sql)->queryColumn()){
                $ids = join(',', Yii::$app->db->createCommand($sql)->queryColumn());
                $whereStr .= " AND id in ($ids)";
            }else{
                $whereStr .= " AND id < 0";
            }
        }

        //有无传cateId分类列表
        if (!empty($cateId)) {
            $cateSql = "SELECT id FROM {{%article_category}} WHERE parent_id = $cateId";
            if (!empty(Yii::$app->db->createCommand($cateSql)->queryColumn())) {
                $cate_ids = join(',', Yii::$app->db->createCommand($cateSql)->queryColumn());
                $whereStr .= " AND (cate_id='$cateId' OR cate_id in ($cate_ids))";
            }else{
                $whereStr .= " AND cate_id='$cateId'";
            }
        }

        $count = "SELECT count(id)  FROM {{%article}} WHERE $whereStr";
        $num    = Yii::$app->db->createCommand($count)->queryScalar();

        $sql = "SELECT * FROM {{%article}} WHERE $whereStr ORDER BY $orderBy limit $pageMin,$pageSize";
        $list  = Yii::$app->db->createCommand($sql)->queryAll();
        if($page == 1){
            $stick_res = Functions::getStickArticle();
            $list  = array_merge($stick_res,$list);
        }
        
        if($list){
            foreach($list as $key=>$val){

                //标题处理（显示2行）
                preg_match_all("/[\x{4e00}-\x{9fa5}]|[a-zA-Z]|[0-9]|[\@\#$\%\&\*\(\)\!]/u",$val['title'],$match);

                $i = 0;
                $data = "";
                $list[$key]['title1'] = $val['title'];
                foreach($match[0] as $v){
                    if(preg_match("/[a-zA-Z]|[0-9]/u",$v)){
                        $i = $i + 0.5;
                    }else if(preg_match_all("/[\x{4e00}-\x{9fa5}]|[\@\#\$\%\&\*\(\)\!]/u",$v)){
                        $i = $i + 1;
                    }

                    $data .= $v;
                    if(ceil($i) > 20){
                        $list[$key]['title1'] = $data ."...";
                        break;
                    }
                }

                //内容处理（抽取文章前60字展示）
                $list[$key]['content'] = mb_substr(strip_tags($val['content']),0,60,"utf-8") . "...";

                //获取热词列表
                $sql = "SELECT ct.tagid,ct.tagname FROM {{%common_tagitem}} cti LEFT JOIN {{%common_tag}} ct ON cti.tagid = ct.tagid WHERE cti.itemid = '{$val['id']}' AND idtype = 2 AND ct.tagname != ''";
                $list[$key]['taglist']  = Yii::$app->db->createCommand($sql)->queryAll();
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

        $data = ['list' => $list , 'pageTotal' => $num , 'page'=> $page , 'pageSize'=> $pageSize,'pageCount'=>$pageCount,'max_page'=>$max_page,'min_page'=>$min_page];
        return  $data;
    }

    //文章联想词列表
    public function getArticleWord($keyword,$num = "5"){
        $sql = "SELECT id,title FROM {{%article}} WHERE status = 1 AND title like '%$keyword%' ORDER BY is_recommend DESC,id DESC LIMIT $num";
        $list  = Yii::$app->db->createCommand($sql)->queryAll();

        return ['status' => $list ? 1 : 0 ,'data' => ['article'=>$list]];
    }

    //文章热词列表
    public function getHotwordList($num = "10"){
        $sql = "SELECT tagid,tagname FROM {{%common_tag}} ct WHERE type = 2 ORDER BY ct.count DESC limit $num";
        $list  = Yii::$app->db->createCommand($sql)->queryAll();

        return $list;
    }

    //文章分类列表
    public function getArticleCateList($has_child = 0){
        $sql = "SELECT * FROM {{%article_category}} WHERE status = 1 AND parent_id = 0 ORDER BY `order`";
        $list  = Yii::$app->db->createCommand($sql)->queryAll();
        if($has_child){
            foreach ($list as $key=>$val) {
                $id = $val['id'];
                $kid = "SELECT * FROM {{%article_category}} WHERE status = 1 AND parent_id = $id ORDER BY `order`";
                $kidlist = Yii::$app->db->createCommand($kid)->queryAll();
                $list[$key]['kid'] = $kidlist;

                $ids = array();
                if($kidlist){
                    foreach ($kidlist as $v){
                        $ids[] = $v['id'];
                    }
                }
                $list[$key]['child_ids'] = $ids;

            }
        }
        return  $list;
    }

    //文章详情
    public function getArticleDetails($id = '0'){

        $list = array();
        if($id){
            $sql = "SELECT * FROM {{%article}} WHERE id = '$id'";
            $list  = Yii::$app->db->createCommand($sql)->queryOne();

            //获取热词列表
            $sql = "SELECT ct.tagid,ct.tagname FROM {{%common_tagitem}} cti LEFT JOIN {{%common_tag}} ct ON cti.tagid = ct.tagid WHERE cti.itemid = '$id' AND idtype = 2 AND ct.tagname !='' limit 5";
            $list['taglist']  = Yii::$app->db->createCommand($sql)->queryAll();
            $list['tag_blist'] = $this->getBrandTag($list);
            //获取文章的一级、二级分类
            $list['catelist'] = array();
            if (!empty($list['cate_id'])) {
                $sql = "SELECT * FROM {{%article_category}} WHERE id = {$list['cate_id']}";
                $temp_list = Yii::$app->db->createCommand($sql)->queryOne();

                if($temp_list['parent_id'] == 0){
                    $list['catelist'][] = $temp_list;
                }else{
                    $sql = "SELECT * FROM {{%article_category}} WHERE id = {$temp_list['parent_id']}";
                    $parent_list = Yii::$app->db->createCommand($sql)->queryOne();

                    $list['catelist'][] = $parent_list;
                    $list['catelist'][] = $temp_list;
                }
            }

            //处理表情
            $list['content'] = Tools::userTextDecode($list['content']);
        }

        return  $list;
    }

    //热门文章（右侧栏）
    public function getHotArticle($page = '1',$pageSize = "5",$product_id = '',$refresh=''){

        $cache     =    Yii::$app->cache;
        $time = strtotime("-7 days");
        $list = $cache->get('pc_hot_article');

        if(empty($list) || $refresh){
            //产品详情页，优先展示与产品相关的文章
            $sql    =   "SELECT id,title,product_id,article_img,created_at,click_num 
                        FROM yjy_article 
                        ORDER BY week_click_time > '$time' DESC,FIND_IN_SET('$product_id',product_id) DESC ,week_click DESC limit 15";
            $list   =   Yii::$app->db->createCommand($sql)->queryAll();
            foreach ($list as $key => $value) {
                $list[$key]['article_img'] = Functions::get_image_path($value['article_img'],1,400,250);
            }

            //随机排序
            shuffle($list);
            $cache->set('pc_hot_article',$list,1800);
        }

        $pageMin= ($page - 1) * $pageSize;
        $num = count($list);
        $pageCount = ceil($num/$pageSize);
        $list = array_slice($list,$pageMin,$pageSize);

        $data = ['list' => $list , 'pageTotal' =>  $num, 'page'=> $page , 'pageSize'=> $pageSize, 'pageCount'=>$pageCount];
        return  $data;
    }

    //----------------------------------------------------------------品牌-------------------------------------------------------------------------------

    //品牌列表
    public function getBrandList($page = '1',$pageSize = '20',$cateId = '',$recommend = '',$orderBy = 'id desc'){
        $pageMin    = ($page - 1) * $pageSize;
        $whereStr = "status = 1";
        $whereStr .= $cateId ? " AND cate_id='$cateId'" : "";
        //$whereStr .= $recommend != "" ? " AND is_recommend='$recommend'" : "";

        $sql = "SELECT * FROM {{%brand}} WHERE $whereStr ORDER BY $orderBy limit $pageMin,$pageSize";
        $list  = Yii::$app->db->createCommand($sql)->queryAll();

        $count = "SELECT count(id)  FROM {{%brand}} WHERE $whereStr";
        $num    = Yii::$app->db->createCommand($count)->queryScalar();

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

        $data = ['list' => $list , 'pageTotal' => $num , 'page'=> $page , 'pageSize'=> $pageSize,'pageCount'=>$pageCount,'max_page'=>$max_page,'min_page'=>$min_page];
        return  $data;
    }

    //品牌分类列表
    public function getBrandCateList(){
        $sql = "SELECT * FROM {{%brand_category}} WHERE status = 1 ORDER BY sort,created_at DESC LIMIT 8";
        $list  = Yii::$app->db->createCommand($sql)->queryAll();
        return  $list;
    }

    //品牌详情
    public function getBrandDetails($id = '0'){

        $list = array();
        if($id){
            $sql = "SELECT * FROM {{%brand}} WHERE id = '$id'";
            $list  = Yii::$app->db->createCommand($sql)->queryOne();

            if($list){
                $list['product_num'] = ProductDetails::find()->where(['brand_id' => $id,'status'=>'1'])->count();
            }
        }
        return  $list;
    }

    //推荐品牌（右侧栏）
    public function getRecommendBrand($num,$brand_id = ''){

        $cache     =    Yii::$app->cache;
        // 查看缓存是否存在
        $brandList = $cache->get('pc_brand_list');
        if(empty($brandList)) {
            $whereStr = "status = 1";
            if ($brand_id) {
                $whereStr .= " AND id != '$brand_id'";
            }

            $sql = "SELECT id,name,ename,img FROM {{%brand}} WHERE $whereStr";
            $brandList = Yii::$app->db->createCommand($sql)->queryAll();

            $cache->set('pc_brand_list',$brandList,3600);
        }

        $list = array_rand($brandList,$num);
        $result = array();
        foreach($list as $val){
            $result[] = $brandList[$val];
        }

        return $result;
    }

    //品牌文章
    public function getBrandArticle($page = '1',$pageSize = '5',$brand_id){

        $pageMin    = ($page - 1) * $pageSize;

        $sql = "SELECT ar.* FROM {{%product_details}} pd LEFT JOIN {{%article}} ar ON FIND_IN_SET(pd.id,ar.product_id)
                WHERE ar.product_id !='' AND pd.brand_id = '$brand_id' GROUP BY ar.id  ORDER BY created_at DESC limit $pageMin,$pageSize";
        $list  = Yii::$app->db->createCommand($sql)->queryAll();

        if($list){
            foreach($list as $key=>$val){
                //内容处理（抽取文章前60字展示）
                $list[$key]['content'] = mb_substr(strip_tags($val['content']),0,60,"utf-8");

                //获取热词列表
                $sql = "SELECT ct.tagid,ct.tagname FROM {{%common_tagitem}} cti LEFT JOIN {{%common_tag}} ct ON cti.tagid = ct.tagid WHERE cti.itemid = '{$val['id']}' AND idtype = 2 limit 5";
                $list[$key]['taglist']  = Yii::$app->db->createCommand($sql)->queryAll();
            }
        }

        $count = "SELECT count(t.id) FROM (SELECT ar.*  FROM {{%product_details}} pd LEFT JOIN {{%article}} ar ON FIND_IN_SET(pd.id,ar.product_id)
                WHERE ar.product_id !='' AND pd.brand_id = '$brand_id' GROUP BY ar.id) t";
        $num    = Yii::$app->db->createCommand($count)->queryScalar();
        $pageCount = ceil($num/$pageSize);


        //有产品的文章
//        $sql = "SELECT id,product_id FROM {{%article}} WHERE product_id !=''";
//        $article_list  = Yii::$app->db->createCommand($sql)->queryAll();
//
//        $ids = array();
//        if($article_list){
//            foreach($article_list as $val){
//                $sql = "SELECT id FROM {{%product_details}} WHERE id in ({$val['product_id']}) AND brand_id = '$brand_id'";
//                if(Yii::$app->db->createCommand($sql)->queryScalar()){
//                    $ids[] = $val['id'];
//                }
//            }
//        }
//
//        if($ids){
//            $ids = join(",",$ids);
//
//            $sql = "SELECT * FROM {{%article}} WHERE id in ($ids) ORDER BY created_at DESC limit $pageMin,$pageSize";
//            $list  = Yii::$app->db->createCommand($sql)->queryAll();
//
//            $count = "SELECT count(id)  FROM {{%article}} WHERE id in ($ids)";
//            $num    = Yii::$app->db->createCommand($count)->queryScalar();
//            $pageCount = ceil($num/$pageSize);
//        }

        $data = ['list' => $list , 'pageTotal' => isset($num) ? $num : "" , 'page'=> $page , 'pageSize'=> $pageSize,'pageCount'=> isset($pageCount) ? $pageCount : ""];
        return  $data;
    }


    //----------------------------------------------------------------关键词-------------------------------------------------------------------------------

    //产品关键词列表
    public function getHotKeyword($num = "8"){
        $sql = "SELECT keyword FROM {{%hot_keyword}} ORDER BY num DESC LIMIT $num";
        $list  = Yii::$app->db->createCommand($sql)->queryColumn();
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

    //----------------------------------------------------------------成分-------------------------------------------------------------------------------

    //成分详情
    public function getComponentDetails($id = '0'){

        $list = array();
        if($id){
            $sql = "SELECT * FROM {{%product_component}} WHERE id = '$id'";
            $list  = Yii::$app->db->createCommand($sql)->queryOne();
        }
        return  $list;
    }

    //相似成分
    public function getSimilarCompoment($id,$action){

        //使用目的
        $list = array();
        $result = array();

        if($action){
            $actions = explode("，",$action);
            foreach($actions as $val){
                $sql = "SELECT id FROM {{%product_component}} WHERE component_action like '%$val%' and id != '$id'";
                $list = array_merge($list,Yii::$app->db->createCommand($sql)->queryColumn());
            }

            if($list){
                //相似度最高的10个
                $list = array_count_values($list);
                arsort($list);
                $list = array_keys($list);
                $list = array_slice($list,0,10);

                foreach($list as $val){
                    $sql = "SELECT id,name FROM {{%product_component}} WHERE id = '$val'";
                    $result  = array_merge($result,Yii::$app->db->createCommand($sql)->queryAll());
                }
            }
        }

        return $result;
    }

    //含有成分产品列表
    public function getCompomentProductList($page = '1',$pageSize = '16',$component_id = '',$orderBy = ''){

        $idArr          = [];
        $tagArr         = [];
        $cateArr        = [];
        $cidArr         = [];
        $newIdArr       = [];
        $newComponent   = [];

        //分页判断
        $pageMin = ($page - 1) * $pageSize;
        $orderBy = "P.comment_num DESC,P.is_recommend DESC,P.is_complete DESC,P.has_img DESC,P.has_price DESC,P.has_brand DESC,P.created_at DESC,P.id DESC";

        $whereStr   = " P.status = '1' ";
        $whereStr  .= $component_id ? " AND R.component_id = '$component_id' " : '';

        //总数
        $sql        = "SELECT COUNT(*) FROM {{%product_details}} P LEFT JOIN {{%product_relate}} R ON P.id = R.product_id 
                       WHERE $whereStr";
        $num        = Yii::$app->db->createCommand($sql)->queryScalar();

        //先搜索产品
        $sql = "SELECT P.id,P.product_name,P.cate_id,P.price,P.form,P.star,P.product_img,P.is_top
                FROM {{%product_details}} P 
                LEFT JOIN {{%product_relate}} R ON P.id = R.product_id 
                WHERE $whereStr
                ORDER BY $orderBy
                LIMIT $pageMin,$pageSize";
        $product_list  = Yii::$app->db->createCommand($sql)->queryAll();  

        //特征标签
        foreach($product_list as $key => $val){
            $idArr[]  = $val['id'];
            $cidArr[] = $val['cate_id'];
            $product_list[$key]['product_img'] = Functions::get_image_path($val['product_img'],1);
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
            if(isset($tagArr[$prodVal['id']])) {
                $product_list[$prodKey]['taglist'] = $tagArr[$prodVal['id']];
            }elseif(isset($newComponent[$prodVal['id']])){
                //查询关联的成分id
                $componentArr = $newComponent[$prodVal['id']];
                $cate_name    = $cateArr[$prodVal['cate_id']];
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

        $data = ['list' => $product_list , 'pageTotal' => count($num) , 'page'=> $page , 'pageSize'=> $pageSize, 'pageCount'=>$pageCount,'max_page'=>$max_page,'min_page'=>$min_page];
        return  $data;
    }


    //广告列表
    public function getAdList($type = "site/index",$position = "main",$sort = "1"){

        $time = time();
        $sql = "SELECT * FROM {{%advertisement}}WHERE '$time' BETWEEN start_time AND end_time AND status =1 AND type = '$type' AND position = '$position' AND sort = '$sort' ORDER BY id DESC limit 1"; 
        $advertisementList  = Yii::$app->db->createCommand($sql)->queryOne();

        return  $advertisementList;
    }

    private function getBrandTag($article){
        if(empty($article)){
            return false;
        }
        if(empty($article['brand_tag'])){
            $rand_arr = ['护肤品推荐','护肤品排行','价格表'];
            $brand_sql = "SELECT id,name FROM {{%brand}} WHERE status = 1";
            $brand_list  = Yii::$app->db->createCommand($brand_sql)->queryAll();
            $num = count($brand_list);
            $str = '';
            for ($i=0; $i < 3; $i++) { 
                $key    = rand(0,$num-1);
                $name   = $brand_list[$key]['name'].$rand_arr[rand(0,2)];
                $str    .= $brand_list[$key]['id'].'-'.$name.',';
                $res[]    = ['id'=>$brand_list[$key]['id'],'name'=>$name];
            }
            $str = substr($str,0,-1);
            $update_sql = "UPDATE {{%article}} SET brand_tag = '$str' WHERE id ='{$article['id']}'";
            Yii::$app->db->createCommand($update_sql)->execute();
        }else{
            $brand_arr = explode(',',$article['brand_tag']);
            foreach ($brand_arr as $key => $value) {
                $arr = explode('-',$value);
                $name = '';
                if(($count = count($arr)) > 2){
                    for ($i = 1; $i < $count; $i++){
                        if($i + 1 == $count){
                            $name .= $arr[$i];
                        }else{
                            $name .= $arr[$i].'-';
                        }
                    }
                }else{
                    $name = $arr['1'];
                }
                $res[] = ['id'=>$arr['0'],'name'=>$name];
            }
        }
        return $res;

    }

}
