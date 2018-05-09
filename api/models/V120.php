<?php
namespace api\models;

use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use yii\sphinx\Query;
use common\functions\Functions;
use common\functions\Tools;
use common\functions\Cosmetics;
use common\functions\SearchProduct;
use common\models\SignupForm;
use common\models\User;
use common\functions\Skin;
use common\functions\NoticeFunctions;
use common\functions\ReplyFunctions;
use common\models\Pms;
use common\models\NoticeUser;
use common\models\NoticeSystem;
use common\models\NoticeSystemRead;
use common\functions\Huodong;
use common\models\Article;
/**
 * APP接口类
 */
class V120 extends Model{
    //解释后参数
    static $data          =   [];
    //化妆品功效
    static $effects       =   ['美白','保湿','舒缓抗敏','去角质','去黑头'];
    //用户信息
    static $userInfo      =   [];
    //TOKEN验证
    static $tokenAction   =   [
                'addComment',
                'addCommentLike',
                'addRemind',
                'userInfo',
                'mobileBind',
                'userGrass',
                'userSkin',
                'saveSkin',
                'getSkinRecommend',
                'getSkinRecommendList',
                'readNotice',
                'faceList',
                'collect',
                'userComment',
                'userPms',
                'userUpdate',
                'userProduct',
                'userCollect',
                'userReply',
                'subAsk',
            ];
    //错误码
    static $ERROR         =   array(
        '0'     =>  '处理失败',
        '1'     =>  '处理成功',
        '-1'    =>  '帐号或密码错误',
        '-2'    =>  '账户不存在',
        '-3'    =>  '参数不完整,缺少 %s 参数',
        '-4'    =>  '注册失败',
        '-5'    =>  '%s 方法未定义',
        '-6'    =>  'TOKEN已过期',
        '-7'    =>  'ip受限',
        '-8'    =>  '产品不存在',
        '-9'    =>  '时间过期',
        '-10'   =>  '帐号已被封号',
        '-11'   =>  '帐号异常',
        '-12'   =>  '验证码无效或已过期',
        '-13'   =>  '短信发送失败',
        '-14'   =>  '手机格式有误',
        '-15'   =>  '评论不存在',
        '-16'   =>  '文章不存在',
        '-17'   =>  '用户已存在',
        '-18'   =>  '帐号已被禁言',
        '-19'   =>  '品牌不存在',
        '-20'   =>  '问题不存在',
        '-21'   =>  '成份不存在',
        '-22'   =>  '手机号已注册，请用该手机号登录',
        '-23'   =>  '百科不存在',
        '-200'  =>  '其他错误'
    );
    /**
     * [__construct 构造函数]
     * @param [type] $data [description]
     */
    PUBLIC function __construct($data) {
        self::$data = $data;
        //验证TOKEN
        self::$data['user_id'] = isset($data['token']) ? Functions::checkToken($data['token']) : 0 ;

        if(!self::$data['user_id'] && in_array($data['action'],self::$tokenAction)){
            $return = array('status' => '-6','msg'=>self::$ERROR['-6']);
            echo  json_encode($return);die;
        }
   }
    /**
     *  作用：检查参数完整性
     */
    PUBLIC function completeParameter($required = array()) {
        //验证参数完整性
        $data       = self::$data;
        $required   = $required ? $required : self::$required;
        $return     = false;
        foreach ($required as $key => $value) {
            if(!isset($data[$value]) || empty($data[$value])){
                $return = array('status' => '-3','msg'=>sprintf(self::$ERROR['-3'], $value));
                break;
            }
        }
        //不完整不通过
        if($return){ echo  json_encode($return);die;}
    }
    /**
     * [checkUserStatus 查询用户状态]
     * @param  [type] $uid [用户ID]
     * @return [type] BOOL    [是否正常]
     */
    PUBLIC function checkUserStatus($userId){
        //判断帐号是否正常
        $userSql   = "SELECT username,admin_id,mobile,email,img,status FROM {{%user}} WHERE id = '$userId'";
        $userInfo  = Yii::$app->db->createCommand($userSql)->queryOne();

        $return = '';
        if(!$userInfo){
            $return = array('status' => '-2','msg'=> self::$ERROR['-2']);
            echo  json_encode($return);die;
        }

        switch ($userInfo['status']) {
            case '3':
                $return = array('status' => '-10','msg'=> self::$ERROR['-10']);
                break;
            case '2':
                $return = array('status' => '-18','msg'=> self::$ERROR['-18']);
                break;
        }

        if($return){echo  json_encode($return); die;}

        self::$userInfo = $userInfo;
    }
    /**
     * [首页接口 -- 包括轮播~最热文章~分类~产品总数]
     * @return [json] [各数据块]
     */
    PUBLIC function index() {
        //参数
        $uid    = isset(self::$data['user_id']) ? intval(self::$data['user_id']) : '';

        $return = ['banner' => [],'articleGory' => [],'essence' => []];
        $time   = time();

        //查询轮播
        $return['banner'] = Functions::getBannerList(1);
        if(!empty($return['banner'])){
            foreach ($return['banner'] as $key => $value) {
                if($value['type'] == 3){
                    $articleInfo = Functions::getArticle($uid,$value['relation_id']);
                    $return['banner'][$key]['comment_num']  = $articleInfo['comment_num'];
                    $return['banner'][$key]['linkUrl']      = $articleInfo['linkUrl'];
                    $return['banner'][$key]['isGras']       = $articleInfo['isGras'];
                    unset($articleInfo);
                }
            }
        }

        //一级文章栏目
        $firstGory   = [];
        $articleGory = Functions::getArticleColumn();
        foreach ($articleGory as $key => $value) {
            unset($value['sub']);
            $firstGory[]  = $value;
        }
        $return['articleGory'] = $firstGory;
        //查询收集的产品数量
        $sql = "SELECT COUNT(*) AS num FROM {{%product_details}} WHERE  status = '1'";
        $return['num']    = Yii::$app->db->createCommand($sql)->queryScalar();
        
        //首页文章
        $param = ['page' => 1,'pageSize' => 10 ,'uid' => $uid];
        $recommendComment  = Functions::getRecommendComment($param);
        //处理不同版本所取的星级字段不同
        foreach ($recommendComment['data'] as $key=>$list){
            $recommendComment['data'][$key]['product']['star'] = $list['product']['cStar'];
            unset($recommendComment['data'][$key]['product']['cStar']);
        }

        $return['essence'] = $recommendComment['data'];
        $num               = $recommendComment['num'];
        
        $data = ['status' => 1, 'msg' => $return,'pageTotal' => $num, 'pageSize'=> 10];
        return  json_encode($data);
    }
    /**
     * [essenceList 精华点评列表]
     * @param  [int] $page [页数]
     * @param  [int] $pageSize [每页数]
     * @return [json]       [列表数据]
     */
    PUBLIC function essenceList() {
        //参数
        $uid        = isset(self::$data['user_id']) ? intval(self::$data['user_id']) : '';
        $pageSize   = isset(self::$data['pageSize']) ? intval(self::$data['pageSize']) : 10;
        $page       = isset(self::$data['page']) ? intval(self::$data['page']) : 1;

        $return     = [];
        $param      = ['page' => $page,'pageSize' => $pageSize,'uid' => $uid];
        //首页文章
        $recommendComment   = Functions::getRecommendComment($param);
        //把星级改为用户评论星级
        foreach ($recommendComment['data'] as $key=>$list){
            $recommendComment['data'][$key]['product']['star'] = $list['product']['cStar'];
            unset($recommendComment['data'][$key]['product']['cStar']);
        }
        
        $return['essence']  = $recommendComment['data'];
        $num                = $recommendComment['num'];
//        $param['pageSize']  = 1;
//        $askinfo = Functions::getRecommendAsk($param);
//        !empty($askinfo) ? $return['ask'] = $askinfo :'';
        $data = ['status' => 1, 'msg' => $return,'pageTotal' => $num, 'pageSize'=> $pageSize];
        return  json_encode($data);
    }
    /**
     * [articleList 首页热门文章列表]
     * @param  [int] $page [页数]
     * @param  [int] $pageSize [每页数]
     * @return [json]       [列表数据]
     */
    PUBLIC function articleList() {
        //参数
        $uid        = isset(self::$data['user_id']) ? intval(self::$data['user_id']) : '';
        $brandId    = isset(self::$data['brand_id']) ? intval(self::$data['brand_id']) : '';
        $categoryId = isset(self::$data['category_id']) ? intval(self::$data['category_id']) : '';
        $pageSize   = isset(self::$data['pageSize']) ? intval(self::$data['pageSize']) : 20;
        $page       = isset(self::$data['page']) ? intval(self::$data['page']) : 1;

        //栏目搜索
        $cateIds    = '';
        if($categoryId){
            $categoryIds = Functions::get_article_subs($categoryId);
            $cateIds = Functions::db_create_in($categoryId.$categoryIds,'cate_id');
        }

        //先搜索字符
        if($brandId){
            $sql    = "SELECT name  FROM {{%brand}} WHERE id = '$brandId'";
            $name   = Yii::$app->db->createCommand($sql)->queryScalar();
            if(!$name){
                return json_encode(['status' => -19, 'msg' => self::$ERROR[-19]]);
            }
            $query       = new Query();
            $articleRows = $query->select('id')->from('article')->match($name)->limit(1000)->all();

            $sql = "SELECT id  FROM {{%article}} WHERE  find_in_set('$brandId',brand_id) ";
            $articleBrand = Yii::$app->db->createCommand($sql)->queryAll();
            if(!$articleRows && !$articleBrand) return json_encode(['status' => 1 ,'msg' => [],'pageSize'=>$pageSize,'pageTotal'=>0]);
            foreach ($articleRows as $key => $value) {
                $idArr[] = $value['id'];
            }
            if($articleBrand){
                foreach ($articleBrand as $key => $value) {
                    $idArr[] = $value['id'];
                }
            }
            $idStr       = Functions::db_create_in($idArr,'id');
        }

        $whereStr   =  " status = '1'";
        $whereStr  .=  $brandId   ? '' : ' AND is_recommend = 1 ';
        $whereStr  .=  $brandId  ? ' AND '.$idStr : '';
        $whereStr  .=  $cateIds  ? ' AND '.$cateIds : '';

        /* 分页 */
        $rows   = array();
        $sql    = "SELECT COUNT(*) AS num FROM {{%article}} WHERE $whereStr";
        $num    = Yii::$app->db->createCommand($sql)->queryScalar();

        $pageMin= ($page - 1) * $pageSize;

        $sql    = "SELECT id,title,click_num,article_img,like_num,comment_num,created_at 
                  FROM {{%article}} 
                  WHERE $whereStr 
                  ORDER BY  id DESC 
                  LIMIT $pageMin,$pageSize";
        $rows   = Yii::$app->db->createCommand($sql)->queryAll();

        if(!empty($rows)){
            foreach ($rows as $key => $value) {
                $rows[$key]['article_img']  = Functions::get_image_path($value['article_img'],1,247,154);
                $rows[$key]['created_at']   = Tools::HtmlDate($value['created_at']);
                $rows[$key]['linkUrl']      = Yii::$app->params['mfrontendUrl'].'article/index?id='.$value['id'].'&isNew=0&from='.self::$data['from'];
                $rows[$key]['isGras']       = Functions::userIsGras($uid,$value['id'],2) ? 1 : 0;
            }
        }

        $data = ['status' => 1, 'msg' => $rows,'pageTotal' => $num, 'pageSize'=> $pageSize];
        return  json_encode($data);
    }
    /**
     * [articleInfo 文章详情]
     * @param  [int] $id [ID]
     * @param  [int] $user_id [用户ID]
     * @return [json] [列表数据]
     */
    PUBLIC function articleInfo() {
        //验证
        $requiredParameter  = array('id');
        $this->completeParameter($requiredParameter);
        //参数
        $id     = isset(self::$data['id']) ? intval(self::$data['id']) : '';
        $uid    = isset(self::$data['user_id']) ? intval(self::$data['user_id']) : '';
        $referer= strtolower(self::$data['from']) == 'ios' ? 'ios' : 'android';

        $sql    = "SELECT id,title,article_img,like_num,comment_num,created_at FROM {{%article}} WHERE status = '1' AND id = '$id'";
        $rows   = Yii::$app->db->createCommand($sql)->queryOne();

        if(!empty($rows)){
            $rows['article_img']  = Functions::get_image_path($rows['article_img']);
            $rows['created_at']   = Tools::HtmlDate($rows['created_at']);
            $rows['linkUrl']      = Yii::$app->params['mfrontendUrl'].'article/index?id='.$rows['id'].'&isNew=0&from='.self::$data['from'];
            $rows['isGras']       = Functions::userIsGras($uid,$rows['id'],2) ? 1 : 0;
        }else{
            return json_encode(['status' => -200, 'msg' => '未找到该文章']);
        }

        //排序
        $whereStr   = " C.type = '2' AND C.first_id = '0' AND C.post_id = '$id' AND C.status = '1' ";
        //文章点击数
        //Article::articleClick($id);

        /* 分页 */
        $num        = 0;
        $commentArr = [];
        $fieldStr   = 'C.like_num DESC ';
        $commentSql = "SELECT C.id
                    FROM {{%comment}} C 
                    WHERE $whereStr ORDER BY  C.is_digest DESC,C.like_num DESC , C.created_at DESC LIMIT 20";

        $commentList   = Yii::$app->db->createCommand($commentSql)->queryAll();

        if(!empty($commentList)){
            foreach ($commentList as $key => $value) {
                $commentArr[$key]   =  Functions::getCommentInfo($value['id'],$uid);
                $reply = Functions::getCommentReply($value['id']);
                if($reply)$commentArr[$key]['reply'] =  $reply;
                unset($reply);
            }
        }
        $rows['commentList']  = $commentArr;
        //支持活动统计
        Huodong::huodongCosmetics($id,'2',$uid,$referer);
        $data = ['status' => 1, 'msg' => $rows];
        return  json_encode($data);
    }
    /**
     * [productList 产品列表接口]
     * @param  [int] $isTop [是否明星产品]
     * @param  [int] $brandId [品牌ID]
     * @param  [int] $page [页数]
     * @param  [int] $pageSize [每页数]
     * @return [json]       [列表数据]
     */
    PUBLIC function productList() {
        //参数
        $search     = isset(self::$data['search']) ? Functions::checkStr(self::$data['search']) : '';
        $isTop      = isset(self::$data['is_top']) ? intval(self::$data['is_top']) : '';
        $brandId    = isset(self::$data['brand_id']) ? intval(self::$data['brand_id']) : '';
        $pageSize   = isset(self::$data['pageSize']) ? intval(self::$data['pageSize']) : 20;
        $page       = isset(self::$data['page']) ? intval(self::$data['page']) : 1;

        $idStr      = '';
        //先搜索字符
        if($search){
            $query          = new Query();
            $articleRows    = $query->select('id')->from('product')->match($search)->limit(1000)->all();
            if(!$articleRows) return json_encode(['status' => 1 ,'data' => [],'pageSize'=>$pageSize,'pageTotal'=>0,'search' => $search]);
            foreach ($articleRows as $key => $value) {
                $idArr[]    = $value['id'];
            }
            $idStr          = Functions::db_create_in($idArr,'P.id');
        }
        //分页判断
        $pageMin = ($page - 1) * $pageSize;
        $orderBy = "P.has_img AND P.price DESC ,P.is_recommend DESC,P.is_top DESC,has_img DESC,P.comment_num DESC,P.star DESC";

        $whereStr   = " P.status = '1' ";
        $whereStr  .= $isTop   ? " AND P.is_top  = '1' " : '';
        $whereStr  .= $brandId ? " AND P.brand_id= '$brandId' " : '';
        $whereStr  .= $idStr ? " AND $idStr " : '';
        /* 分页 */
        $rows   = array();
        $sql    = "SELECT COUNT(*) AS num FROM {{%product_details}} P WHERE $whereStr";
        $num    = Yii::$app->db->createCommand($sql)->queryScalar();

        $sql    = "SELECT P.id,B.id AS brand_id,B.name AS brand_name,B.rule,P.product_name,P.product_img,P.price,P.form,P.star,B.name AS brand_name
                  FROM {{%product_details}} P
                  LEFT JOIN {{%brand}} B ON P.brand_id = B.id
                  WHERE $whereStr 
                  ORDER BY  $orderBy 
                  LIMIT $pageMin,$pageSize";

        $rows   = Yii::$app->db->createCommand($sql)->queryAll();
        foreach ($rows as $key => $value) {
            $rows[$key]['product_img'] = Functions::get_image_path($value['product_img'],1);
        }

        $data = ['status' => 1, 'msg' => $rows,'pageTotal' => $num, 'pageSize'=> $pageSize,'search' => $search];
        return  json_encode($data);
    }
    /**
     * [componentInfo 成份详情]
     * @param  [int] $id [成份ID]
     * @return [json]       [列表数据]
     */
    PUBLIC function componentInfo(){
        //参数
        $id         = isset(self::$data['id']) ? intval(self::$data['id']) : '';
        $rows       = [];
        //查询成份详情
        $sql    = "SELECT id,name,ename,alias,component_action,is_pox,is_active,risk_grade,description
                FROM {{%product_component}} 
                WHERE id = '$id' ";
        $componentInfo   = Yii::$app->db->createCommand($sql)->queryOne();

        if (!$componentInfo) {
            return json_encode(['status' => -21, 'msg' => self::$ERROR[-21]]);
        }
        $data = ['status' => 1, 'msg' => $componentInfo];
        return  json_encode($data);
    }
    /**
     * [componentProductIist 成份产品列表]
     * @param  [int] $id [成份ID]
     * @param  [int] $page [页数]
     * @param  [int] $pageSize [每页数]
     * @return [json]       [列表数据]
     */
    PUBLIC function componentProductIist(){
        //参数
        $id         = isset(self::$data['id']) ? intval(self::$data['id']) : '';
        $pageSize   = isset(self::$data['pageSize']) ? intval(self::$data['pageSize']) : 20;
        $page       = isset(self::$data['page']) ? intval(self::$data['page']) : 1;

        $rows       = [];
        //查询成份详情
        $sql    = "SELECT id FROM {{%product_component}} WHERE id = '$id' ";
        $componentInfo   = Yii::$app->db->createCommand($sql)->queryOne();

        if (!$componentInfo) {
            return json_encode(['status' => -21, 'msg' => self::$ERROR[-21]]);
        }
        //查询产品
        $params = [
            'id'        =>  $id,
            'page'      =>  $page,
            'pageSize'  =>$pageSize,
        ];
        $productList = Skin::productComponentList($params);
        $num         = $productList['total'];
        $list        = $productList['data'];

        $data = ['status' => 1, 'msg' => $list,'pageTotal' => $num, 'pageSize'=> $pageSize];
        return  json_encode($data);
    }
    /**
     * [brandInfo 品牌详情接口]
     * @param  [int] id [品牌ID]
     * @return [json]   [列表数据]
     */
    PUBLIC function brandInfo() {
        $requiredParameter  = array('id');
        $this->completeParameter($requiredParameter);
        //参数
        $id     = intval(self::$data['id']);

        $sql    = "SELECT id,name,img,hot,description FROM {{%brand}} WHERE id = '$id'";
        $rows   = Yii::$app->db->createCommand($sql)->queryOne();
        if(!$rows) return json_encode(['status' => -19, 'msg' => self::$ERROR[-19]]);
        //先搜索字符
        $rows['relevantArticle'] = 0;
        $rows['img']= Functions::get_image_path($rows['img'],1);

        $query          = new Query();
        $articleRows    = $query->select('id')->from('article')->match($rows['name'])->limit(1)->all();

        $sql            = "SELECT id  FROM {{%article}} WHERE  find_in_set('$id',brand_id) ";
        $articleBrand   = Yii::$app->db->createCommand($sql)->queryAll();

        if($articleRows || $articleBrand) $rows['relevantArticle'] = 1;

        //其他品牌
        $sql        = "SELECT id,name,img FROM {{%brand}} WHERE id != '$id' ORDER BY hot DESC LIMIT 10";
        $brandArr   = Yii::$app->db->createCommand($sql)->queryAll();
        foreach ($brandArr as $key => $value) {
            $brandArr[$key]['img'] = Functions::get_image_path($value['img'],1);
        }

        //判断品牌下是否有明星产品
        $rows['is_top'] = 0;
        $productSql = "SELECT id FROM {{%product_details}} WHERE brand_id = {$id} AND is_top = 1 AND status = 1";
        $productArr   = Yii::$app->db->createCommand($productSql)->queryOne();
        if($productArr){
            $rows['is_top'] = 1;
        }

        $data   = ['brandInfo' => $rows,'otherBrand' => $brandArr];
        $return = ['status' => 1, 'msg' => $data];
        return  json_encode($return);
    }
    /**
     * [productInfo 产品详情接口]
     * @param  [int] $page [页数]
     * @param  [int] $pageSize [每页数]
     * @return [json]       [列表数据]
     */
    PUBLIC function productInfo() {
        $requiredParameter  = array('id');
        $this->completeParameter($requiredParameter);

        //参数
        $id     = intval(self::$data['id']);
        $uid    = isset(self::$data['user_id']) ? intval(self::$data['user_id']) : '';
        $rows   = Skin::componentStatistics($id,$uid);
        if(!$rows) return json_encode(['status' => -8, 'msg' => self::$ERROR[-8]]);

        $ProductParentColumn = Functions::getProductParentColumn($rows['cate_id']);
        $cateIsHide = Functions::cateIsHide($ProductParentColumn);
        if($cateIsHide){
            $rows['effect'] = [];
        }

        //登录状态下，是否长草
        $isGras = 0 ;                        
        if($uid){
            $isGras     = Functions::userIsGras($uid,$id,1) ? 1 : 0;
        }
        $rows['isGras'] = $isGras;
        $rows['linkUrl']= Yii::$app->params['mfrontendUrl'].'product/details?id='.$id;
        //三个平台下链接
        $rows['buy']    = [
            'taobao' => 'https://ai.taobao.com/search/index.htm?key='.$rows['product_name'].'&pid=mm_124287267_25890794_99532920',
            'jd'     => 'http://api.yjyapp.com/oauth/get-jd-url?u=https://so.m.jd.com/ware/search.action?keyword='.$rows['product_name'],
            'amazon' => 'https://www.amazon.cn/gp/search?ie=UTF8&camp=536&creative=3200&index=aps&keywords='.$rows['product_name'].'&linkCode=ur2&tag=865230-23',
        ];

        $extend_sql     = "SELECT type,url,link_price,tb_goods_id FROM {{%product_link}} WHERE product_id = $id";
        $extend_info    = Yii::$app->db->createCommand($extend_sql)->queryAll();
        $rows['link_buy'] = [];
        $type_linkbuy = ['1'=>'link_tb','2'=>'link_jd'];
        if($extend_info){
            foreach ($extend_info as $key => $value) {
                $LinkPrice = Functions::getLinkPrice($value['tb_goods_id'],$value['type']);
                $value['link_price'] = isset($LinkPrice[$value['tb_goods_id']]) ? $LinkPrice[$value['tb_goods_id']] : '';
                $value['brand_name'] = $rows['brand'];
                $value['brand_img'] = $rows['brand_img'];
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
                    if(!empty($rows[$link_type])){
                        $value['url'] = $rows[$link_type];
                        $rows['link_buy'][] = $value;
                        unset($rows[$link_type]);
                        unset($type_linkbuy[$value['type']]);
                    } 
                }else{
                    $rows['link_buy'][] = $value;
                    unset($type_linkbuy[$value['type']]);
                }
            }
        }
        if($type_linkbuy){
            $res_arr['brand_name'] = $rows['brand'];
            $res_arr['brand_img'] = $rows['brand_img'];
            $res_arr['link_price'] = '';
            foreach ($type_linkbuy as $key => $value) {
                if(!empty($rows[$value])){
                    $res_arr['type'] = "$key";
                    $res_arr['url'] = $rows[$value];
                    $rows['link_buy'][] = $res_arr;
                }
            }
        }
        $data = ['status' => 1, 'msg' => $rows];
        return  json_encode($data);
    }
    /**
     * [commentList 评论列表]
     * @param  [int] $id    [关联ID]
     * @param  [int] $type  [1为产品，2为文章]
     * @param  [int] $page  [页数]
     * @param  [int] $pageSize [每页数]
     * @param  [int] $desc  [排序方式]
     * @return [json]       [列表数据]
     */
    PUBLIC function commentList() {
        $requiredParameter  = array('id','type');
        $this->completeParameter($requiredParameter);

        //参数
        $id         = intval(self::$data['id']);
        $uid        = isset(self::$data['user_id'])     ?   intval(self::$data['user_id']) : '';
        $type       = self::$data['type'] == 1          ?   1 : 2;
        $orderBy    = isset(self::$data['orderBy']) &&  self::$data['orderBy'] == 'skin' ?  'skin' :  'default';
        $condition  = isset(self::$data['condition'])   ?   Functions::checkStr(self::$data['condition']) : '';
        $pageSize   = isset(self::$data['pageSize'])    ?   intval(self::$data['pageSize']) : 10;
        $page       = isset(self::$data['page'])        ?   intval(self::$data['page']) : 1;
        $pageMin    = ($page - 1) * $pageSize;

        //排序
        $whereStr   = " C.type = '$type' AND C.first_id = '0' AND C.post_id = '$id' AND C.status = '1' ";
        $rows       = [];
        if($condition){
            switch (self::$data['condition']) {
                case 'Praise':
                    $whereStr .= " AND C.star >= 4"; 
                    break; 
                case 'middle':
                    $whereStr .= " AND C.star =  3"; 
                    break; 
                case 'bad':
                    $whereStr .= " AND C.star <= 2"; 
                    break;
            }         
        }

        /* 分页 */
        $num        = 0;
        $fieldStr   = 'C.is_digest DESC,C.like_num DESC ';
        $sql        = "SELECT COUNT(*) AS num  FROM {{%comment}}  C  WHERE $whereStr";
        $num        = Yii::$app->db->createCommand($sql)->queryScalar();

        if($orderBy == 'skin'){
            $userSkin   = Skin::getUserSkin($uid);
            $skin_name  = $userSkin['skin_name'];
            $fieldStr   = $skin_name ? " if(S.skin_name ='$skin_name',0,1)" : $fieldStr;
        }

        $sql    = "SELECT C.id,C.user_id,C.comment,C.like_num,C.is_digest,S.dry,S.tolerance,S.pigment,S.compact FROM {{%comment}} C 
                   LEFT JOIN {{%user_skin}} S ON C.user_id = S.uid
                   WHERE $whereStr ORDER BY  $fieldStr ,C.created_at DESC LIMIT $pageMin,$pageSize";
        $commentList   = Yii::$app->db->createCommand($sql)->queryAll();

        if(!empty($commentList)){
            foreach ($commentList as $key => $value) {
                $commentList[$key]['comment'] = Tools::userTextDecode($value['comment']);
                $rows[$key]    = Functions::getCommentInfo($value['id'],$uid);
                $reply         = Functions::getCommentReply($value['id']);
                if($reply) $rows[$key]['reply'] =  $reply;
                unset($reply);
            }
        }

        $data = ['status' => 1, 'msg' => $rows,'pageTotal' => $num, 'pageSize'=> $pageSize];
        return  json_encode($data);
    }
    /**
     * [commentInfo 评论详情]
     * @return [type] [description]
     */
    PUBLIC function commentInfo() {
        $requiredParameter  = array('id');
        $this->completeParameter($requiredParameter);

        //参数
        $id         = intval(self::$data['id']);
        $uid        = isset(self::$data['user_id'])     ?   intval(self::$data['user_id']) : '';
        $page       = isset(self::$data['page'])        ?   intval(self::$data['page']) : 1;
        $pageSize   = isset(self::$data['pageSize'])    ?   intval(self::$data['pageSize']) : 10;
        $rows       = [];

        $commentInfo= Functions::getCommentInfo($id,$uid);

        if(!$commentInfo){
            $return =  ['status' => -15, 'msg' => self::$ERROR['-15']];
            echo json_encode($return);die;  
        }

        //获取评论对应的产品或者文章
        if($commentInfo['type'] == 1){
            $productSql = "SELECT product_img,product_name FROM {{%product_details}} WHERE id = {$commentInfo['post_id']}";
            $product = Yii::$app->db->createCommand($productSql)->queryOne();
            $commentInfo['product_img'] = $product ? Functions::get_image_path($product['product_img'],1) : '';
            $commentInfo['title'] = $product ? $product['product_name'] : '';
        }else{
            $articleSql = "SELECT title FROM {{%article}} WHERE id = {$commentInfo['post_id']}";
            $title = Yii::$app->db->createCommand($articleSql)->queryScalar();
            $commentInfo['title'] = $title ? $title : '';
        }

        //获取回答评论列表
        $params     = [
            'id'    => $id,
            'uid'   => $uid,
            'page'  => $page,
            'pageSize'=> $pageSize
        ];
        $replyList  = Functions::commentReplyList($params);

        $rows       = $commentInfo;
        $rows['replyList'] = $replyList['data'];
        $num        = $replyList['total'];
        
        $data = ['status' => 1, 'msg' => $rows,'pageTotal' => $num, 'pageSize'=> $pageSize];
        return  json_encode($data);
    }
    /**
     * [commentInfo 评论详情]
     * @return [type] [description]
     */
    PUBLIC function commentReplyList() {
        $requiredParameter  = array('id');
        $this->completeParameter($requiredParameter);

        //参数
        $id         = intval(self::$data['id']);
        $uid        = isset(self::$data['user_id'])     ?   intval(self::$data['user_id']) : '';
        $page       = isset(self::$data['page'])        ?   intval(self::$data['page']) : 1;
        $pageSize   = isset(self::$data['pageSize'])    ?   intval(self::$data['pageSize']) : 10;
        $rows       = [];

        $sql    = "SELECT id FROM {{%comment}}  WHERE id = '$id'";
        $commentInfo   =    Yii::$app->db->createCommand($sql)->queryScalar();

        if(!$commentInfo){
            $return =  ['status' => -15, 'msg' => self::$ERROR['-15']];
            echo json_encode($return);die;  
        }

        $params     = [
            'id'    => $id,
            'uid'   => $uid,
            'page'  => $page,
            'pageSize'=> $pageSize
        ];
        $replyList  = Functions::commentReplyList($params);

        $rows    = $commentInfo;
        $rows    = $replyList['data'];
        $num     = $replyList['total'];
        
        $data = ['status' => 1, 'msg' => $rows,'pageTotal' => $num, 'pageSize'=> $pageSize];
        return  json_encode($data);
    }
    /**
    * 评论点赞功能接口
    * @para string $sign      校验码
    * @para int    $time      时间
    * @para int    $commentId 评论ID
    * @para int    $userId    用户ID
    * @return json 成功返回信息，失败返回错误
    */
    PUBLIC function addCommentLike()
    {
        //验证
        $requiredParameter  = array('commentId','user_id');
        $this->completeParameter($requiredParameter);

        //查询
        $time       = time();
        $userId     = intval(self::$data['user_id']);
        $commentId  = intval(self::$data['commentId']);
        $referer    = strtolower(self::$data['from']) == 'ios' ? 'ios' : 'android';
        //判断帐号是否正常
        $this->checkUserStatus($userId);

        //判断帖子或评论是否存在
        $infoSql     = "SELECT user_id,type,post_id FROM {{%comment}} WHERE id = '$commentId'";
        $commentInfo = Yii::$app->db->createCommand($infoSql)->queryOne();

        if(!$commentInfo){
            $return =  ['status' => -15, 'msg' => self::$ERROR['-15']];
            echo json_encode($return);die;
        }
        //判断是否已点过赞
        $selectSql   = "SELECT id FROM {{%comment_like}} WHERE user_id = '$userId' AND comment_id = '$commentId'";
        $isExsit     = Yii::$app->db->createCommand($selectSql)->queryScalar();

        if($isExsit){
            $sql    = "DELETE  FROM {{%comment_like}} WHERE id = '$isExsit'"; 
            $rows   = Yii::$app->db->createCommand($sql)->execute();
        }else{

            $sql    = "INSERT INTO {{%comment_like}} (user_id,type,post_id,comment_id,referer,created_at,updated_at) 
                        VALUES('$userId','$commentInfo[type]','$commentInfo[post_id]','$commentId','$referer','$time','$time')";
            $rows   = Yii::$app->db->createCommand($sql)->execute();
            if($userId != $commentInfo['user_id']) {
                //点赞消息
                ReplyFunctions::reply($userId,$commentId,ReplyFunctions::$REPLY_LIKE_COMMENT);
            }
        }
        
        //修改点赞数
        $updateSql  = "UPDATE {{%comment}} C SET C.like_num = (SELECT COUNT(*)  FROM {{%comment_like}}  WHERE  comment_id = '$commentId') WHERE C.id = '$commentId'";

        Yii::$app->db->createCommand($updateSql)->execute();

        //更新点赞数 
        if($commentInfo['type'] == 2){
            $postId     = $commentInfo['post_id'];
            $updateSql  = " UPDATE {{%article}} P SET P.like_num = (SELECT SUM(like_num)  
                        FROM {{%comment}}  WHERE type = '2' AND post_id = '$postId')  WHERE P.id = '$postId'";

            Yii::$app->db->createCommand($updateSql)->execute();  
        }


        if($rows){
            $data = ['status' => 1, 'msg' => self::$ERROR['1']];
        }else{
            $data = ['status' => -200, 'msg' => self::$ERROR['0']];
        }
        
        return  json_encode($data);
    }
    /**
    * 评论接口
    * @para int     $userId    用户ID
    * @para int     $type      1-产品，2-文章
    * @para int     $id        对应的ID
    * @para string  $comment   评论内容
    * @return json 操作成功，失败返回错误
    */
    PUBLIC function addComment(){
        //验证
        $requiredParameter  = array('type','id','comment');
        $this->completeParameter($requiredParameter);

        $id       = intval(self::$data['id']);
        $userId   = intval(self::$data['user_id']);
        $type     = intval(self::$data['type']) == 1 ? 1 : 2;
        $star     = isset(self::$data['star']) ? intval(self::$data['star']) : '0';
        $parentId = isset(self::$data['parent_id']) ? intval(self::$data['parent_id']) : '0';
        $comment  = Functions::checkStr(self::$data['comment']);
        $comment  = Tools::userTextEncode($comment,1);
        $attachment = isset(self::$data['attachment']) ? Functions::checkStr(self::$data['attachment']) : '';
        $referer  = strtolower(self::$data['from']) == 'ios' ? 'ios' : 'android';
        $time     = time();
        $firstId  = 0;

        switch ($type) {
            case '1':
                $tableName = 'product_details';
                $errorNo   = -8;
                break;
            default:
                $tableName = 'article';
                $errorNo   = -16;
                break;
        }

        //判断帐号是否正常
        $this->checkUserStatus($userId);
        //查询帖子是否存在
        $postSql    = "SELECT id FROM {{%$tableName}} WHERE id = '$id' AND status = '1'"; 
        $postInfo   = Yii::$app->db->createCommand($postSql)->queryScalar(); 

        if(!$postInfo){
            $data = ['status' => $errorNo, 'msg' => self::$ERROR[$errorNo]];
            return  json_encode($data);
        }
        //查询上级评论是否存在
        if($parentId){
            $commentSql    = "SELECT id,first_id FROM {{%comment}} WHERE id = '$parentId' AND status = '1'"; 
            $commentInfo   = Yii::$app->db->createCommand($commentSql)->queryOne();
            if($commentInfo){
                $firstId   = $commentInfo['first_id'] ? $commentInfo['first_id'] : $commentInfo['id']; 
            }else{
                $data = ['status' => -15, 'msg' => self::$ERROR[-15]];
                return  json_encode($data);
            }
        }

        $author   = self::$userInfo['username']; 
        $adminId  = self::$userInfo['admin_id'];

        //入库
        $sql      = "INSERT INTO {{%comment}} (type,first_id,parent_id,user_id,post_id,admin_id,author,star,comment,referer,created_at,updated_at) 
                     VALUES ('$type','$firstId','$parentId','$userId','$id','$adminId,','$author','$star','$comment','$referer','$time','$time')"; 

        $return   = Yii::$app->db->createCommand($sql)->execute();
        $newcommentId =  Yii::$app->db->getLastInsertId();

        if($parentId && $newcommentId){
            //二级评论消息
            ReplyFunctions::reply($userId,$parentId,ReplyFunctions::$REPLY_OTHER_COMMENT,$comment);
        }

        if($return){
            $addMoney  = 0;
            $content = '';
            //更新评论数
            switch ($type) {
                case '1':
                    $tableName  = '{{%product_details}}';
                    $isAddMoney = $parentId ? 0 : Functions::updateMoney($userId,10,'点评产品',2);
                    $addMoney   = $isAddMoney ?  1 : 0 ;
                    $content = $addMoney ==1 ? '点评成功,颜值+10分' : '';
                    break;
                case '2':
                    $tableName = '{{%article}}';
                    break;
            }
            $attachmentPath =   '';
            //入库附件
            if($attachment){
                $attachmentSql  = "INSERT INTO {{%attachment}} (cid,uid,attachment,dateline) VALUES ('$newcommentId','$userId','$attachment','$time')"; 
                Yii::$app->db->createCommand($attachmentSql)->execute();
                $attachmentPath =  Functions::get_image_path($attachment);
                Functions::uploadOssimg($attachmentPath);
            }
            $updateSql  = "UPDATE $tableName P SET P.comment_num = (SELECT COUNT(*)  FROM {{%comment}} C WHERE type = '$type' AND first_id = '0' AND post_id = '$id' AND C.status = 1)  WHERE P.id = '$id'";

            Yii::$app->db->createCommand($updateSql)->execute();
            //判断是否是活动
            Huodong::checkCosmetics($id,$type,$userId,$newcommentId);
            //返回图片地址，失了智了
            $data = ['status' => 1, 'msg' => ['commentId' => $newcommentId,'attachment' => $attachmentPath,'flag' => $addMoney,'content'=>$content]];
        }else{
            $data = ['status' => 0, 'msg' => self::$ERROR['0']];
        }
        
        return  json_encode($data);
    }
    /**
    * 批号品牌列表接口
    * @return json 操作成功，失败返回错误
    */
    PUBLIC function brandList(){
        //品牌列表
        $from           = strtolower(self::$data['from']) == 'android' ? 'android' : 'ios';
        $model          = isset(self::$data['model']) ? intval(self::$data['model']) : 0;
        $action         = self::$data['from'].'CosmeticsList_v3';

        $cosmeticsList  = Cosmetics::$action($model);
        $data = ['status' => 1,'data' => $cosmeticsList];

        return  json_encode($data);
    }
    /**
    * 批号品牌列表接口
    * @para int     $id         品牌ID
    * @para string  $number     批号
    * @return json 操作成功，失败返回错误
    */
    PUBLIC function queryCosmetics(){
        //验证
        $requiredParameter  = array('id','number');
        $this->completeParameter($requiredParameter);

        $id       = intval(self::$data['id']);
        $number   = strtoupper(trim(self::$data['number']));
        $number   = str_replace(' ', '', $number);

        $sql      = "SELECT rule FROM {{%brand}} WHERE id = '$id'";
        $rule     = Yii::$app->db->createCommand($sql)->queryScalar();

        if(!$rule){
            $return = ['status' => -200, 'msg' => '品牌规则不存在'];
            return  json_encode($return);
        }

        $fun    = 'rule'.$rule;
        $data   = Cosmetics::$fun($number);

        $sql = "UPDATE {{%cosmetics_tool}} SET num = num + 1 WHERE id = '$id'";
        Yii::$app->db->createCommand($sql)->execute();

        if($data && $data['status'] == 1){
            $data['msg'] = ['startDay' => $data['startDay'], 'endDay'=> $data['endDay']];
            unset($data['startDay'],$data['endDay']);
        }else{
            $data = ['status' => -200, 'msg' => $data['msg']];
        }

        return  json_encode($data);
    }
    /**
    * 产品保质期提醒接口
    * @para int     $id         品牌ID
    * @para string  $number     批号
    * @return json 操作成功，失败返回错误
    */
    PUBLIC function addRemind(){
        //验证
        $requiredParameter  = array('product','seal_time','quality_time','overdue_time');
        $this->completeParameter($requiredParameter);

        $userId      = intval(self::$data['user_id']);
        $brandId     = intval(self::$data['brand_id']);
        $brandName   = isset(self::$data['brand_name']) ? Functions::checkStr(self::$data['brand_name'])    : '';
        $product     = isset(self::$data['product'])    ? Functions::checkStr(self::$data['product'])      : '';
        $productImg  = isset(self::$data['product_img'])? Functions::checkStr(self::$data['product_img'])   : '';
        $isSeal      = isset(self::$data['is_seal']) ? intval(self::$data['is_seal']) : 0 ;
        $sealTime    = intval(self::$data['seal_time']);
        $qualityTime = intval(self::$data['quality_time']);
        $overdueTime = intval(self::$data['overdue_time']);

        $time           = time();
        $newProductImg  = '';
        if($productImg){
           $position        = strpos($productImg,"user_product"); 
           $position        = strpos($productImg,"product_img");
           $newProductImg   = substr($productImg,$position);
        }
        $expire_time = $isSeal ? strtotime(date('Y-m-d',strtotime("+$qualityTime month",$sealTime))) : $overdueTime;
        $expire_time = $expire_time > $overdueTime ? $overdueTime : $expire_time;
        //入库
        $sql        = "INSERT INTO {{%user_product}} (user_id,brand_id,brand_name,product,img,is_seal,seal_time,quality_time,overdue_time,expire_time,add_time) 
                     VALUES ('$userId','$brandId','$brandName','$product','$newProductImg','$isSeal','$sealTime','$qualityTime','$overdueTime','$expire_time','$time')"; 

        $return     = Yii::$app->db->createCommand($sql)->execute();

        if($return){
            $data = ['status' => 1, 'msg' => self::$ERROR['1']];
        }else{
            $data = ['status' => 0, 'msg' => self::$ERROR['0']];
        }
        
        return  json_encode($data);
    }
    /**
    * 用户资料接口
    * @para int     $user_id    用户ID
    * @return json 返回用户信息
    */
    PUBLIC function userInfo(){
        $userId     = intval(self::$data['user_id']);

        //查询
        $sql        = "SELECT U.id,U.username,U.mobile,U.email,U.img,U.img_state,U.sex,U.birth_year,U.birth_month,U.birth_day,U.province,U.city,U.status,U.rank_points,S.dry,S.tolerance,S.pigment,S.compact,S.add_time
                       FROM {{%user}} U LEFT JOIN {{%user_skin}} S ON U.id = S.uid WHERE U.id = '$userId'"; 

        $return     = Yii::$app->db->createCommand($sql)->queryOne();

        if($return){
            $return['img']      = Functions::get_image_path($return['img'],1);
            $return['username'] = Tools::userTextDecode($return['username']);
            $return['birth_year']   = $return['birth_year'] ? $return['birth_year'] : '';
            $return['birth_month']  = $return['birth_month'] ? $return['birth_month'] : '';
            $return['birth_day']    = $return['birth_day'] ? $return['birth_day'] : '';
            $skinInfo = [
                'dry'       => $return['dry'],
                'tolerance' => $return['tolerance'],
                'pigment'   => $return['pigment'],
                'compact'   => $return['compact']
            ];

            $userSkin   = Skin::evaluateSkin($skinInfo);
            //完全测试完成才显示
            if($userSkin){
                unset($userSkin['skin_name']);
                $return['skin_name'] = join(" | ",$userSkin);
            }else{
                $return['skin_name'] = '';
            }

            $return['token'] = self::$data['token'];

            $data = ['status' => 1, 'msg' => $return];
        }else{
            $data = ['status' => -2, 'msg' => self::$ERROR['-2']];
        }
        return  json_encode($data);
    }
    /**
    * 用户反馈接口
    * @para int     $user_id    用户ID
    * @return json 返回用户信息
    */
    PUBLIC function userFeedback(){
        //验证
        $requiredParameter  = array('content');
        $this->completeParameter($requiredParameter);

        $userId      = intval(self::$data['user_id']);
        $source      = self::$data['from'] == 'android' ? '1' : '2';
        //$username    = Functions::checkStr(self::$data['username']);
        $content     = Tools::userTextEncode(Functions::checkStr(self::$data['content']),1) ;
        $telphone    = isset(self::$data['telphone']) ? Functions::checkStr(self::$data['telphone']) : '';
        $model       = isset(self::$data['model']) ? Functions::checkStr(self::$data['model']) : '';
        $system      = isset(self::$data['system']) ? Functions::checkStr(self::$data['system']) : '';
        $number      = isset(self::$data['number']) ? Functions::checkStr(self::$data['number']) : '1.1.0';
        $attachment  = isset(self::$data['attachment']) ? Functions::checkStr(self::$data['attachment']) : '';

        $time  = time();
        $isTel = Functions::isMoblie($telphone);

        if($telphone && !$isTel){
            $data = ['status' => -14, 'msg' => self::$ERROR['-14']];
            return json_encode($data);   
        }
        $userInfo = Functions::getUserInfo($userId);
        if(!$userInfo){
            $data = ['status' => -2, 'msg' => self::$ERROR['-2']];
            return json_encode($data);  
        }
        $username = Tools::userTextEncode($userInfo['username'],1);
        //入库
        $sql    = "INSERT INTO {{%user_feedback}} (`user_id`, `source`, `username`,`content`,`telphone`,`number`,`model`,`system`,`attachment`,`created_at`) 
                      VALUES ('$userId', '$source','$username','$content','$telphone','$number','$model','$system','$attachment','$time')";

        $return     = Yii::$app->db->createCommand($sql)->execute();

        if($attachment){
            $attachmentPath =  Functions::get_image_path($attachment);
            Functions::uploadOssimg($attachmentPath); 
        }
        if($return){
            $data = ['status' => 1, 'msg' => self::$ERROR['1']];
        }else{
            $data = ['status' => 0, 'msg' => self::$ERROR['0']];
        }
        
        return  json_encode($data);
    }
    /**
     * 版本更新接口
     * @para int    $type      1为android，2为ios
     * @para string $sign      校验码
     * @para int    $time      时间
     * @return json 更新返更新版本信息，不更新失败返回操作成功，失败返回错误信息
     */
    PUBLIC function versionUp()
    {
        $type = self::$data['from'] == 'android' ? '1' : '2';

        //入库
        $sql    = "SELECT id,type,content,number,downloadUrl,isMust FROM {{%app_version}}  WHERE type = '$type' AND status = 1 ORDER BY create_time DESC"; 

        $info   = Yii::$app->db->createCommand($sql)->queryOne(); 

        if ($info) {
            $content    = [];
            $rule       = '/<p.*?>(.*?)<\/p.*?>/';
            preg_match_all($rule,$info['content'],$result); 
            if($result){
                foreach ($result['1'] as $key => $value) {
                    $tag            = strip_tags($value);
                    if($tag){
                        $content[]  =  $tag;
                    }
                    unset($tag);
                }
            }
            $info['content'] = $content;
            $info['downloadUrl'] = Yii::$app->params['frontendUrl'].$info['downloadUrl'];

            $data = ['status' => '1', 'msg' => $info];
        } else {
            $data = ['status' => '0', 'msg' => self::$ERROR['0']];
        }
        return  json_encode($data);
    
    }
    /**
     * 微信登陆后绑定手机
     * @para int    $user_id   用户ID
     * @para string $mobile    手机
     * @para int    $captcha   校验码
     * @para int    $time      时间
     * @return json 更新返更新版本信息，不更新失败返回操作成功，失败返回错误信息
     */
    PUBLIC function mobileBind()
    {
        //验证
        $requiredParameter  = array('mobile','captcha');
        $this->completeParameter($requiredParameter);

        $userId    = intval(self::$data['user_id']);
        $captcha   = intval(self::$data['captcha']);
        $mobile    = Functions::checkStr(self::$data['mobile']);
 
        //判断帐号是否正常
        $this->checkUserStatus($userId);

        //验证验证码
        $isCaptcha   = Functions::checkCaptcha($mobile,$captcha,4);

        if(!$isCaptcha) {
            $data = ['status' => '-12', 'msg' => self::$ERROR['-12']];
            return  json_encode($data);
        }
        //验证手机号
        $hasMobile   = Functions::checkMobile($mobile,$userId);

        if($hasMobile) {
            $data = ['status' => '0', 'msg' => self::$ERROR['0']];
            return  json_encode($data);
        }

        $sql    =   "UPDATE {{%user}} SET mobile = '$mobile' WHERE id = '$userId'";
        $return =   Yii::$app->db->createCommand($sql)->execute();

        if ($return) {
            //修改验证码状态
            Functions::useCaptcha($mobile,$captcha,4);
            $data = ['status' => '1', 'msg' => self::$ERROR['1']];
        } else {
            $data = ['status' => '0', 'msg' => self::$ERROR['0']];
        }

        return  json_encode($data);
    }

    /**
     * 用户手机绑定接口
     * @para int    $user_id   用户ID
     * @para string $mobile    手机
     * @para int    $captcha   校验码
     * @para int    $time      时间
     * @return json 更新返更新版本信息，不更新失败返回操作成功，失败返回错误信息
     */
    PUBLIC function mobileIsBind()
    {
        //验证
        $requiredParameter  = array('mobile','captcha');
        $this->completeParameter($requiredParameter);

        $userId    = intval(self::$data['user_id']);
        $captcha   = intval(self::$data['captcha']);
        $mobile    = Functions::checkStr(self::$data['mobile']);
 
        //判断帐号是否正常
        $this->checkUserStatus($userId);

        //验证验证码
        $isCaptcha   = Functions::checkCaptcha($mobile,$captcha,4);

        if(!$isCaptcha) {
            $data = ['status' => '-12', 'msg' => self::$ERROR['-12']];
            return  json_encode($data);
        }

        //验证手机号
        $hasMobile   = Functions::checkMobile($mobile,$userId);

        if($hasMobile) {
            $data = ['status' => '-22', 'msg' => self::$ERROR['-22']];
            return  json_encode($data);
        }

        $data = ['status' => '1', 'msg' => self::$ERROR['1']];
        return  json_encode($data);
    }
    /**
    *  用户长草
    *  @para int      $cate_id    分类id
    *  @para string   $effect     功效关键字
    *  @para int      $pageSize   每页页数
    *  @para int      $page       页数
    * 
    * @return json 成功返回信息，失败返回错误
    */
    PUBLIC function userGrass(){

        $userId    = isset(self::$data['user_id'])  ? intval(self::$data['user_id']) : 0 ;
        $cateId    = isset(self::$data['cate_id'])  ? intval(self::$data['cate_id']) : 0 ;
        $effect    = isset(self::$data['effect'])   ? intval(self::$data['effect']) :  ''  ;
        $pageSize  = isset(self::$data['pageSize']) ? intval(self::$data['pageSize']) : 20;
        $page      = isset(self::$data['page'])     ? intval(self::$data['page']) : 1;
        $pageMin   = ($page - 1) * $pageSize;
        /*查询条件*/
        $result     = ['data' => [] ,'categroy' => [], 'effects'=> []];
        $whereStr   = "C.type = '1' AND C.user_id = '$userId'";

        $whereStr  .= $cateId ? " AND D.cate_id = '$cateId'" : '' ;
        $whereStr  .= $effect ? ' AND FIND_IN_SET(\''.$effect.'\',D.effect_id) ' : '';

        /*分页*/
        $sql        = " SELECT COUNT(*) AS num FROM {{%user_collect}} C  
                        LEFT JOIN {{%product_details}} D ON C.relation_id = D.id
                        WHERE $whereStr";

        $num        = Yii::$app->db->createCommand($sql)->queryScalar();

        
        $selectSql  = " SELECT D.id,D.product_name,D.product_img FROM {{%user_collect}} C  
                        LEFT JOIN {{%product_details}} D ON C.relation_id = D.id
                        WHERE $whereStr ORDER BY C.add_time DESC  LIMIT $pageMin,$pageSize";

        $return['data']   = Yii::$app->db->createCommand($selectSql)->queryAll();
        
        foreach ($return['data'] as $key => $value) {
            $return['data'][$key]['product_img'] = Functions::get_image_path($value['product_img'],1);
        }
        //分类列表
        $return['categroy']   = Functions::getProductColumn();
        //$return['effects']    = Functions::effectList();

        //判断分类除了面部护肤及以下二级分类外都没有功效的数据
        if($cateId){
            $sql = "SELECT cate_name,parent_id FROM {{%product_category}} WHERE id = {$cateId}";
            $category = Yii::$app->db->createCommand($sql)->queryOne();
            if($category['parent_id'] == 0){
                $return['effects'] = $category['cate_name'] == '面部护肤' ? Functions::effectList() : [];
            }else{
                $secondSql = "SELECT cate_name FROM {{%product_category}} WHERE id = {$category['parent_id']}";
                $cateName = Yii::$app->db->createCommand($secondSql)->queryScalar();
                $return['effects'] = $cateName == '面部护肤' ? Functions::effectList() : [];
            }
        }else{
            $return['effects'] = Functions::effectList();
        }

        $data = ['status' => 1, 'msg' => $return,'pageTotal' => $num, 'pageSize'=> $pageSize];
        return  json_encode($data);
    }
    /**
    *  用户肤质
    *  @para int    $user_id    用户ID
    * 
    * @return json 成功返回信息，失败返回错误
    */
    PUBLIC function userSkin(){
        $userId   = intval(self::$data['user_id']);

        $userSkin = skin::getUserTestSkin($userId);
        $info     = skin::getUserSkin($userId);

        $return['desc'] = $userSkin;
        if($info){
            $skinList = skin::getSkin();
            $skinInfo = $skinList[$info['skin_name']];
            $return['explain']  = $skinInfo['explain'];
            $return['features'] = $skinInfo['features'];
            $return['elements'] = $skinInfo['elements'];
            $return['star']     = $skinInfo['star'];
            $return['skin_time']= $info['add_time'];
            $return['baike'] = Functions::getbaikeList($skinInfo['skin_id']);
        }
        if($return){
            $data = ['status' => 1, 'msg' =>$return];
        }else{
            $data = ['status' => -1, 'msg' =>self::$ERROR['0']];
        }
        return  json_encode($data);
    }
    /**
    *  用户肤质测试提交
    *  @para int    $user_id    用户ID
    *  @para int    $type       测试类型
    *  @para int    $value      测试值 
    * 
    * @return json 成功返回信息，失败返回错误
    */
    PUBLIC function saveSkin(){
        //验证
        $requiredParameter  = array('type','value');
        $this->completeParameter($requiredParameter);

        $skinArr   = ['dry','tolerance','pigment','compact'];

        $isType    = in_array(self::$data['type'],$skinArr); 
        if(!$isType) return json_encode(['status' => -1, 'msg' =>self::$ERROR['0']]);
        $skin_type = skin::$skinConfig[self::$data['type']];
        foreach ($skin_type as $key => $value) {
            if($value['name'] == self::$data['value']){
                self::$data['value'] = rand($value['min'],$value['max']);
            }
        }

        $userId    = intval(self::$data['user_id']);
        $type      = self::$data['type'];
        $value     = isset(self::$data['value'])  ? intval(self::$data['value']) : 0 ;
        $referer   = strtolower(self::$data['from']) == 'ios' ? 'ios' : 'android';

        $return    = skin::saveSkin($userId,$type,$value);
        //参与日志
        Functions::skinLog($userId,$type,$referer);
        //保存成功，推荐
        if($return['status'] == 1){
            //添加测试积分
            $isComplete = 0;
            $addMoney   = 0;
            $info       = skin::getUserSkin($userId);
            if($info && $info['skin_name']){
                $isComplete = 1;
                $isAddMoney = Functions::updateMoney($userId,100,'肤质测试',2);
                $addMoney   = $isAddMoney ? 1 : 0;
            }
            $data  = ['status' => 1, 'msg' =>['isComplete' => $isComplete,'addMoney' => $addMoney]];
        }else{
             $data = ['status' => -1, 'msg' =>self::$ERROR['0']];
        }
        return  json_encode($data);
    }
    /**
    *  功课生成记录
    *  @para int    $user_id    用户ID
    * 
    * @return json 成功返回信息，失败返回错误
    */
    PUBLIC function addLessons(){
        $userId    = intval(self::$data['user_id']);
        $referer   = strtolower(self::$data['from']) == 'ios' ? 'ios' : 'android';
        //参与日志
        Functions::lessonsLog($userId,$referer);

        $data  = ['status' => 1, 'msg' =>self::$ERROR['1']];
        return  json_encode($data);
    }
    /**
     * 保存分享数接口
     * @para int    $type      类型，1为产品，2为文章
     * @para int    $id        ID
     * @para int    $userId   用户ID（ 未登录userId为0）
     * @return json 成功返回信息，失败返回错误
     */
    PUBLIC function addShare()
    {
        //验证
        $requiredParameter  = array('type','id');
        $this->completeParameter($requiredParameter);

        $userId    = intval(self::$data['user_id']);
        $type      = intval(self::$data['type']);
        $id        = intval(self::$data['id']);
        $referer   = strtolower(self::$data['from']) == 'ios' ? 'ios' : 'android';

        //参与日志
        Functions::shareLog($userId,$type,$id,$referer);

        $data  = ['status' => 1, 'msg' =>self::$ERROR['1']];

        return  json_encode($data);
    }

    /**
     * 页面分享接口(加颜值分)
     * @return json
     */
    PUBLIC function sharePage(){
        //验证
        $requiredParameter  = array('user_id');
        $this->completeParameter($requiredParameter);

        $userId = intval(self::$data['user_id']);

        $is_plus = Functions::updateMoney($userId,10,'分享页面',3);
        $msg['flag'] = $is_plus ? 1 : 0;
        $msg['content'] = $msg['flag'] == 1 ? '分享页面，+10分' : '';

        $data  = ['status' => 1, 'msg' =>$msg];
        return  json_encode($data);
    }

    /**
     * 保存幻灯点击接口
     * @para int    $id        ID
     * @return json 成功返回信息，失败返回错误
     */
    PUBLIC function bannerLog()
    {
        //验证
        $requiredParameter  = array('id');
        $this->completeParameter($requiredParameter);
        
        //查询        
        $userId    = intval(self::$data['user_id']);
        $id        = intval(self::$data['id']);
        $referer   = strtolower(self::$data['from']) == 'ios' ? 'ios' : 'android';
        //参与日志
        Functions::bannerLog($userId,$id,$referer);

        $data  = ['status' => 1, 'msg' =>self::$ERROR['1']]; 

        return  json_encode($data);
    }
    /**
    *  用户肤质推荐
    *  @para int    $user_id    用户ID
    * 
    * @return json 成功返回信息，失败返回错误
    */
    PUBLIC function getSkinRecommend(){
        $userId     = intval(self::$data['user_id']);
        $info       = skin::getUserSkin($userId);

        if(!$info) {
            $data = ['status' => -1, 'msg' =>self::$ERROR['0']];
            return json_encode($data);
        }
        $skinName = $info['skin_name'];

        $return['skinProduct']  = skin::skinProduct($skinName);
        $return['skinArticle']  = skin::skinArticle($skinName);
        $return['categoryList'] = skin::getUserSkinCopy($userId);

        $data = ['status' => 1, 'msg' =>$return];
        return  json_encode($data);
    }
    /**
    *  用户肤质推荐列表
    *  @para int    $user_id    用户ID
    *  @para int    $cate_id    栏目ID
    *  @para int    $pageSize   每页数
    *  @para int    $page       页数
    * @return json 成功返回信息，失败返回错误
    */
    PUBLIC function getSkinRecommendList(){
        //验证
        $requiredParameter  = array('cate_id');
        $this->completeParameter($requiredParameter);

        $userId    = intval(self::$data['user_id']);
        $cateId    = intval(self::$data['cate_id']);
        $min       = isset(self::$data['min']) ? floatval(self::$data['min']) : '';
        $max       = isset(self::$data['max']) ? floatval(self::$data['max']) : '';
        $pageSize  = isset(self::$data['pageSize']) ? intval(self::$data['pageSize']) : 20;
        $page      = isset(self::$data['page'])     ? intval(self::$data['page']) : 1;

        $info      = skin::getUserSkin($userId);

        if(!$info) {
            $data = ['status' => -1, 'msg' =>self::$ERROR['0']];
            return json_encode($data);
        }

        $skinName = $info['skin_name'];
        $return   = skin::skinProductList($skinName,$cateId,$page,$pageSize,$min,$max);

        return json_encode($return);   
    }
    /**
    *  颜值积分记录
    *  @para int    $user_id    用户ID
    *  @para int    $page       页数
    *  @para int    $pageSize   每页数
    * 
    * @return json 成功返回信息，失败返回错误
    */
    PUBLIC function faceList(){
        $userId    = isset(self::$data['user_id'])  ? intval(self::$data['user_id']) : 0 ;
        $pageSize  = isset(self::$data['pageSize']) ? intval(self::$data['pageSize']) : 20;
        $page      = isset(self::$data['page'])     ? intval(self::$data['page']) : 1;
        $pageMin   = ($page - 1) * $pageSize;
        /*查询条件*/
        $whereStr   = "user_id = '$userId' AND pay = '积分'";

        /*分页*/
        $sql        = " SELECT COUNT(*)  FROM {{%user_account}} WHERE $whereStr";

        $num        = Yii::$app->db->createCommand($sql)->queryScalar();

        
        $selectSql  = " SELECT `money`,`content`,`created_at`,`type` FROM {{%user_account}}
                        WHERE $whereStr  ORDER BY created_at DESC  LIMIT $pageMin,$pageSize";

        $rows       = Yii::$app->db->createCommand($selectSql)->queryAll();
        if($rows){
            foreach($rows as $key =>$val){
                if($val['type'] == -1){
                    $rows[$key]['money'] = '-'.$val['money'];
                }
                unset($rows[$key]['type']);
            }
        }

        $data = ['status' => 1, 'msg' => $rows,'pageTotal' => $num, 'pageSize'=> $pageSize];
        return  json_encode($data);
    }
    /**
    *  消息设为已读
    *  @para int    $user_id    用户ID
    *  @para int    $id         消息ID
    *  @para int    $type       类型
    * @return json 成功返回信息，失败返回错误
    */
    PUBLIC function readNotice(){
        $requiredParameter  = array('id','type');
        $this->completeParameter($requiredParameter);

        $userId    = intval(self::$data['user_id']);
        $id        = intval(self::$data['id']);
        $type      = Functions::checkStr(self::$data['type']);
        
        $time      = time();

        $data      = ['status' => 1, 'msg' =>self::$ERROR['1']];
        switch ($type) {
            case 'system':
                $isExsit   = NoticeSystem::findOne($id); 
                if($isExsit){
                    $sql        = 'SELECT * FROM {{%notice_system_read}} WHERE notice_id = \''.$id.'\' AND user_id = \''.$userId.'\'';
                    $isRead     = Yii::$app->db->createCommand($sql)->queryOne();
                    if(!$isRead){
                        $insertSql = "INSERT INTO {{%notice_system_read}} (user_id,notice_id,created_at) VALUES ('$userId','$id','$time')";
                        Yii::$app->db->createCommand($insertSql)->execute();
                    }
                }
                break;
            case 'notice':
                $isExsit   = NoticeUser::findOne($id); 

                if($isExsit){
                    $sql        = "UPDATE {{%notice_user}} SET status = '1' WHERE id = '$id' AND user_id = '$userId'";
                    $isRead     = Yii::$app->db->createCommand($sql)->execute();
                }
                break;
            case 'pms':
                $isExsit   = Pms::findOne($id); 
                if($isExsit){
                    $sql        = "UPDATE {{%pms}} SET status = '1' WHERE id = '$id' AND receive_id = '$userId'";
                    $isRead     = Yii::$app->db->createCommand($sql)->execute();
                }
                break;
            default:
                $data     = ['status' => -1, 'msg' =>self::$ERROR['0']];
                break;
        }
        return json_encode($data);
    } 
    /**
    * 通知-获取未读数量
    * @para int    $userId    用户ID
    * @return json 成功返回信息，失败返回错误
    */
    PUBLIC function noticeUnread(){
        //查询
        $userId     = intval(self::$data['user_id']);
        $userInfo   = User::findOne(['id'=>$userId]);

        if(!$userInfo){
            $data    = ['status' => -1, 'msg' =>self::$ERROR['-2']];
            return json_encode($data);
        }
        $userInfo   = $userInfo->toArray();
        $startTime  = $userInfo['created_at'];
        //系统通知数
        $systemSql  = "SELECT COUNT(*) AS num FROM {{%notice_system}} WHERE status = '1' AND created_at >= '$startTime'";
        $systemNum  = Yii::$app->db->createCommand($systemSql)->queryScalar();
        //系统通知已读
        $readSql    = "SELECT COUNT(*) FROM {{%notice_system_read}} WHERE user_id = '$userId'";
        $readNum    = Yii::$app->db->createCommand($readSql)->queryScalar();
        //用户通知数
        $userSql    = "SELECT COUNT(*) AS num FROM {{%notice_user}} WHERE user_id = '$userId' AND status = '0' AND type != '2'";
        $userNum    = Yii::$app->db->createCommand($userSql)->queryScalar();
        //用户消息数
        $pmsSql     = "SELECT COUNT(*) AS num FROM {{%pms}} WHERE receive_id = '$userId' AND status = '0'";
        $pmsNum     = Yii::$app->db->createCommand($pmsSql)->queryScalar();

        $systemUnNum= intval($systemNum - $readNum);
        $systemUnNum= $systemUnNum < 0 ? 0 : $systemUnNum;
        $unNum      = $userNum + $systemUnNum;
      
        $unReadsysNum  = $unNum >= 0 ? $unNum : 0 ;
        $unReadNum     = $unReadsysNum + $pmsNum;

        //过期通知数
        $overdueSql = "SELECT COUNT(*) AS num FROM {{%notice_user}} WHERE user_id = '$userId' AND status = '0' AND type = '2'";
        $overdueNum = Yii::$app->db->createCommand($overdueSql)->queryScalar();

        $info       = skin::getUserSkin($userId);
        $isComplete = 0;
        if($info && $info['skin_name']){
            $isComplete = 1;
        }

        $nums = [
            'overdueNum' => $overdueNum,
            'unReadNum'  => $unReadNum,
            'isComplete' => $isComplete,
        ];

        $data   = ['status' => 1, 'msg' => $nums];
        return json_encode($data);
    }
    /*****************************************************************************************************************************/
                                /*FROM 凌忠进 */
    /*****************************************************************************************************************************/
    /**
    * 发送短信接口
    * @para string $mobile    手机
    * @para int    $type      0为注册，1为找回，2登录，3登录注册,4为绑定
    * @return json 成功返回用户信息，失败返回错误
    */
    PUBLIC function message(){
        $requiredParameter  = array('mobile');
        $this->completeParameter($requiredParameter);

        //测试与正式二套方案
        $isEnviron  =  Yii::$app->params['isOnline'];
       
        $time         = time();
        $expire_time  = $time  + 60 * 30;
        $captcha      = $isEnviron ? rand(1000, 9999) :  1111;
        $mobile       = Functions::checkStr(self::$data['mobile']);
        $type         = intval(self::$data['type']) ? intval(self::$data['type']) : 0; 

        // 验证手机号
        $isTel = Functions::isMoblie($mobile);

        if(!$isTel){
            $data = ['status' => -14, 'msg' => self::$ERROR['-14']];
            return json_encode($data);   
        }
        //开始发送短信验证码
        $output = Functions::captcha($mobile, $captcha);

        if ($output->code == 0 || !$isEnviron ){
            //入库
            $sql    = "INSERT INTO {{%mobile_captcha}} (`mobile`, `captcha`, `expire_time`,`type`,`created_at`) 
                      VALUES ('$mobile', '$captcha','$expire_time','$type','$time')";

            $return = Yii::$app->db->createCommand($sql)->execute();

            $data = ['status' => 1, 'msg' => self::$ERROR['1']];
        }else{
            $data = ['status' => '-13','msg'=>self::$ERROR['-13']];
        }
        return json_encode($data);
    }
    /**
    * 注册接口
    * @para string $mobile    手机
    * @para string $password  密码
    * @para int    $captcha   手机验证码
    * @return json 成功返回用户信息，失败返回错误
    */
    public function register(){
        $requiredParameter  = array('mobile','captcha','password');
        $this->completeParameter($requiredParameter);


        $mobile   = self::$data['mobile'];
        $captcha  = intval(self::$data['captcha']);
        $time     = time(); 
        $referer  = strtolower(self::$data['from']) == 'ios' ? 'ios' : 'android';

        // 验证手机号
        $isTel = Functions::isMoblie($mobile);

        if(!$isTel){
            $data = ['status' => -14, 'msg' => self::$ERROR['-14']];
            return json_encode($data);   
        }

        //检查用户
        $hasUser   = Functions::checkIsuser($mobile);
        
        if($hasUser) {
            $data = ['status' => '-17', 'msg' => self::$ERROR['-17']];
            return  json_encode($data);
        }

        //验证验证码
        $isCaptcha   = Functions::checkCaptcha($mobile,$captcha,0);
        
        if(!$isCaptcha) {
            $data = ['status' => '-12', 'msg' => self::$ERROR['-12']];
            return  json_encode($data);
        }

        $model    = new SignupForm();
        self::$data['referer']    = $referer;
        $signupForm['SignupForm'] = self::$data;
        $model->load($signupForm);
        if ($user = $model->signup()) {
            $params = ['user_id' => $user->id , 'referer' => self::$data['from']];
            $token  = Functions::getToken($params);

            $data = ['status' => 1, 'msg' => ['user_id' => $user->id,'token' => $token]];
            //修改验证码状态
            $captchaSql   = "UPDATE  {{%mobile_captcha}} SET is_use = '1',using_time = '$time' WHERE mobile ='$mobile' AND captcha = '$captcha' AND type = '0' AND expire_time >= '$time'";
            Yii::$app->db->createCommand($captchaSql)->execute();
            Functions::updateMoney($user->id,200,'注册',2);
            // 添加注册成功通知
            NoticeFunctions::notice($user->id, NoticeFunctions::$SIGN_UP);
        } else {
            $data = ['status' => -4, 'msg' => $model->getErrors()];
        }
        return  json_encode($data);
    }
    /**
    * 登录接口
    * @para int    $type      1密码登录 2快捷登录
    * @para string $mobile    手机
    * @para string $password  密码
    * @para int    $captcha   手机验证码
    * @return json 成功返回用户信息，失败返回错误
    */
    public function login()
    {
        $requiredParameter  = array('mobile','type','password');
        $this->completeParameter($requiredParameter);

        //参数
        $mobile     = Functions::checkStr(self::$data['mobile']);
        $type       = !empty(self::$data['type']) ? self::$data['type'] : 1;
        $password   = !empty(self::$data['password']) ? self::$data['password'] : '';
        $captcha    = !empty(self::$data['captcha']) ? self::$data['captcha'] : '';

        //验证帐号
        //用户不存在
        $rows = (new \yii\db\Query())
                ->select('id,password_hash,username,img,status')
                ->from('{{%user}}')
                ->where(['mobile' => $mobile])
                ->one();     
        if(empty($rows)){
           $return = array('status' => '-2','msg'=>self::$ERROR['-2']);
           return  json_encode($return);
        }
        //封号
        if ($rows['status'] == 3) {
            $return = ['status' => -10, 'msg' => self::$ERROR['-10']];
            return  json_encode($return);
        } 
        
        //验证密码或验证码
        if ($type == 1) { 
            if (Yii::$app->getSecurity()->validatePassword($password, $rows['password_hash'])) {
                $params = ['user_id' => $rows['id'] , 'referer' => self::$data['from']];
                $token  = Functions::getToken($params);
                $return = [
                        'status'    => '1',
                        'msg'       =>[
                            'user_id'   => $rows['id'],
                            'user_name' => Tools::userTextDecode($rows['username']),
                            'img'       => Functions::get_image_path($rows['img'],1),
                            'token'     => $token
                            ]
                        ];

            } else {
                $return = array('status' => '-1','msg'=>self::$ERROR['-1']);
            }
        } elseif ($type == 2) {
            $time = time();
            $sql = "SELECT * FROM {{%mobile_captcha}} WHERE mobile = '$mobile' AND captcha = '$captcha' AND expire_time >= '$time' AND type = '$type' ORDER BY id DESC LIMIT 1";
            $captchaInfo = Yii::$app->db->createCommand($sql)->queryOne();

            if(empty($captchaInfo) || $captchaInfo['captcha'] != $captcha ){
                $return = array('status' => '-12','msg'=>self::$ERROR['-12']);
            } else {
                //修改验证码状态
                $captchaSql   = "UPDATE  {{%mobile_captcha}} SET is_use = '1',using_time = '$time' WHERE mobile ='$mobile' AND captcha = '$captcha' AND type = '$type' AND expire_time >= '$time'";
                $update = Yii::$app->db->createCommand($captchaSql)->execute();
                if ($update) {
                    $params = ['user_id' => $rows['id'] , 'referer' => self::$data['from']];
                    $token  = Functions::getToken($params);
                    $return = [
                        'status' => '1',
                        'msg'=>[
                            'user_id'   => $rows['id'],
                            'user_name' => Tools::userTextDecode($rows['username']),
                            'user_img'  => Functions::get_image_path($rows['img'],1),
                            'token'     => $token
                        ],
                    ];
                } else {
                    $return = ['status' => '0', 'msg' => self::$ERROR['0']];
                }
            }         
        }        
        return  json_encode($return);
    }
    /**
    * 第三方登录接口
    * @para string $openid    openid
    * @para string $unionid   开放平台用户统一标识
    * @para string $nickname  普通用户昵称
    * @para string $sex       普通用户性别，1为男性，2为女性
    * @para string $city      普通用户个人资料填写的城市
    * @para string $headimgurl用户头像
    * @para int    $type      类型，目前微信为weixin
    * @return json 成功返回用户信息，失败返回错误
    */
    public function thirdLogin()
    {
        $requiredParameter  = array('openid','unionid','nickname');
        $this->completeParameter($requiredParameter);

        //参数
        $time       = time();
        $openid     = Functions::checkStr(self::$data['openid']);
        $nickname   = Functions::checkStr(self::$data['nickname']);
        $unionid    = isset(self::$data['unionid'])       ? Functions::checkStr(self::$data['unionid']) : '' ;
        $sex        = isset(self::$data['sex']) &&  self::$data['sex'] !== ''  ? intval(self::$data['sex']) : 2 ;
        $province   = isset(self::$data['province'])      ? self::$data['province'] : '' ;
        $city       = isset(self::$data['city'])          ? self::$data['city'] : '' ;
        $headimgurl = isset(self::$data['headimgurl'])    ? self::$data['headimgurl'] : '' ;
        $type       = isset(self::$data['type'])          ? self::$data['type'] : 'weixin' ;
        $referer    = strtolower(self::$data['from']) == 'ios' ? 'ios' : 'android';
        $version    = isset(self::$data['version'])       ? intval(self::$data['version']) : '1';

        //查询用户
        $selectSql  = " SELECT user_id,unionid FROM {{%third_login}}  WHERE unionid = '$unionid'";
        $userInfo     = Yii::$app->db->createCommand($selectSql)->queryOne();

        //存在返回用户ID，不存在则注册
        if(!empty($userInfo['user_id'])) {
            $userInfo = Functions::getUserInfo($userInfo['user_id']);
            $params = ['user_id' => $userInfo['id'] , 'referer' => self::$data['from']];
            $token  = Functions::getToken($params);
            $data = [
                'status' => '1',
                'msg'=>[
                    'user_id' => $userInfo['id'],
                    'user_name' => Tools::userTextDecode($userInfo['username']),
                    'user_img' => $userInfo['img'],
                    'mobile'    => $userInfo['mobile'],
                    'token'     => $token
                    ],
            ];
            return  json_encode($data); die;
        }
        //先入库UNIONID
        try {
            //过滤EMOJI
            $filter     = ['💭' => '☁'];
            if(array_key_exists($nickname,$filter)){
                $nickname = $filter[$nickname];
            }
            //再入库用户
            $user           = new User();
            $nickname       = Tools::userTextEncode($nickname,1);
            $user->username = $nickname;
            $user->img      = $headimgurl ? Functions::uploadUrlimg($headimgurl) : 'photo/member.png';
            $user->sex      = $sex;
            $user->province = $province;
            $user->city     = $city;
            $user->mobile   = $type;
            $user->status   = '1';
            $user->referer  = $referer;
            $user->version  = $version;
            $user->setPassword($openid);
            $user->generateAuthKey();
            $user->save();
            $userId  = $user->id;
            // $user->mobile   = $type . $user->id;
            // $user->save();
            if(!empty($userId)){
                if(empty($userInfo['user_id']) && !empty($userInfo['unionid'])){
                    $upSql = "UPDATE {{%third_login}} SET user_id = '$userId' WHERE unionid = '$unionid'";
                    Yii::$app->db->createCommand($upSql)->execute();
                }else{
                    //插入第三方登录记录
                    $thirdSql   = "INSERT INTO {{%third_login}} (user_id,type,openid,unionid,created_at) 
                                 VALUES('$userId','$type','$openid','$unionid','$time')";
                    Yii::$app->db->createCommand($thirdSql)->execute();
                }
                NoticeFunctions::notice($userId, NoticeFunctions::$SIGN_UP);
                //返回TOKEN
                $params = ['user_id' => $userId, 'referer' => self::$data['from']];
                $token  = Functions::getToken($params);
                $data   = [
                    'status' => 1,
                    'msg' => [
                        'user_id'   => $userId,
                        'user_name' => $nickname,
                        'user_img'  => Functions::get_image_path($user->img,1),
                        'mobile'    => '',
                        'token'     => $token
                        ]
                    ];
            }else{
                $data = ['status' => -1, 'msg' => self::$ERROR['-1']];
            }
        } catch (Exception $e) {
            $data = ['status' => -4, 'msg' => $user->getErrors()];
        }
        return  json_encode($data);
    }
    /*
    *添加收藏
    *@type 类型 1长草2文章
    */
    PUBLIC function collect(){
        $requiredParameter  = array('relation_id','type');
        $this->completeParameter($requiredParameter);

        $userId         = isset(self::$data['user_id'])         ? intval(self::$data['user_id']) : 0 ;
        $relationId     = isset(self::$data['relation_id'])    ? intval(self::$data['relation_id']) : 0 ;
        $type           = isset(self::$data['type'])           ? intval(self::$data['type']) : 1;
        $time           = time();

        $selectSql      = "SELECT * FROM {{%user_collect}}  WHERE user_id = '$userId' AND relation_id = '$relationId' AND type = '$type'";
        $result         = Yii::$app->db->createCommand($selectSql)->queryScalar();

        $data = ['status' => 1, 'msg' => self::$ERROR['1']];

        if(!empty($result)){
            $deleteSql  = "DELETE FROM {{%user_collect}}  WHERE user_id = '$userId' AND relation_id = '$relationId' AND type = '$type'";
            $ret        = Yii::$app->db->createCommand($deleteSql)->execute();
        }else{
            $addSql     = "INSERT INTO {{%user_collect}} (user_id,relation_id,add_time,type) VALUES ('$userId','$relationId','$time','$type')";
            $ret        = Yii::$app->db->createCommand($addSql)->execute();
        }

        if(!$ret) $data = ['status' => 0, 'msg' => self::$ERROR['0']];
        return  json_encode($data);

    }
    /**
     * [userCollect 用户收藏]
     * @return [type] [description]
     */
    PUBLIC function userCollect(){
        //参数
        $userId     = intval(self::$data['user_id']);
        $pageSize   = isset(self::$data['pageSize']) ? intval(self::$data['pageSize']) : 20;
        $page       = isset(self::$data['page']) ? intval(self::$data['page']) : 1;
        $pageMin    = ($page - 1) * $pageSize;

        $whereStr   = "C.user_id = '$userId'  AND C.type = '2'";
        $sql        = "SELECT COUNT(*) AS num FROM {{%user_collect}} C WHERE $whereStr";
        $num        = Yii::$app->db->createCommand($sql)->queryScalar();

        $sql        = " SELECT A.id,C.add_time,A.title,A.article_img,A.like_num, A.comment_num,A.created_at,A.click_num
                        FROM {{%user_collect}} C LEFT JOIN {{%article}}  A ON  C.relation_id = A.id 
                        WHERE $whereStr ORDER BY C.add_time DESC LIMIT $pageMin,$pageSize";

        $data       = Yii::$app->db->createCommand($sql)->queryAll();
        
        foreach ($data as $key => $value) {
            $data[$key]['isGras']       = 1;
            $data[$key]['created_at']   = Tools::HtmlDate($value['created_at']);
            $data[$key]['article_img']  = Functions::get_image_path($value['article_img'],1);
            $data[$key]['linkUrl']      = Yii::$app->params['mfrontendUrl'].'article/index?id='.$value['id'].'&isNew=0&from='.self::$data['from'];
        }

        $data = ['status' => 1, 'msg' => $data ,'pageTotal' => $num, 'pageSize'=> $pageSize];

        return  json_encode($data);
    }
    /**
     * [用户评论表]
     * @param  [int] $userId [用户id]
     */
    PUBLIC function userComment(){
        $userId     = isset(self::$data['user_id']) ? intval(self::$data['user_id']) : 0 ;
        $type       = isset(self::$data['type'])    ? intval(self::$data['type']) : 0;
        $type_sql   = !empty($type)                 ? " AND type = '$type'" : '';

        $pageSize   = isset(self::$data['pageSize']) ? intval(self::$data['pageSize']) : 20;
        $page       = isset(self::$data['page']) ? intval(self::$data['page']) : 1;
        $pageMin    = ($page - 1) * $pageSize;

        $sql        = "SELECT COUNT(*) AS num FROM {{%comment}}  WHERE user_id = '$userId'".$type_sql;
        $num        = Yii::$app->db->createCommand($sql)->queryScalar();

        $selectSql  = "SELECT id,type,post_id,comment,star,created_at FROM {{%comment}}  WHERE user_id = '$userId' AND parent_id = 0 ".$type_sql." ORDER BY created_at DESC LIMIT $pageMin,$pageSize";
        $result     = Yii::$app->db->createCommand($selectSql)->queryAll();

        $product_ids = [];
        $article_ids = [];
        $product_info_new = [];
        $article_info_new = [];
        if(!empty($result)){
            foreach ($result as $key => $value) {
                $value['type'] == 1 ? $product_ids[] = $value['post_id'] : $article_ids[] = $value['post_id'];
            }
            $product_ids = array_unique($product_ids);
            $article_ids = array_unique($article_ids);
        
            if(!empty($product_ids)){
                $product_str = Functions::db_create_in($product_ids,'id');
                $productSql      = "SELECT id,product_name as name,product_img,price,form,star FROM {{%product_details}}  WHERE $product_str";
                $product_info    = Yii::$app->db->createCommand($productSql)->queryAll();
                foreach ($product_info as $k1 => $v1) {
                    $product_info_new[$v1['id']]        = $v1;
                    $product_info_new[$v1['id']]['img'] = Functions::get_image_path($v1['product_img'],1);
                    $product_info_new[$v1['id']]['price'] = (float)$v1['price'];
                    unset($product_info_new[$v1['id']]['product_img']);
                }
            }

            if(!empty($article_ids)){
                $article_str = Functions::db_create_in($article_ids,'id');
                $articleSql      = "SELECT id,title as name,article_img,comment_num FROM {{%article}}  WHERE $article_str";
                $article_info    = Yii::$app->db->createCommand($articleSql)->queryAll();
                foreach ($article_info as $k2 => $v2) {
                    $article_info_new[$v2['id']]            = $v2;
                    $article_info_new[$v2['id']]['img']     = Functions::get_image_path($v2['article_img'],1);
                    $article_info_new[$v2['id']]['linkUrl'] = Yii::$app->params['mfrontendUrl'].'article/index?id='.$v2['id'].'&isNew=0&from='.self::$data['from'];
                    $article_info_new[$v2['id']]['isGras']  = Functions::userIsGras($userId,$v2['id'],2) ? 1 : 0;
                    unset($article_info_new[$v2['id']]['article_img']);
                }
            }
            foreach ($result as $k3 => $v3) {
                $info = $v3['type'] == 1 ?  'product_info_new' : 'article_info_new';
                $info = $$info;
                $result[$k3]['detail']  = isset($info[$v3['post_id']]) ? $info[$v3['post_id']] : [] ;
                $result[$k3]['comment'] = Tools::userTextDecode($v3['comment']);
                $result[$k3]['star']    = $v3['star'];
            }
        }
        $data = ['status' => 1, 'msg' => $result,'pageTotal' => $num, 'pageSize'=> $pageSize];
        return  json_encode($data);

    }
    /**
     * [userPms 用户消息]
     * @return [type] [消息数据]
     */
    PUBLIC function userPms(){
        $time       = time();
        $userId     = intval(self::$data['user_id']);
        $pageSize   = isset(self::$data['pageSize'])    ? intval(self::$data['pageSize'])   : 20;
        $page       = isset(self::$data['page'])        ? intval(self::$data['page'])       : 1;
        $pageMin    = ($page - 1) * $pageSize;
        //查询
        $userId     = intval(self::$data['user_id']);
        $userInfo   = User::findOne($userId);
        if(!$userInfo){
            $data   = ['status' => -1, 'msg' =>self::$ERROR['-2']];
            return json_encode($data);
        }
        $userInfo = $userInfo->toArray();
        $startTime  = $userInfo['created_at'];
        //以下pms表group by a.type字段是防止评论点赞和问答点赞出现合并的情况
        $sql = "SELECT id,relation_id,0 as askid,0 AS user_name,content,created_at,'photo/admin_user.png' AS img ,100 AS type,type as otype,0 as num FROM {{%notice_user}}  WHERE user_id = '$userId' AND type != '2'
                UNION all 
                SELECT id ,relation_id,askid,user_name ,content,max(created_at) AS created_at,img ,type, otype,count(*) as num from (
                SELECT p.id AS id,c.post_id as relation_id,p.relation_id as askid,u.username AS user_name,p.message AS content,p.created_at,u.img ,p.type,c.type as otype  FROM {{%pms}} AS p
                LEFT JOIN {{%user}} AS u ON p.from_id = u.id 
                LEFT JOIN {{%comment}} AS c ON p.relation_id = c.id WHERE p.receive_id = '$userId' ORDER BY p.id DESC) a GROUP BY a.type,a.relation_id,a.content
                UNION all 
                SELECT ns.id AS id,relation as relation_id,0 as askid,a.username AS user_name,ns.content,ns.created_at,'photo/admin_user.png' AS img,99 AS type, type as otype ,0 as num 
                FROM {{%notice_system}} AS ns LEFT JOIN {{%admin}} AS a ON ns.admin_id = a.id WHERE ns.status = '1' AND ns.created_at>= '$startTime' ORDER BY created_at DESC ";

        $sql_count  = "SELECT COUNT(*) AS num FROM ($sql) t ";

        $num        = Yii::$app->db->createCommand($sql_count)->queryScalar();
        $result     = [];
        //分页判断
        if($num > 0){
            $maxPage            = ceil($num/$pageSize);
            if($page > $maxPage){
                return json_encode(['status' => 1, 'msg' => $result,'pageTotal' => $num, 'pageSize'=> $pageSize]);
            }
            $pageMin= ($page - 1) * $pageSize; 

            /**
            * DESC:所对应客户端的类型，以后可在此添加类型，具体其他参数再做逻辑 
            * 
            * 字段type ：2是点赞 4是系统回复用户 5是收到提问 6 提问消息回复 7二级回复评论 8为后台指定用户一对一的发送消息 9问答评论点赞 99（notice_system）,100是系统推送（notice_user）  
            * 对应客户端 type ：1 用户消息 2 系统消息 3 提问消息 4 提问消息回复 5用户回复评论 6问答评论点赞
            * (以下$type_arr 键名对应字段type，键值对应客户端type)
            * 对应客户端 otype ：0系统消息 1 产品 2 文章 3评论
            */
            $type_arr = ['2'=>'1','4'=>'2','5'=>'3','6'=>'4','7'=>'5','8'=>'2','9'=>'6','99'=>'2','100'=>'2'];

            $selectSql      = "SELECT * FROM ($sql) t ORDER BY t.created_at DESC LIMIT $pageMin,$pageSize";
            $result         = Yii::$app->db->createCommand($selectSql)->queryAll();
            if(!empty($result)){
                foreach ($result as $key => $value) {
                    $result[$key]['user_name']      = empty($value['user_name'])  ? '颜究院小秘书' : Tools::userTextDecode($value['user_name']);
                    $result[$key]['content']        = $value['content'] ? Tools::userTextDecode($value['content']) : ''; //不赋值给$value是为了防止漏掉其他类型
                    if($value['type'] != 100 && $value['type'] != 4 && $value['type'] != 5 && $value['num'] > 1){
                        $result[$key]['user_name']  = Tools::userTextDecode($result[$key]['user_name']).'...等'.$value['num'].'人';
                    }
                    if($value['type'] == 4 || $value['type'] == 8){
                        $result[$key]['user_name']  = '颜究院小秘书';
                        $result[$key]['img']        = 'photo/admin_user.png';
                        $result[$key]['content']    =  $value['type'] == 4 ? '回复了你的意见反馈：'.$result[$key]['content'] : $result[$key]['content'];
                        $result[$key]['otype']      = '0';
                    }
                    //1.1.0 notice_system表消息里面带链接
                    if($value['type'] == 99){
                        $result[$key]['relation_id'] = empty($value['relation_id'])  ?  '0' : trim($value['relation_id']);
                        switch ($value['otype']) {//notice_system表1为过期提醒，2为H5，3为文章,4为产品，0为常规
                            case '2':
                                $result[$key]['content']        = $result[$key]['content'].$value['relation_id'];
                                $result[$key]['relation_id']    = '0';
                                $result[$key]['otype']          = '0';
                                break;
                            case '3':
                                $result[$key]['otype'] = '2';
                                break;
                            case '4':
                                $result[$key]['otype'] = '1';
                                break;
                            default:
                                $result[$key]['otype'] = '0';
                                break;
                        }
                    }
                    //1.1.1新增的显示用户的评论为精华
                    if($value['type'] == 100 ){
                        $result[$key]['otype']  = $value['otype'] == 3 ? '3' : '0';//0系统消息 1 产品 2 文章 3评论
                    }
                    $result[$key]['img']    = Functions::get_image_path($result[$key]['img'],1);
                    $result[$key]['type']   = isset($type_arr[$value['type']]) ? $type_arr[$value['type']] : '2';
                   
                }
            }
            //消息设为已读
            $readPmSql = "UPDATE {{%pms}} SET status = '1' WHERE receive_id = '$userId'";
            Yii::$app->db->createCommand($readPmSql)->execute();
            //消息设为已读
            $readSql = "UPDATE {{%notice_user}} SET status = '1' WHERE user_id = '$userId' AND type != '2'";
            Yii::$app->db->createCommand($readSql)->execute(); 

            $noticeList   = NoticeSystem::find()->where(['>', 'created_at',$startTime])->all();
            foreach ($noticeList as $k => $v) {
                $sql        = 'SELECT * FROM {{%notice_system_read}} WHERE notice_id = \''.$v['id'].'\' AND user_id = \''.$userId.'\'';
                $isRead     = Yii::$app->db->createCommand($sql)->queryOne();
                if(!$isRead){
                    $insertSql = "INSERT INTO {{%notice_system_read}} (user_id,notice_id,created_at) VALUES ('$userId','$v[id]','$time')";
                    Yii::$app->db->createCommand($insertSql)->execute();
                }
                unset($isRead);
            }
        }
        $data = ['status' => 1, 'msg' => $result,'pageTotal' => $num, 'pageSize'=> $pageSize];
        return  json_encode($data);
    }
    /**
    * 用户回复
    * @para string $user_id    用户id
    * @return json 成功返回信息，失败返回错误
    */
    PUBLIC function userReply()
    {
        $userId     = intval(self::$data['user_id']);
        $pageSize   = isset(self::$data['pageSize'])    ? intval(self::$data['pageSize'])   : 20;
        $page       = isset(self::$data['page'])        ? intval(self::$data['page'])       : 1;
        $pageMin    = ($page - 1) * $pageSize;
        //查询
        $userInfo   = User::findOne($userId);
        if(!$userInfo){
            $data   = ['status' => -1, 'msg' =>self::$ERROR['-2']];
            return json_encode($data);
        }

        $sql = "SELECT C.first_id AS id,C.created_at,PD.product_name AS content , 1 AS type,C.post_id as product_id FROM {{%comment}} C LEFT JOIN {{%product_details}} PD ON C.post_id = PD.id WHERE C.type = 1 AND C.user_id = '$userId' AND C.parent_id != 0 AND C.status = 1
                UNION 
                SELECT C.first_id AS id,C.created_at,AE.title AS content , 2 AS type,0 AS product_id FROM {{%comment}} C LEFT JOIN {{%article}} AE ON C.post_id = AE.id WHERE C.type = 2 AND C.user_id = '$userId' AND C.parent_id != 0 AND C.status = 1
                UNION 
                SELECT AR.askid AS id,AR.add_time AS created_at,AK.subject AS content  ,3 AS type, AK.product_id FROM {{%ask_reply}} AR LEFT JOIN {{%ask}} AK ON AR.askid = AK.askid WHERE AR.user_id = '$userId'";

        $sql_count  = "SELECT COUNT(*) AS num FROM ($sql) t ";

        $num        = Yii::$app->db->createCommand($sql_count)->queryScalar();
        $result     = [];
        //分页判断
        if($num > 0){
            $maxPage            = ceil($num/$pageSize);
            if($page > $maxPage){
                return json_encode(['status' => 1, 'msg' => $result,'pageTotal' => $num, 'pageSize'=> $pageSize]);
            }
            $pageMin= ($page - 1) * $pageSize; 

            $selectSql      = "SELECT * FROM ($sql) t ORDER BY t.created_at DESC LIMIT $pageMin,$pageSize";
            $result         = Yii::$app->db->createCommand($selectSql)->queryAll();
            if(!empty($result)){
                foreach ($result as $key => $value) {
                    $str    = $result[$key]['content'];
                    $title  = Functions::sub_str($str,10,1);
                    $result[$key]['content']  =  $title;
                    unset($str,$title);
                }
            }
            
        }
        $data = ['status' => 1, 'msg' => $result,'pageTotal' => $num, 'pageSize'=> $pageSize];
        return  json_encode($data);
    }
   /**
    * 提交问题
    * @para string $user_id    用户id
    * @para string $product_id    产品id
    * @para string $content   提交标题
    * @return json 成功返回信息，失败返回错误
    */
    PUBLIC function subAsk()
    {
        $type     = intval(self::$data['type']);
        $msg = "";
        $username = Functions::getUserInfo(self::$data['user_id'])['username'];
        if(isset($type) && $type == 1){
            $requiredParameter  = array('product_id','product_name','content');
            $this->completeParameter($requiredParameter);

            $product_id     = intval(self::$data['product_id']);
            $user_id        = intval(self::$data['user_id']);
            $subject        = Tools::userTextEncode(Functions::checkStr(self::$data['content']),1) ;

            $product_name   = isset(self::$data['product_name'])    ? Functions::checkStr(self::$data['product_name']) : '' ;
            $time           = time();

            $Sql            = "INSERT IGNORE INTO {{%ask}} (subject,username,user_id,product_name,product_id,add_time,status) 
                             VALUES('$subject','$username','$user_id','$product_name','$product_id','$time',1)";
            $result         = Yii::$app->db->createCommand($Sql)->execute();
            $askid          = Yii::$app->db->getLastInsertId();

            $user_sql       = "SELECT user_id FROM {{%comment}} WHERE post_id = '$product_id' AND type = 1 AND parent_id = 0 GROUP BY user_id" ;
            $user_ids       = Yii::$app->db->createCommand($user_sql)->queryColumn();

            if(!empty($user_ids)){
                $user_str       = Functions::db_create_in($user_ids,'receive_id');
                $start_time     = strtotime(date('Y-m-d'));
                $end_time       = $start_time + 86400;
                $receive_sql    = "SELECT receive_id FROM {{%pms_log}}  WHERE $user_str AND created_at >= '$start_time' AND created_at < '$end_time'";                    
                $receive_ids    = Yii::$app->db->createCommand($receive_sql)->queryColumn();
                $receive_arr    = array_diff($user_ids,$receive_ids);
                if(!empty($receive_arr)){
                    foreach($receive_arr as $k => $v){
                        ReplyFunctions::reply($user_id,$askid,ReplyFunctions::$USER_ASK,$subject,$v);
                    }
                }

            }

            $msg['id'] = '';
            $msg['flag'] = 0;
            $msg['content'] = '';
        }else{
            $requiredParameter  = array('askid','content');
            $this->completeParameter($requiredParameter);

            $askid          = intval(self::$data['askid']);
            $user_id        = intval(self::$data['user_id']);
            $reply          = Tools::userTextEncode(self::$data['content'],1) ;

            $time           = time();
            
            $ask_sql    = "SELECT * FROM {{%ask}}  WHERE askid = '$askid'";                    
            $ask_info    = Yii::$app->db->createCommand($ask_sql)->queryOne();
            if(!empty($ask_info)){
                $Sql = "INSERT IGNORE INTO {{%ask_reply}} (askid,username,user_id,reply,add_time) VALUES('$askid','$username','$user_id','$reply','$time')";
                $result = Yii::$app->db->createCommand($Sql)->execute();
                $msg['id'] = Yii::$app->db->getLastInsertId();
                if($ask_info['user_id'] != $user_id){
                    $is_plus = Functions::updateMoney($user_id,10,'回答问题',2);
                    $msg['flag'] = $is_plus ? 1 : 0;
                    $msg['content'] = $msg['flag'] == 1 ? '回答问题，+10分' : '';
                    ReplyFunctions::reply($user_id,$askid,ReplyFunctions::$USER_ASK_REPLY,$reply); 
                    // 问题得到评论时的推送
                    NoticeFunctions::JPushOne(['Alias' => $ask_info['user_id'],'option' => 'ask','id'=>$askid,'relation'=>$askid,'type'=>'5','replaceStr' => $ask_info['subject'] ]);
                }
            }
            
        }
        
        $data = ['status' => '1', 'msg' => $msg];
        return json_encode($data);
    }

    /**
    * 提问列表
    * @para string $product_id    产品id
    * @return json 成功返回信息，失败返回错误
    */
    PUBLIC function askList()
    {
        $requiredParameter  = array('product_id');
        $this->completeParameter($requiredParameter);

        $product_id = intval(self::$data['product_id']);
        $pageSize   = isset(self::$data['pageSize'])    ? intval(self::$data['pageSize'])   : 20;
        $page       = isset(self::$data['page'])        ? intval(self::$data['page'])       : 1;
        $pageMin    = ($page - 1) * $pageSize;

        $pinfo_sql  = "SELECT id,product_name,product_img,cate_id FROM {{%product_details}} WHERE id = '$product_id' AND status = 1";
        $product_info        = Yii::$app->db->createCommand($pinfo_sql)->queryOne();
        if(!$product_info){
            $data   = ['status' => -1, 'msg' =>self::$ERROR['-8']];
            return json_encode($data);
        }
        $result['product_id']   = $product_info['id'];
        $result['product_name'] = $product_info['product_name'];
        $result['product_img']  = Functions::get_image_path($product_info['product_img']);

        $sql_count  = "SELECT COUNT(*) AS num FROM {{%ask}} WHERE product_id = '$product_id' AND status = '1' ";

        $num        = Yii::$app->db->createCommand($sql_count)->queryScalar();
        $res        = [];
        //分页判断
        if($num > 0){
            $result['type']     = 1;//有问题
            $maxPage            = ceil($num/$pageSize);
            if($page > $maxPage){
                return json_encode(['status' => 1, 'msg' => $res,'pageTotal' => $num, 'pageSize'=> $pageSize]);
            }
            $pageMin= ($page - 1) * $pageSize; 

            $selectSql  = " SELECT A.askid,A.subject
                            FROM {{%ask}} A  WHERE A.product_id = '$product_id' AND A.status = '1' 
                            ORDER BY A.askid DESC LIMIT $pageMin,$pageSize";
            $res        = Yii::$app->db->createCommand($selectSql)->queryAll();
            foreach($res as $k => $v){
                $res[$k]['subject']         = Tools::userTextDecode($v['subject']);
                $res[$k]['num']             = Functions::getReplyNum($v['askid']);
                $replyInfo                  = Functions::getAskReplyInfo($v['askid']);
                $res[$k]['reply']           = $replyInfo  ? $replyInfo['reply'] : '';
                $res[$k]['add_time']        = $replyInfo  ? $replyInfo['add_time'] : 0;
                unset($replyInfo);
            }
        }else{
            $result['type'] = 2;//没有问题
            $selectSql      = "SELECT question as subject FROM {{%ask_question}} WHERE category_id = '$product_info[cate_id]' ORDER BY add_time DESC LIMIT $pageMin,$pageSize";
            $res         = Yii::$app->db->createCommand($selectSql)->queryAll();
        }
        $result['question_list'] = $res;
        $data = ['status' => 1, 'msg' => $result,'pageTotal' => $num, 'pageSize'=> $pageSize];
        return  json_encode($data);

    }

    /**
    * 问答列表
    * @para string $product_id    产品id
    * @return json 成功返回信息，失败返回错误
    */
    PUBLIC function myAskList()
    {
        $requiredParameter  = array('type');
        $this->completeParameter($requiredParameter);

        $type     = intval(self::$data['type']);
        $userId   = isset(self::$data['user_id'])       ? intval(self::$data['user_id'])   : 0; 
        $pageSize   = isset(self::$data['pageSize'])    ? intval(self::$data['pageSize'])   : 20;
        $page       = isset(self::$data['page'])        ? intval(self::$data['page'])       : 1;
        $pageMin    = ($page - 1) * $pageSize;
        $res = [];
        if($type == 1 || $type == 2){
            $whereStr = '';
            if($type == 2 && $userId){
                $whereStr .= " AND A.user_id = '$userId' ";
            }
            $sql_count  = "SELECT COUNT(*) AS num FROM {{%ask}} A WHERE A.status = 1 $whereStr";
            $num        = Yii::$app->db->createCommand($sql_count)->queryScalar();
            if($num > 0){
                $maxPage            = ceil($num/$pageSize);
                if($page > $maxPage){
                    return json_encode(['status' => 1, 'msg' => $res,'pageTotal' => $num, 'pageSize'=> $pageSize]);
                }
                $res = Functions::getAskList($whereStr,'t.add_time DESC',$page,$pageSize);
                foreach($res as $k=>$v){
                    if(empty($v['reply'])){
                        $res[$k]['is_like']     =  0 ;
                    }else{
                        $res[$k]['reply']         = Tools::userTextDecode($v['reply']);
                        //判断是否已点过赞
                        $selectSql   = "SELECT id FROM {{%ask_like}} WHERE user_id = '$userId' AND reply_id = '$v[askReplyId]'";
                        $isExsit     = Yii::$app->db->createCommand($selectSql)->queryScalar();
                        $res[$k]['is_like'] = $isExsit ? 1 : 0 ;
                    }
                    $res[$k]['subject']         = Tools::userTextDecode($v['subject']);
                }
            }
        }else{
            $requiredParameter  = array('user_id');
            $this->completeParameter($requiredParameter);
            $sql_count  = "SELECT COUNT(*) AS num FROM (SELECT A.askid FROM {{%ask_reply}} AR lEFT JOIN {{%ask}} A ON A.askid = AR.askid WHERE AR.user_id = '$userId' AND A.status = 1 ) t";
            $num        = Yii::$app->db->createCommand($sql_count)->queryScalar();
            //分页判断
            if($num > 0){
                $maxPage            = ceil($num/$pageSize);
                if($page > $maxPage){
                    return json_encode(['status' => 1, 'msg' => $res,'pageTotal' => $num, 'pageSize'=> $pageSize]);
                }
                $pageMin= ($page - 1) * $pageSize; 
                //'我的回答'列表:注意以下的主表是ask_reply。所以没有用上面的方法。
                $selectSql      = " SELECT A.askid,A.subject,AR.reply,AR.replyid,AR.like_num,A.add_time,PD.product_img FROM {{%ask_reply}} AR
                                    lEFT JOIN {{%ask}} A ON A.askid = AR.askid
                                    lEFT JOIN {{%product_details}} PD ON PD.id = A.product_id
                                    WHERE AR.user_id = '$userId' AND A.status = 1 ORDER BY AR.like_num DESC,AR.add_time DESC LIMIT $pageMin,$pageSize";
                $res         = Yii::$app->db->createCommand($selectSql)->queryAll();
                foreach($res as $k=>$v){
                    $res[$k]['product_img'] = Functions::get_image_path($v['product_img']);
                    $res[$k]['askReplyId']    = $v['replyid'] ? $v['replyid'] : 0;
                    $res[$k]['like_num']      = $v['like_num'] ? $v['like_num'] : 0;
                    $res[$k]['reply']         = $v['reply'] ? Tools::userTextDecode($v['reply']) :'';
                    $res[$k]['subject']       = Tools::userTextDecode($v['subject']);
                    unset($res[$k]['replyid']);
                }
            }
        }

        $data = ['status' => 1, 'msg' => $res,'pageTotal' => $num, 'pageSize'=> $pageSize];
        return  json_encode($data);

    }

    /**
    * 答案列表
    * @para string $product_id    产品id
    * @return json 成功返回信息，失败返回错误
    */
    PUBLIC function questionList()
    {
        $requiredParameter  = array('askid');
        $this->completeParameter($requiredParameter);

        $askid     = intval(self::$data['askid']);
        $userId    = isset(self::$data['user_id'])       ? intval(self::$data['user_id'])   : 0; 
        $pageSize   = isset(self::$data['pageSize'])    ? intval(self::$data['pageSize'])   : 20;
        $page       = isset(self::$data['page'])        ? intval(self::$data['page'])       : 1;
        $pageMin    = ($page - 1) * $pageSize;

        $res_sql  = "SELECT PD.id,PD.product_name,PD.product_img,A.subject FROM {{%ask}} A LEFT JOIN {{%product_details}} PD ON PD.id = A.product_id AND PD.status = 1 WHERE A.askid = '$askid' AND A.status = 1";
        $res_info        = Yii::$app->db->createCommand($res_sql)->queryOne();

        if(!$res_info['subject']){
            $data   = ['status' => -20, 'msg' =>self::$ERROR['-20']];
            return json_encode($data);
        }

        if(!$res_info['product_name']){
            $data   = ['status' => -8, 'msg' =>self::$ERROR['-8']];
            return json_encode($data);
        }
        
        $result['product_id']   = $res_info['id'];
        $result['product_name'] = $res_info['product_name'];
        $result['product_img']  = Functions::get_image_path($res_info['product_img']);
        $result['subject']      = Tools::userTextDecode($res_info['subject']);

        $sql_count  = "SELECT COUNT(*) AS num FROM {{%ask_reply}} WHERE askid = '$askid'";

        $num        = Yii::$app->db->createCommand($sql_count)->queryScalar();
        $res     = [];
        //分页判断
        if($num > 0){
            $maxPage            = ceil($num/$pageSize);
            if($page > $maxPage){
                return json_encode(['status' => 1, 'msg' => $res,'pageTotal' => $num, 'pageSize'=> $pageSize]);
            }
            $pageMin= ($page - 1) * $pageSize; 

            $selectSql      = "SELECT replyid,reply,user_id,add_time,like_num FROM {{%ask_reply}} WHERE askid = '$askid' ORDER BY like_num DESC,add_time DESC LIMIT $pageMin,$pageSize";
            $res         = Yii::$app->db->createCommand($selectSql)->queryAll();
            foreach($res as $k=>$v){
                $res[$k]['askReplyId']    = $v['replyid'] ? $v['replyid'] : 0;
                $res[$k]['reply']         = $v['reply'] ? Tools::userTextDecode($v['reply']) :'';
                $user_info = Functions::getUserInfo($v['user_id']);
                $res[$k]['user_info']['user_id'] = $user_info['id'];
                $res[$k]['user_info']['username'] = $user_info['username'];
                $res[$k]['user_info']['user_img'] = $user_info['img'];
                $res[$k]['user_info']['skin'] = $user_info['skin'];
                $res[$k]['user_info']['age'] = $user_info['age'];
                unset($user_info);
                unset($res[$k]['user_id']);
                unset($res[$k]['replyid']);
                if(empty($userId)){
                    $res[$k]['is_like']     =  0 ;
                }else{
                    //判断是否已点过赞
                    $selectSql   = "SELECT id FROM {{%ask_like}} WHERE user_id = '$userId' AND reply_id = '$v[replyid]'";
                    $isExsit     = Yii::$app->db->createCommand($selectSql)->queryScalar();
                    $res[$k]['is_like'] = $isExsit ? 1 : 0 ;
                }
            }
        }
        $result['question_list'] = $res;
        $data = ['status' => 1, 'msg' => $result,'pageTotal' => $num, 'pageSize'=> $pageSize];
        return  json_encode($data);

    }
    /**
    * 用户密码重置
    * @para string $mobile    用户手机号
    * @para string $captcha   短信验证码 
    * @para string $newPassword  修改成的值 
    * @return json 成功返回信息，失败返回错误
    */
    PUBLIC function resetPassword()
    {
        $requiredParameter  = array('mobile','captcha','newPassword');
        $this->completeParameter($requiredParameter);

        $mobile         = !empty(self::$data['mobile'])     ?  Functions::checkStr(self::$data['mobile']) :'' ;    
        $captcha        = !empty(self::$data['captcha'])    ?  Functions::checkStr(self::$data['captcha']) :'' ; 
        $type           = !empty(self::$data['type'])       ?  intval(self::$data['type']) : 1 ; 
        $newPassword    = Functions::checkStr(self::$data['newPassword']);

        // 验证手机号
        $isTel = Functions::isMoblie($mobile);

        if(!$isTel){
            $data = ['status' => -14, 'msg' => self::$ERROR['-14']];
            return json_encode($data);   
        }
        
        if ((mb_strlen($newPassword,"UTF-8")<6) || (mb_strlen($newPassword,"UTF-8")>15)) {
            $data = ['status' => -200, 'msg' => '密码字符长度6-15位'];
            return json_encode($data);
        }
        //验证验证码
        $isCaptcha   = Functions::checkCaptcha($mobile,$captcha,$type);
        
        if(!$isCaptcha) {
            $data = ['status' => '-12', 'msg' => self::$ERROR['-12']];
            return  json_encode($data);
        }

        if($type == 4){
            $userId    = intval(self::$data['user_id']);
            $sql    =   "UPDATE {{%user}} SET mobile = '$mobile' WHERE id = '$userId'";
            $return =   Yii::$app->db->createCommand($sql)->execute();
            // 添加注册送颜值
            Functions::updateMoney($userId,200,'注册',2);
        }

        $user = User::find()
              ->where('mobile = :mobile', [':mobile' => $mobile])
              ->one();
        if ($user) {
            $user->setPassword($newPassword);
            if ($user->save()) {
                Functions::useCaptcha($mobile,$captcha,$type);
                $data   = ['status' => '1', 'msg' => self::$ERROR['1']];
                if($type != 4){
                    //重新获取TOKEN
                    $params = ['user_id' => $user->id , 'referer' => self::$data['from']];
                    $token  = Functions::getToken($params);
                    $data['msg']   = ['token' => $token];
                }
            } else {
              $data = ['status' => 0, 'msg' => $user->getErrors()];
            }
        } else {
            $data = ['status' => -2, 'msg' => self::$ERROR['-2']];
        }
        return json_encode($data);
    }

    /**
    * 用户资料修改
    * @para int    $userId    用户ID
    * @para string $attribute 需要修改的属性
    * @para string $content   修改成的值 
    * @return json 成功返回信息，失败返回错误
    */
    PUBLIC function userUpdate()
    {   

        $requiredParameter  = array('attribute');
        $this->completeParameter($requiredParameter);

        $attribute  = !empty(self::$data['attribute'])  ?  Functions::checkStr(self::$data['attribute']) :'' ;
        $content    = !empty(self::$data['content'])    ?  Functions::checkStr(self::$data['content']) :'' ;
        
        $allowArr   = ['username','mobile','birthday','img','email','sex','constellation','province','city','marital_status','interest'];
        if(!in_array($attribute,$allowArr)){
            $data = ['status' => '-200', 'msg' => '不允许修改的属性'];
            return  json_encode($data);
        }
        //查询
        $userId     = isset(self::$data['user_id']) ? intval(self::$data['user_id']) : '';
        $userInfo   = (new \yii\db\Query())
              ->select('id')
              ->from('{{%user}}')
              ->where('id = :id', [':id' => $userId])
              ->one();
        if(!$userInfo){
            $data = ['status' => '-2', 'msg' => self::$ERROR['-2']];
            return  json_encode($data);
        } 
        //生日特殊处理
        switch ($attribute) {
            case 'birthday':
                $year   = date('Y',$content);
                $month  = date('m',$content);
                $day    = date('d',$content);
                $u_sql    = "birth_year = '$year',birth_month = '$month',birth_day = '$day'";
                break;
            case 'img':
                $u_sql    = "$attribute = '$content', img_state = 0,img_mtimes=1";
                break;
            case 'username':
                $content = Tools::userTextEncode($content,1); 
                $u_sql    = "$attribute = '$content'";
                break;       
            default:
                $u_sql    = "$attribute = '$content'";
                break;
        }
        $sql    = "UPDATE {{%user}} SET ".$u_sql." WHERE id = '$userId'"; 
        //执行修改语句
        $return    =  Yii::$app->db->createCommand($sql)->execute();
        if($return && $attribute == 'username'){
            Functions::updateUserName($userId);
        }
        $data = ['status' => 1, 'msg' => self::$ERROR['1']];
        return  json_encode($data);
    }

    /*
    *用户的在用列表
    * @para int    $userId    用户ID
    **/
    PUBLIC function userProduct(){
        $userId     = intval(self::$data['user_id']);
        $type       = self::$data['type']     ? intval(self::$data['type']) : 0;
        $pageSize   = self::$data['pageSize'] ? intval(self::$data['pageSize']) : 10;
        $page       = self::$data['page']     ? intval(self::$data['page']) : 1;

        $time       = strtotime(date('Y-m-d'));
        $whereStr   = " user_id = '$userId' ";
        switch ($type) {
            case '1':
                $whereStr .= " AND  is_seal = '1' AND  expire_time >=  '$time' ";
                break;
            case '2':
                $whereStr .= " AND  is_seal = '0' AND  expire_time >=  '$time' ";
                break;
            case '3':
                $whereStr .= " AND  expire_time <  '$time' ";
                break;
        }

        $count_sql  = "SELECT COUNT(*) FROM {{%user_product}}  WHERE $whereStr";
        $num        = Yii::$app->db->createCommand($count_sql)->queryScalar();
        $result     = [];
        //分页判断
        if($num > 0){
            $maxPage   = ceil($num/$pageSize);
            if($page > $maxPage){
                return json_encode(['status' => 1, 'msg' => '']);
            }
            $pageMin   = ($page - 1) * $pageSize;

            $selectSql = "SELECT * FROM {{%user_product}}  WHERE $whereStr ORDER BY add_time DESC LIMIT $pageMin,$pageSize";
            $result    = Yii::$app->db->createCommand($selectSql)->queryAll();
            foreach ($result as $key => $value) {
                $result[$key]['img']  = Functions::get_image_path($value['img']);
            }
        }
        //消息设为已读
        $readSql = "UPDATE {{%notice_user}} SET status = '1' WHERE user_id = '$userId' AND type = '2'";
        Yii::$app->db->createCommand($readSql)->execute();

        $data = ['status' => 1, 'msg' => $result,'pageTotal' => $num, 'pageSize'=> $pageSize];
        return  json_encode($data);
    }
    /*
    *操作用在用列表
    * @para int    $userId    用户ID
    **/
    PUBLIC function  operateUserproduct(){
        $requiredParameter  = array('id','type');
        $this->completeParameter($requiredParameter);

        $Id         = !empty(self::$data['id'])         ? intval(self::$data['id']) : 0 ;
        $type       = !empty(self::$data['type'])       ? intval(self::$data['type']) : 1 ;
        $time       = time();
        $data       = ['status' => 1, 'msg' => self::$ERROR['1']];
        if($type == 1){
            $sql = "UPDATE {{%user_product}} SET is_seal = 1,seal_time = '$time' WHERE id = '$Id'";
        }else{
            $sql = "DELETE FROM {{%user_product}} WHERE id = '$Id'";
        }
        
        $res      = Yii::$app->db->createCommand($sql)->execute();
        if(!$res){
            $data = ['status' => 0, 'msg' => self::$ERROR['0']];
        }
        
        return  json_encode($data);
    }
    /**
    * 大家都在搜接口
    * @para int    $pageSize    条数
    * @return json 成功返回信息，失败返回错误
    */
    PUBLIC function searchHot(){
        $pageSize   = !empty(self::$data['pageSize'])     ? intval(self::$data['pageSize']) : 10;
        $page       = !empty(self::$data['page'])         ? intval(self::$data['page']) : 1;

        $count_sql  = "SELECT COUNT(*) AS num FROM {{%hot_keyword}}";
        $num      = Yii::$app->db->createCommand($count_sql)->queryScalar();
        //分页判断
        $maxPage            = ceil($num/$pageSize);
        if($page > $maxPage){
            return json_encode(['status' => 1, 'msg' => '']);
        }
        $pageMin= ($page - 1) * $pageSize;

        $selectSql      = "SELECT keyword as name,num FROM {{%hot_keyword}}  ORDER BY num DESC LIMIT $pageMin,$pageSize";
        $result         = Yii::$app->db->createCommand($selectSql)->queryAll();
        $data = ['status' => 1, 'msg' => $result,'pageTotal' => $num, 'pageSize'=> $pageSize];
        return  json_encode($data);
    }

    /**
    * 搜索联想接口
    * @para int    $keywords    关键字
    * @return json 成功返回信息，失败返回错误
    */
    PUBLIC function searchAssociate(){
        $requiredParameter  = array('keywords');
        $this->completeParameter($requiredParameter);

        $keywords   = Functions::checkStr(self::$data['keywords']);

        $data = searchProduct::associative($keywords,10);
        return  json_encode($data);
    }

    /**
    * 搜索查询接口
    * * @para string    $keywords    关键字
    * * @para int       $type       类型
    * * @para int       $cate_id    分类id
    * * @para string    $effect     功效关键字
    * * @para int       $pageSize   每页页数
    * * @para int       $page       页数
    * 
    * @return json 成功返回信息，失败返回错误
    */
    PUBLIC function searchQuery(){
        $keyword    = !empty(self::$data['keywords'])   ? Functions::checkStr(self::$data['keywords']) :  '' ;//关键字搜索可为空
        $cateId     = !empty(self::$data['cate_id'])    ? intval(self::$data['cate_id']) : 0 ;
        $effectId   = !empty(self::$data['effect'])     ? intval(self::$data['effect']) :   '' ;
        $pageSize   = !empty(self::$data['pageSize'])   ? intval(self::$data['pageSize']) : 20;
        $page       = !empty(self::$data['page'])       ? intval(self::$data['page']) : 1;
        
        $param = [
            "page"      => $page,
            "search"    => $keyword,
            "categroyId" => $cateId,
            "effect"    => $effectId,
            "pageSize"  => $pageSize
        ];
        //产品列表
        $data = searchProduct::searchProduct($param);
        $rankinfo = Functions::getRankCategory($cateId,$keyword,0,5);
        $categoryList = Functions::getProductColumn();

        //判断分类除了面部护肤及以下二级分类外都没有功效的数据
        if($cateId){
            $sql = "SELECT cate_name,parent_id FROM {{%product_category}} WHERE id = {$cateId}";
            $category = Yii::$app->db->createCommand($sql)->queryOne();
            if($category['parent_id'] == 0){
                $effectList = $category['cate_name'] == '面部护肤' ? Functions::effectList() : [];
            }else{
                $secondSql = "SELECT cate_name FROM {{%product_category}} WHERE id = {$category['parent_id']}";
                $cateName = Yii::$app->db->createCommand($secondSql)->queryScalar();
                $effectList = $cateName == '面部护肤' ? Functions::effectList() : [];
            }
        }else{
            $effectList = Functions::effectList();
        }

        if($data['status'] != 1) return json_encode(['status' => 1,'msg' => ['categoryList'=>$categoryList,'effectList'=>$effectList,'rank'=>$rankinfo,'brand' => (object)[],'product' => []],'pageTotal' => 0 , 'page'=>$page]);

        $return = [
            'status'    =>  '1',
            'msg'       =>  ['brand' => $data['brand'],'product' => $data['data'],'rank'=>$rankinfo]
        ];

        $return['page']                 = $data['page'];
        $return['pageTotal']            = $data['pageTotal'];
        $return['msg']['categoryList']  = $categoryList;
        $return['msg']['effectList']    = $effectList;
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
        return  json_encode($return);
    }
    /*****************************************************************************************************************************/
                                /*1.1.1版本新加接口 */
    /*****************************************************************************************************************************/
    /*
     *验证用户是否绑定手机 
     */
    PUBLIC function isBind(){
        $user_id    = Functions::checkStr(self::$data['user_id']);

        $selectSql  = "SELECT mobile FROM {{%user}}  WHERE id = $user_id";
        $mobile     = Yii::$app->db->createCommand($selectSql)->queryScalar();
        $type     = Functions::isMoblie($mobile) ? 1 : 2;
        
        $data       = ['status' => 1, 'msg' => $type];
        return  json_encode($data);
    }

    /*
     *排行榜详情页
     */
    PUBLIC function RankingInfo(){
        $requiredParameter  = array('rank_id');
        $this->completeParameter($requiredParameter);

        $rank_id    = intval(self::$data['rank_id']);
        $RankInfo   = Functions::getRankingInfo($rank_id);
        $RankInfo['rankingInfo']['share_url']   = Yii::$app->params['mfrontendUrl'].'app/ranking?id='.$rank_id;
        $RankInfo['relation_info'] = Functions::getRankCategory(0,0,$rank_id,3);
        
        $data       = ['status' => 1, 'msg' => $RankInfo];
        return  json_encode($data);
    }

    /*
     *护肤百科列表页
     */
    PUBLIC function BaikeList(){
        $pageSize   = isset(self::$data['pageSize']) ? intval(self::$data['pageSize']) : 10;
        $page       = isset(self::$data['page']) ? intval(self::$data['page']) : 1;
        $pageMin    = ($page - 1) * $pageSize;

        $sql_count  = "SELECT COUNT(*) AS num FROM {{%skin_baike}} ";
        $num        = Yii::$app->db->createCommand($sql_count)->queryScalar();
        $res     = [];
        //分页判断
        if($num > 0){
            $maxPage            = ceil($num/$pageSize);
            if($page > $maxPage){
                return json_encode(['status' => 1, 'msg' => $res,'pageTotal' => $num, 'pageSize'=> $pageSize]);
            }
        }
        $pageinfo = ['pageMin'=>$pageMin,'pageSize'=>$pageSize];
        $res   = Functions::getbaikeList(0,false,$pageinfo);
        foreach ($res as $key => $value) {
            $res[$key]['picture'] = Functions::get_image_path($value['picture']);
            $res[$key]['content'] = $res[$key]['shortcontent'];
            unset($res[$key]['shortcontent']);
        }
        $data       = ['status' => 1, 'msg' => $res,'pageTotal' => $num, 'pageSize'=> $pageSize];
        return  json_encode($data);
    }
    /*
     *护肤百科详情页
     */
    PUBLIC function BaikeInfo(){
        $requiredParameter  = array('baike_id');
        $this->completeParameter($requiredParameter);

        $baike_id    = intval(self::$data['baike_id']);
        $userId    = intval(self::$data['user_id']);
        $baikeInfo   = Functions::getbaikeInfo($baike_id);
        $baikeInfo['share_url']   = Yii::$app->params['mfrontendUrl'].'app/baike?id='.$baike_id;
        $baikeInfo['relation_info'] = Functions::getBaikeRelation($baike_id);
        if(isset($baikeInfo['shortcontent'])){
            unset($baikeInfo['shortcontent']);
        }
        if(isset($baikeInfo['picture'])){
            $baikeInfo['picture']   = Functions::get_image_path($baikeInfo['picture']);
        }
        if($userId){
            $selectSql   = "SELECT id FROM {{%baike_like}} WHERE user_id = '$userId' AND baike_id = '$baike_id'";
            $isExsit     = Yii::$app->db->createCommand($selectSql)->queryScalar();
            $baikeInfo['is_like'] = $isExsit ? 1 : 0 ;
        }else{
            $baikeInfo['is_like'] = 0;
        }
            
        $data       = ['status' => 1, 'msg' => $baikeInfo];
        return  json_encode($data);
    }


    /*****************************************************************************************************************************/
    /*1.2.0版本新加接口
      from 杨艳琴
    */
    /*****************************************************************************************************************************/

    /**
     * 产品库首页
     * @return json
     */
    PUBLIC function productLibrary(){
        //获取产品总数量
        $productSql = "SELECT count(*) From {{%product_details}} WHERE status = 1";
        $data['sum'] = $categoryList = Yii::$app->db->createCommand($productSql)->queryScalar();

        //获取分类列表
        $oneCategorySql = "SELECT id FROM {{%product_category}}
                       WHERE status = 1 AND parent_id = 0 ORDER BY sort ASC";
        $parentId = Yii::$app->db->createCommand($oneCategorySql)->queryScalar();

        $categorySql = "SELECT id, cate_name, cate_app_img FROM {{%product_category}}
                       WHERE status = 1 AND parent_id = {$parentId} ORDER BY sort ASC LIMIT 7";
        $categoryList = Yii::$app->db->createCommand($categorySql)->queryAll();
        foreach ($categoryList as $key=>$category){
            $categoryList[$key]['cate_app_img'] = Functions::get_image_path($category['cate_app_img'],1);
        }
        $data['categoryList'] = $categoryList ? $categoryList : [];

        //获取品牌列表
        $brandSql = "SELECT id, name, ename, img FROM {{%brand}}
                    WHERE status = 1 AND is_recommend = 1 ORDER BY retime DESC LIMIT 7";
        $brandList = Yii::$app->db->createCommand($brandSql)->queryAll();
        foreach ($brandList as $key=>$brand){
            $brandList[$key]['img'] = Functions::get_image_path($brand['img'],1);
        }
        $data['brandList'] = $brandList ? $brandList : [];

        //获取排行榜列表
        $rankingList = Functions::getRankIndex(3);
        $data['rankingList'] = $rankingList ? $rankingList : [];

        return json_encode(['status' => 1, 'msg' => $data]);
    }

    /**
     * 产品分类
     * @return json
     */
    PUBLIC function productCategory(){
        $category = Functions::getProductColumn();

        return json_encode(['status' => 1, 'msg' => $category]);
    }

    /**
     * 排行榜首页
     * @return json
     */
    PUBLIC function rankingIndex(){
        $pageSize = !empty(self::$data['pageSize']) ? intval(self::$data['pageSize']) : 10;
        $page = !empty(self::$data['page']) ? intval(self::$data['page']) : 0;

        //获取排行榜数据
        $rankingList = Functions::getRankIndex();

        //分页
        $dataProvider= new ArrayDataProvider([
            'allModels' => $rankingList,
            'pagination' => [
                'pageSize' => $pageSize,
                'page' => $page - 1
            ],
        ]);

        return json_encode(['status' => 1, 'msg' => array_values($dataProvider->getModels()) ,
            'pageTotal' => $dataProvider->getTotalCount(), 'pageSize'=> $pageSize]);

    }

    /**
     * 免费福利页面
     * @return json
     */
    PUBLIC function freeBenefits(){
        $pageSize   = isset(self::$data['pageSize']) ? intval(self::$data['pageSize']) : 10;
        $page       = isset(self::$data['page']) ? intval(self::$data['page']) : 1;
        $pageMin    = ($page - 1) * $pageSize;

        //统计总数
        $sqlCount        = "SELECT COUNT(*) AS num FROM {{%huodong_special_config}} WHERE status = 1";
        $num        = Yii::$app->db->createCommand($sqlCount)->queryScalar();

        $sql = "SELECT id, picture, type, prize_num, prize, relation, starttime, endtime FROM {{%huodong_special_config}}
                WHERE status = 1 ORDER BY starttime DESC LIMIT $pageMin, $pageSize";
        $activityList = Yii::$app->db->createCommand($sql)->queryAll();

        foreach ($activityList as $key=>$activity){
            $activityList[$key]['picture'] = Functions::get_image_path($activity['picture'],1);
            $activityList[$key]['current_time'] = time();
        }

        return json_encode(['status' => 1, 'msg' => $activityList, 'pageTotal' => $num, 'pageSize'=> $pageSize]);
    }

    /**
    * 答案点赞功能接口
    * @para string $sign      校验码
    * @para int    $time      时间
    * @para int    $askReplyId 答案评论ID
    * @para int    $userId    用户ID
    * @return json 成功返回信息，失败返回错误
    */
    PUBLIC function addAskLike()
    {
        //验证
        $requiredParameter  = array('askReplyId','user_id');
        $this->completeParameter($requiredParameter);

        //查询
        $time       = time();
        $userId     = intval(self::$data['user_id']);
        $askReplyId  = intval(self::$data['askReplyId']);
        $referer    = strtolower(self::$data['from']) == 'ios' ? 'ios' : 'android';
        //判断帐号是否正常
        $this->checkUserStatus($userId);

        //判断评论是否存在
        $infoSql     = "SELECT user_id,askid FROM {{%ask_reply}} WHERE replyid = '$askReplyId'";
        $replyInfo = Yii::$app->db->createCommand($infoSql)->queryOne();

        if(!$replyInfo){
            $return =  ['status' => -15, 'msg' => self::$ERROR['-15']];
            echo json_encode($return);die;
        }
        //判断是否已点过赞
        $selectSql   = "SELECT id FROM {{%ask_like}} WHERE user_id = '$userId' AND reply_id = '$askReplyId'";
        $isExsit     = Yii::$app->db->createCommand($selectSql)->queryScalar();

        if($isExsit){
            $sql    = "DELETE  FROM {{%ask_like}} WHERE id = '$isExsit'"; 
            $rows   = Yii::$app->db->createCommand($sql)->execute();
        }else{

            $sql    = "INSERT INTO {{%ask_like}} (user_id,ask_id,reply_id,referer,created_at,updated_at) 
                        VALUES('$userId','$replyInfo[askid]','$askReplyId','$referer','$time','$time')";
            $rows   = Yii::$app->db->createCommand($sql)->execute();
          
        }
        
        //修改点赞数
        $selectSql   = "SELECT COUNT(*)  FROM {{%ask_like}}  WHERE  reply_id = '$askReplyId'";
        $num     = Yii::$app->db->createCommand($selectSql)->queryScalar();
        if($num == 11){
            Functions::updateMoney($replyInfo['user_id'],20,'答案点赞',2);
        }
        $updateSql  = "UPDATE {{%ask_reply}} SET like_num = '$num' WHERE replyid = '$askReplyId'";
        Yii::$app->db->createCommand($updateSql)->execute();

        if($userId != $replyInfo['user_id']) {
            //点赞消息
            ReplyFunctions::reply($userId,$replyInfo['askid'],ReplyFunctions::$USER_ASK_REPLY_LIKE,'',$replyInfo['user_id']);
        }


        if($rows){
            $data = ['status' => 1, 'msg' => self::$ERROR['1']];
        }else{
            $data = ['status' => -200, 'msg' => self::$ERROR['0']];
        }
        
        return  json_encode($data);
    }
    /**
    * 百科点赞功能接口
    * @para string $sign      校验码
    * @para int    $time      时间
    * @para int    $askReplyId 答案评论ID
    * @para int    $userId    用户ID
    * @return json 成功返回信息，失败返回错误
    */
    PUBLIC function addBaikeLike()
    {
        //验证
        $requiredParameter  = array('baike_id','user_id');
        $this->completeParameter($requiredParameter);

        //查询
        $time       = time();
        $userId     = intval(self::$data['user_id']);
        $baikeId  = intval(self::$data['baike_id']);
        $referer    = strtolower(self::$data['from']) == 'ios' ? 'ios' : 'android';
        //判断帐号是否正常
        $this->checkUserStatus($userId);

        //判断评论是否存在
        $infoSql     = "SELECT id FROM {{%skin_baike}} WHERE id = '$baikeId'";
        $baikeInfo = Yii::$app->db->createCommand($infoSql)->queryOne();

        if(!$baikeInfo){
            $return =  ['status' => -23, 'msg' => self::$ERROR['-23']];
            echo json_encode($return);die;
        }
        //判断是否已点过赞
        $selectSql   = "SELECT id FROM {{%baike_like}} WHERE user_id = '$userId' AND baike_id = '$baikeId'";
        $isExsit     = Yii::$app->db->createCommand($selectSql)->queryScalar();

        if($isExsit){
            $sql    = "DELETE  FROM {{%baike_like}} WHERE id = '$isExsit'"; 
            $rows   = Yii::$app->db->createCommand($sql)->execute();
        }else{

            $sql    = "INSERT INTO {{%baike_like}} (user_id,baike_id,referer,created_at,updated_at) 
                        VALUES('$userId','$baikeId','$referer','$time','$time')";
            $rows   = Yii::$app->db->createCommand($sql)->execute();
          
        }
        
        //修改点赞数
        $selectSql   = "SELECT COUNT(*)  FROM {{%baike_like}}  WHERE  baike_id = '$baikeId'";
        $num     = Yii::$app->db->createCommand($selectSql)->queryScalar();

        $updateSql  = "UPDATE {{%skin_baike}} SET like_num = '$num' WHERE id = '$baikeId'";
        Yii::$app->db->createCommand($updateSql)->execute();

        if($rows){
            $data = ['status' => 1, 'msg' => self::$ERROR['1']];
        }else{
            $data = ['status' => -200, 'msg' => self::$ERROR['0']];
        }
        
        return  json_encode($data);
    }
    /**
    * 发现页面接口
    * @para int    $userId    用户ID
    * @return json 成功返回信息，失败返回错误
    */
    PUBLIC function discovery()
    {
        $userId   = isset(self::$data['user_id'])       ? intval(self::$data['user_id'])   : 0;
        $return = ['banner' => [],'ask' => [],'baike' => []];
        $return['userSkin'] = [
                'dry'       =>['name'=>'干/油','letter'=>''],
                'tolerance' => ['name'=>'敏感度','letter'=>''],
                'pigment'   =>['name'=>'色素性','letter'=>''],
                'compact'   =>['name'=>'皱纹性','letter'=>''],
            ];
        //banner图
        $return['banner']   = Functions::getBannerList(2);
        $id_str = '';
        //用户肤质
        if($userId){
            $id_str = " AND A.user_id != '$userId' ";
            $userInfo= skin::getUserTestSkin($userId);
            if($userInfo){
                foreach ($userInfo as $key => $value) {
                    $return['userSkin'][$value['type']]['name']       = $value['name'];
                    $return['userSkin'][$value['type']]['letter']     = $value['letter'];
                }
            }
        } 
        $return['userSkin'] = array_values($return['userSkin']);

        //问题(判断有无点赞在外部判断)
        $return['ask']         = Functions::getAskList($id_str,'t.add_time DESC',1,3);
        foreach ($return['ask'] as $key => $value) {
            $return['ask'][$key]['reply'] = Tools::userTextDecode($value['reply']);
        }
        
        //百科
        $pageinfo = ['pageMin'=>0,'pageSize'=>3];
        $return['baike']   = Functions::getbaikeList(0,true,$pageinfo);
        foreach ($return['baike'] as $key => $value) {
            $return['baike'][$key]['picture'] = Functions::get_image_path($value['picture']);
            $return['baike'][$key]['content'] = $return['baike'][$key]['shortcontent'];
            unset($return['baike'][$key]['shortcontent']);

        }
        
        $data = ['status' => 1, 'msg' => $return];
        return  json_encode($data);
    }

    
}
