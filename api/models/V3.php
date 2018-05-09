<?php
namespace api\models;

use Yii;
use yii\base\Model;
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
/**
 * APP接口类
 */
class V3 extends Model{  
    //解释后参数
    static $data          =   [];
    //化妆品功效
    static $effects       =   ['美白','保湿','舒缓抗敏','去角质','去黑头'];
    //用户信息
    static $userInfo      =   [];
    //错误码
    static $ERROR         =   array(
        '0'     =>  '处理失败',
        '1'     =>  '处理成功',
        '-1'    =>  '帐号或密码错误',
        '-2'    =>  '账户不存在',
        '-3'    =>  '参数不完整,缺少 %s 参数',
        '-4'    =>  '注册失败',
        '-5'    =>  '%s 方法未定义',
        '-6'    =>  'TOKEN错误',
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
        '-200'  =>  '其他错误'
    );
    /**
     * [__construct 构造函数]
     * @param [type] $data [description]
     */
    PUBLIC function __construct($data) {
        self::$data = $data;
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

        $return = ['banner' => [],'category' => [],'num' => 0 ,'article' => []];
        $time   = time();
        //查询轮播
        $sql = "SELECT id, title, img, url,type,relation_id FROM {{%banner}} WHERE  status = '1' AND start_time < '$time' AND end_time > '$time'  ORDER BY sort_id ASC,start_time DESC";

        $return['banner']   = Yii::$app->db->createCommand($sql)->queryAll();
        if(!empty($return['banner'])){
            foreach ($return['banner'] as $key => $value) {
                if($value['type'] == 3){
                    $articleInfo = Functions::getArticle($uid,$value['relation_id']);
                    $return['banner'][$key]['comment_num']  = $articleInfo['comment_num'];
                    $return['banner'][$key]['linkUrl']      = $articleInfo['linkUrl'];
                    $return['banner'][$key]['isGras']       = $articleInfo['isGras'];
                    unset($articleInfo);
                }
                $return['banner'][$key]['img']  = Functions::get_image_path($value['img']);
            }
        }
        //查询分类
        $sql = "SELECT id,cate_name,cate_app_img AS cate_img FROM {{%product_category}} WHERE  status = '1'  ORDER BY sort DESC LIMIT 8";
        $return['category'] = Yii::$app->db->createCommand($sql)->queryAll();
        if(!empty($return['category'])){
            foreach ($return['category'] as $key => $value) {
                $return['category'][$key]['cate_img']  = Functions::get_image_path($value['cate_img'],1);
            }
        }
        //查询收集的产品数量
        $sql = "SELECT COUNT(*) AS num FROM {{%product_details}} WHERE  status = '1'";
        $return['num']    = Yii::$app->db->createCommand($sql)->queryScalar();

        //首页文章
        $sql    = "SELECT COUNT(*) AS num FROM {{%article}}  WHERE status = '1'  AND is_recommend = '1'";
        $num    = Yii::$app->db->createCommand($sql)->queryScalar();

        $sql    =   "SELECT id,title,article_img,like_num,comment_num,created_at 
                    FROM {{%article}} WHERE status = '1'  AND is_recommend = '1' 
                    ORDER BY  id DESC
                    LIMIT 20";
        $return['article']   = Yii::$app->db->createCommand($sql)->queryAll();

        if(!empty($return['article'])){
            foreach ($return['article'] as $key => $value) {
                $return['article'][$key]['article_img']  = Functions::get_image_path($value['article_img']);
                $return['article'][$key]['created_at']   = Tools::HtmlDate($value['created_at']);
                $return['article'][$key]['linkUrl']      = Yii::$app->params['mfrontendUrl'].'article/index?id='.$value['id'].'&isNew=0&from='.self::$data['from'];
                $return['article'][$key]['isGras']       = Functions::userIsGras($uid,$value['id'],2) ? 1 : 0;
            }
        }
        $data = ['status' => 1, 'msg' => $return,'pageTotal' => $num, 'pageSize'=> 20];
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
        $pageSize   = 20;//isset(self::$data['pageSize']) ? intval(self::$data['pageSize']) : 20;
        $page       = isset(self::$data['page']) ? intval(self::$data['page']) : 1;
        
        
        //先搜索字符
        if($brandId){
            $sql    = "SELECT name  FROM {{%brand}} WHERE id = '$brandId'";
            $name   = Yii::$app->db->createCommand($sql)->queryScalar();
            if(!$name){
                return json_encode(['status' => -19, 'msg' => self::$ERROR[-19]]);
            }
            $query          = new Query();
            $articleRows    = $query->select('id')->from('article')->match($name)->limit(1000)->all();

            $sql    = "SELECT id  FROM {{%article}} WHERE  find_in_set('$brandId',brand_id) ";
            $articleBrand   = Yii::$app->db->createCommand($sql)->queryAll();
            if(!$articleRows && !$articleBrand) return json_encode(['status' => 1 ,'data' => [],'page'=>$page,'pageTotal'=>0]);
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

        $whereStr   = " status = '1'";
        $whereStr  .= $brandId   ? '' : ' AND is_recommend = 1 ';
        $whereStr  .=  $brandId  ? ' AND '.$idStr : '';

        /* 分页 */
        $rows   = array();
        $sql    = "SELECT COUNT(*) AS num FROM {{%article}} WHERE $whereStr";
        $num    = Yii::$app->db->createCommand($sql)->queryScalar();
        //分页判断
        // $maxPage = ceil($num/$pageSize);
        // $page > $maxPage  ? $page = $maxPage  : '';
        $pageMin= ($page - 1) * $pageSize;

        $sql    = "SELECT id,title,article_img,like_num,comment_num,created_at 
                  FROM {{%article}} 
                  WHERE $whereStr 
                  ORDER BY  id DESC 
                  LIMIT $pageMin,$pageSize";
        $rows   = Yii::$app->db->createCommand($sql)->queryAll();

        if(!empty($rows)){
            foreach ($rows as $key => $value) {
                $rows[$key]['article_img']  = Functions::get_image_path($value['article_img'],1);
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
        $whereStr   = " C.type = '2' AND C.post_id = '$id' AND C.status = '1' ";
        //增加点击数
        $updateSql    = "UPDATE  {{%article}} SET click_num = click_num + 1 WHERE id ='$id'";
        Yii::$app->db->createCommand($updateSql)->execute();

        /* 分页 */
        $num        = 0;
        $fieldStr   = 'C.like_num DESC ';
        $commentSql = "SELECT C.id,C.user_id,C.comment,C.like_num,U.username,U.img,U.birth_year,S.dry,S.tolerance,S.pigment,S.compact 
                    FROM {{%comment}} C 
                    LEFT JOIN {{%user}} U ON C.user_id = U.id 
                    LEFT JOIN {{%user_skin}} S ON C.user_id = S.uid
                    WHERE $whereStr ORDER BY  C.created_at DESC LIMIT 20";

        $commentList   = Yii::$app->db->createCommand($commentSql)->queryAll();

        if(!empty($commentList)){
            foreach ($commentList as $key => $value) {
                                
                $skinInfo = [
                    'dry'       => isset($value['dry']) ? $value['dry'] : '',
                    'tolerance' => isset($value['tolerance']) ? $value['tolerance'] : '',
                    'pigment'   => isset($value['pigment']) ? $value['pigment'] :'',
                    'compact'   => isset($value['compact'])  ? $value['compact'] : ''
                ];
                $commentList[$key]['skin']    = '';
                $commentList[$key]['username']= Tools::userTextDecode($value['username']);
                $commentList[$key]['comment'] = Tools::userTextDecode($value['comment']);
                $commentList[$key]['age']     = isset($value['birth_year'])  ? Functions::getUserAge($value['birth_year']) : '';
                $commentList[$key]['img']     = isset($value['img']) ? Functions::get_image_path($value['img'],1) : '';
                $commentList[$key]['isLike']  = Functions::userIsLike($uid,$value['id']) ? 1 : 0;

                $userSkin   = Skin::evaluateSkin($skinInfo);

                if($userSkin){
                    unset($userSkin['skin_name']);
                    $commentList[$key]['skin'] = join(' | ',$userSkin);
                }

                unset($commentList[$key]['birth_year']);
                unset($commentList[$key]['dry']);
                unset($commentList[$key]['tolerance']);
                unset($commentList[$key]['pigment']);
                unset($commentList[$key]['compact']);
                unset($skinInfo);
                unset($userSkin);
            }
        }
        $rows['commentList']  = $commentList;
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
        $whereStr   = " C.type = '$type' AND C.post_id = '$id' AND C.status = '1' ";

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
        $fieldStr   = 'C.like_num DESC ';
        $sql        = "SELECT COUNT(*) AS num  FROM {{%comment}}  C  WHERE $whereStr";
        $num        = Yii::$app->db->createCommand($sql)->queryScalar();

        if($orderBy == 'skin'){
            // $whereStr .= " AND S.skin_name != ''";
            // $sql        = "SELECT COUNT(*) AS num ,skin_name FROM {{%comment}}  C LEFT JOIN {{%user_skin}} S  ON C.user_id = S.uid WHERE $whereStr GROUP BY S.skin_name ORDER BY num DESC";
            // $fieldAll   = Yii::$app->db->createCommand($sql)->queryAll();
            // $str        = '';
            // foreach ($fieldAll as $k => $v) {
            //     $num += $v['num'];
            //     $str .= $str ? ' ,\''.$v['skin_name'].'\'' : ' \''.$v['skin_name'].'\'';
            // }
            // $fieldStr   = $str ? ' field(skin_name,'.$str.')' : $fieldStr;
            $userSkin   = Skin::getUserSkin($uid);
            $skin_name  = $userSkin['skin_name'];
            $fieldStr   = $skin_name ? " if(S.skin_name ='$skin_name',0,1)" : $fieldStr;
        }

        $sql    = "SELECT C.id,C.user_id,C.comment,C.like_num,C.is_digest,U.username,U.img,U.birth_year,S.dry,S.tolerance,S.pigment,S.compact FROM {{%comment}} C 
                   LEFT JOIN {{%user}} U ON C.user_id = U.id 
                   LEFT JOIN {{%user_skin}} S ON C.user_id = S.uid
                   WHERE $whereStr ORDER BY  $fieldStr ,C.created_at DESC LIMIT $pageMin,$pageSize";
        $rows   = Yii::$app->db->createCommand($sql)->queryAll();

        if(!empty($rows)){
            foreach ($rows as $key => $value) {
                $skinInfo = [
                    'dry'       => $value['dry'],
                    'tolerance' => $value['tolerance'],
                    'pigment'   => $value['pigment'],
                    'compact'   => $value['compact']
                ];
                $rows[$key]['skin']    = '';
                $rows[$key]['username']= Tools::userTextDecode($value['username']);
                $rows[$key]['comment'] = Tools::userTextDecode($value['comment']);
                $rows[$key]['age']     = Functions::getUserAge($value['birth_year']);
                $rows[$key]['img']     = Functions::get_image_path($value['img'],1);
                $rows[$key]['isLike']  = Functions::userIsLike($uid,$value['id']) ? 1 : 0;

                $userSkin   = Skin::evaluateSkin($skinInfo);

                if($userSkin){
                    unset($userSkin['skin_name']);
                    $rows[$key]['skin'] = join(" | ",$userSkin);
                }

                unset($rows[$key]['birth_year']);
                unset($rows[$key]['dry']);
                unset($rows[$key]['tolerance']);
                unset($rows[$key]['pigment']);
                unset($rows[$key]['compact']);
                unset($skinInfo);
                unset($userSkin);
            }
        }

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
        $requiredParameter  = array('user_id','type','id','comment');
        $this->completeParameter($requiredParameter);

        $id       = intval(self::$data['id']);
        $userId   = intval(self::$data['user_id']);
        $type     = intval(self::$data['type']) == 1 ? 1 : 2;
        $star     = isset(self::$data['star']) ? intval(self::$data['star']) : '0';
        $comment  = Functions::checkStr(self::$data['comment']);
        $comment  = Tools::userTextEncode($comment,1);
        $referer  = strtolower(self::$data['from']) == 'ios' ? 'ios' : 'android';
        $time     = time();

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
        $author   = self::$userInfo['username']; 
        $adminId  = self::$userInfo['admin_id'];

        //入库
        $sql      = "INSERT INTO {{%comment}} (type,user_id,post_id,admin_id,author,star,comment,referer,created_at,updated_at) 
                     VALUES ('$type','$userId','$id','$adminId,','$author','$star','$comment','$referer','$time','$time')"; 

        $return   = Yii::$app->db->createCommand($sql)->execute();
        $newcommentId =  Yii::$app->db->getLastInsertId();
        if($return){
            //更新评论数
            switch ($type) {
                case '1':
                    $tableName = '{{%product_details}}';
                    Functions::updateMoney($userId,10,'点评产品',2);
                    break;
                case '2':
                    $tableName = '{{%article}}';
                    break;
            }
            
            $updateSql  = "UPDATE $tableName P SET P.comment_num = (SELECT COUNT(*)  FROM {{%comment}} C WHERE type = '$type' AND first_id = '0' AND post_id = '$id' AND C.status = 1)  WHERE P.id = '$id'";
            Yii::$app->db->createCommand($updateSql)->execute();
            //判断是否是活动
            Huodong::checkCosmetics($id,$type,$userId,$newcommentId);
            $data = ['status' => 1, 'msg' => self::$ERROR['1'],'commentId' => $newcommentId];
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
            $return = ['status' => -1, 'msg' => '品牌规则不存在'];
            return  json_encode($return);
        }

        $fun    = 'rule'.$rule;
        $data   = Cosmetics::$fun($number);

        $sql = "UPDATE {{%cosmetics_tool}} SET num = num + 1 WHERE id = '$id'";
        Yii::$app->db->createCommand($sql)->execute();

        if($data && $data['status'] == 1){
            $data['msg'] = ['startDay' => $data['startDay'], 'endDay'=> $data['endDay']];
            unset($data['startDay'],$data['endDay']);
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
        $requiredParameter  = array('user_id','brand_name','product','seal_time','quality_time','overdue_time');
        $this->completeParameter($requiredParameter);

        $userId      = intval(self::$data['user_id']);
        $brandId     = intval(self::$data['brand_id']);
        $brandName   = Functions::checkStr(self::$data['brand_name']);
        $product     = Functions::checkStr(self::$data['product']);
        $productImg  = Functions::checkStr(self::$data['product_img']);
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
        //入库
        $sql        = "INSERT INTO {{%user_product}} (user_id,brand_id,brand_name,product,img,is_seal,seal_time,quality_time,overdue_time,add_time) 
                     VALUES ('$userId','$brandId','$brandName','$product','$newProductImg','$isSeal','$sealTime','$qualityTime','$overdueTime','$time')"; 

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
        //验证
        $requiredParameter  = array('user_id');
        $this->completeParameter($requiredParameter);

        $userId     = intval(self::$data['user_id']);

        //查询
        $sql        = "SELECT U.id,U.username,U.mobile,U.email,U.img,U.img_state,U.sex,U.birth_year,U.birth_month,U.birth_day,U.province,U.city,U.status,U.rank_points,S.dry,S.tolerance,S.pigment,S.compact,S.skin_name
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

            if($userSkin){
                unset($userSkin['skin_name']);
                $return['skin_name'] = join(" | ",$userSkin);
            }
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
        $requiredParameter  = array('user_id','username','content');
        $this->completeParameter($requiredParameter);

        $userId      = intval(self::$data['user_id']);
        $source      = self::$data['from'] == 'android' ? '1' : '2';
        //$username    = Functions::checkStr(self::$data['username']);
        $content     = Tools::userTextEncode(Functions::checkStr(self::$data['content']),1) ;
        $telphone    = isset(self::$data['telphone']) ? Functions::checkStr(self::$data['telphone']) : '';

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
        $username = $userInfo['username'];
        //入库
        $sql    = "INSERT INTO {{%user_feedback}} (`user_id`, `source`, `username`,`content`,`telphone`,`created_at`) 
                      VALUES ('$userId', '$source','$username','$content','$telphone','$time')";

        $return     = Yii::$app->db->createCommand($sql)->execute();

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
        $sql    = "SELECT id,type,content,number,downloadUrl FROM {{%app_version}}  WHERE type = '$type' AND status = 1 ORDER BY create_time DESC"; 

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
     * 用户手机绑定接口
     * @para int    $user_id   用户ID
     * @para string $mobile    手机
     * @para int    $captcha   校验码
     * @para int    $time      时间
     * @return json 更新返更新版本信息，不更新失败返回操作成功，失败返回错误信息
     */
    PUBLIC function mobileBind()
    {
        //验证
        $requiredParameter  = array('user_id','mobile','captcha');
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
    *  用户长草
    *  @para int      $cate_id    分类id
    *  @para string   $effect     功效关键字
    *  @para int      $pageSize   每页页数
    *  @para int      $page       页数
    * 
    * @return json 成功返回信息，失败返回错误
    */
    PUBLIC function userGrass(){
        //验证
        $requiredParameter  = array('user_id');
        $this->completeParameter($requiredParameter);

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
        //分类列表和功效列表
        $return['categroy']   = Functions::categoryList();
        $return['effects']    = Functions::effectList();

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
        //验证
        $requiredParameter  = array('user_id');
        $this->completeParameter($requiredParameter);

        $userId   = intval(self::$data['user_id']);

        $userSkin   = skin::getUserTestSkin($userId);
        $info       = skin::getUserSkin($userId);

        $return['desc'] = $userSkin;
        if($info){
            $skinList = skin::getSkin();
            $skinInfo = $skinList[$info['skin_name']];
            $return['explain']  = $skinInfo['explain'];
            $return['features'] = $skinInfo['features'];
            $return['elements'] = $skinInfo['elements'];
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
        $requiredParameter  = array('user_id','type','value');
        $this->completeParameter($requiredParameter);

        $skinArr   = ['dry','tolerance','pigment','compact'];

        $isType    = in_array(self::$data['type'],$skinArr);
        if(!$isType) return json_encode(['status' => -1, 'msg' =>self::$ERROR['0']]);

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
            $info       = skin::getUserSkin($userId);
            if($info && $info['skin_name']){
                $isComplete = 1;
                Functions::updateMoney($userId,100,'肤质测试',2);
            }
            $data  = ['status' => 1, 'msg' =>self::$ERROR['1'],'isComplete' => $isComplete];
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
        //验证
        $requiredParameter  = array('user_id');
        $this->completeParameter($requiredParameter);

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
        $requiredParameter  = array('user_id','cate_id');
        $this->completeParameter($requiredParameter);

        $userId    = intval(self::$data['user_id']);
        $cateId    = intval(self::$data['cate_id']);
        $pageSize  = isset(self::$data['pageSize']) ? intval(self::$data['pageSize']) : 20;
        $page      = isset(self::$data['page'])     ? intval(self::$data['page']) : 1;

        $info      = skin::getUserSkin($userId);

        if(!$info) {
            $data = ['status' => -1, 'msg' =>self::$ERROR['0']];
            return json_encode($data);
        }

        $skinName = $info['skin_name'];
        $return   = skin::skinProductList($skinName,$cateId,$page,$pageSize);

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
        $requiredParameter  = array('user_id');
        $this->completeParameter($requiredParameter);

        $userId    = isset(self::$data['user_id'])  ? intval(self::$data['user_id']) : 0 ;
        $pageSize  = isset(self::$data['pageSize']) ? intval(self::$data['pageSize']) : 20;
        $page      = isset(self::$data['page'])     ? intval(self::$data['page']) : 1;
        $pageMin   = ($page - 1) * $pageSize;
        /*查询条件*/
        $whereStr   = "user_id = '$userId' AND pay = '积分'";

        /*分页*/
        $sql        = " SELECT COUNT(*)  FROM {{%user_account}} WHERE $whereStr";

        $num        = Yii::$app->db->createCommand($sql)->queryScalar();

        
        $selectSql  = " SELECT `money`,`content`,`created_at` FROM {{%user_account}}
                        WHERE $whereStr  ORDER BY created_at DESC  LIMIT $pageMin,$pageSize";

        $rows       = Yii::$app->db->createCommand($selectSql)->queryAll();

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
        $requiredParameter  = array('id','user_id','type');
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
        //验证
        $requiredParameter  = array('user_id');
        $this->completeParameter($requiredParameter);

        //查询
        $userId     = intval(self::$data['user_id']);
        $userInfo   = User::findOne(['id'=>$userId])->toArray();
        if(!$userInfo){
            $data    = ['status' => -1, 'msg' =>self::$ERROR['-2']];
            return json_encode($data);
        }
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
        $unNum  = intval($userNum + $systemNum - $readNum);
      
        $unReadsysNum  = $unNum >= 0 ? $unNum : 0 ;
        $unReadNum = $unReadsysNum+$pmsNum;

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
            $data = ['status' => 1, 'msg' => $user->id];
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
                $return = [
                    'status' => '1',
                    'msg'=>['user_id' => $rows['id'],'user_name' => Tools::userTextDecode($rows['username']),'img' => Functions::get_image_path($rows['img'],1)]
                ];
            } else {
                $return = array('status' => '-1','msg'=>self::$ERROR['-1']);
            }
        } elseif ($type == 2) {
            $time = time();
            $sql = "SELECT * FROM {{%mobile_captcha}} WHERE mobile = '$mobile' AND captcha = '$captcha' AND expire_time >= '$time' AND type = '$dataType' ORDER BY id DESC LIMIT 1";
            $captchaInfo = Yii::$app->db->createCommand($sql)->queryOne();

            if(empty($captchaInfo) || $captchaInfo['captcha'] != $captcha ){
                $return = array('status' => '-12','msg'=>self::$ERROR['-12']);
            } else {
                //修改验证码状态
                $captchaSql   = "UPDATE  {{%mobile_captcha}} SET is_use = '1',using_time = '$time' WHERE mobile ='$mobile' AND captcha = '$captcha' AND type = '$type' AND expire_time >= '$time'";
                $update = Yii::$app->db->createCommand($captchaSql)->execute();
                if ($update) {
                    $return = [
                        'status' => '1',
                        'msg'=>['user_id' => $rows['id'],'user_name' => Tools::userTextDecode($rows['username']),'user_img' => Functions::get_image_path($rows['img'],1)],
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
        $selectSql  = " SELECT t.user_id FROM {{%third_login}} t WHERE t.unionid = '$unionid'";
        $userId     = Yii::$app->db->createCommand($selectSql)->queryScalar();

        //存在返回用户ID，不存在则注册
        if($userId) {
            $userInfo = Functions::getUserInfo($userId);
            $data = [
                'status' => '1',
                'msg'=>['user_id' => $userInfo['id'],'user_name' => Tools::userTextDecode($userInfo['username']),'user_img' => $userInfo['img']],
            ];
            return  json_encode($data); die;
        }
        //先入库UNIONID
        try {
            $thirdSql   = "INSERT IGNORE INTO {{%third_login}} (type,openid,unionid,created_at) 
                         VALUES('$type','$openid','$unionid','$time')";
            Yii::$app->db->createCommand($thirdSql)->execute();
            $insertId   = Yii::$app->db->getLastInsertID();  

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
            $user->mobile   = $type . $user->id;
            $user->save();
            $userId  = $user->id;
            //插入第三方登录记录
            $thirdSql = "UPDATE {{%third_login}} SET user_id = '$userId' WHERE id = '$insertId'";
            $result   = Yii::$app->db->createCommand($thirdSql)->execute();
            // 添加注册成功通知
            Functions::updateMoney($userId,200,'注册',2);
            NoticeFunctions::notice($userId, NoticeFunctions::$SIGN_UP);

            $data = ['status' => 1, 'msg' => ['user_id' => $userId,'user_name' => $nickname,'user_img' => Functions::get_image_path($user->img,1)]];

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
        $requiredParameter  = array('user_id','relation_id','type');
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

        $requiredParameter  = array('user_id');
        $this->completeParameter($requiredParameter);
        //参数
        $userId     = intval(self::$data['user_id']);
        $pageSize   = isset(self::$data['pageSize']) ? intval(self::$data['pageSize']) : 20;
        $page       = isset(self::$data['page']) ? intval(self::$data['page']) : 1;
        $pageMin    = ($page - 1) * $pageSize;

        $whereStr   = "C.user_id = '$userId'  AND C.type = '2'";
        $sql        = "SELECT COUNT(*) AS num FROM {{%user_collect}} C WHERE $whereStr";
        $num        = Yii::$app->db->createCommand($sql)->queryScalar();

        $sql        = " SELECT A.id,C.add_time,A.title,A.article_img,A.like_num, A.comment_num,A.created_at 
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
        $requiredParameter  = array('user_id');
        $this->completeParameter($requiredParameter);

        $userId     = isset(self::$data['user_id']) ? intval(self::$data['user_id']) : 0 ;
        $type       = isset(self::$data['type'])    ? intval(self::$data['type']) : 0;
        $type_sql   = !empty($type)                 ? " AND type = '$type'" : '';

        $pageSize   = isset(self::$data['pageSize']) ? intval(self::$data['pageSize']) : 20;
        $page       = isset(self::$data['page']) ? intval(self::$data['page']) : 1;
        $pageMin    = ($page - 1) * $pageSize;

        $sql        = "SELECT COUNT(*) AS num FROM {{%comment}}  WHERE user_id = '$userId'".$type_sql;
        $num        = Yii::$app->db->createCommand($sql)->queryScalar();

        $selectSql  = "SELECT id,type,post_id,comment,created_at FROM {{%comment}}  WHERE user_id = '$userId'".$type_sql." ORDER BY created_at DESC LIMIT $pageMin,$pageSize";
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
        $requiredParameter  = array('user_id');
        $this->completeParameter($requiredParameter);

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

        $sql = "SELECT id,0 as relation_id,0 AS user_name,content,created_at,'photo/admin_user.png' AS img ,100 AS type,type as otype,0 as num FROM {{%notice_user}}  WHERE user_id = '$userId' AND type != '2'
                UNION all 
                SELECT id ,relation_id,user_name ,content,max(created_at) AS created_at,img ,type, otype,count(*) as num from (
                SELECT p.id AS id,c.post_id as relation_id,u.username AS user_name,p.message AS content,p.created_at,u.img ,p.type,c.type as otype  FROM {{%pms}} AS p
                LEFT JOIN {{%user}} AS u ON p.from_id = u.id 
                LEFT JOIN {{%comment}} AS c ON p.relation_id = c.id WHERE p.receive_id = '$userId' ORDER BY p.id DESC) a GROUP BY a.relation_id,a.content
                UNION all 
                SELECT ns.id AS id,0 as relation_id,a.username AS user_name,ns.content,ns.created_at,'photo/admin_user.png' AS img,100 AS type, 0 as otype ,0 as num 
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

            $selectSql      = "SELECT * FROM ($sql) t ORDER BY t.created_at DESC LIMIT $pageMin,$pageSize";
            $result         = Yii::$app->db->createCommand($selectSql)->queryAll();
            if(!empty($result)){
                foreach ($result as $key => $value) {
                    $result[$key]['user_name']      = empty($value['user_name'])  ? '颜究院小秘书' : Functions::checkStr($value['user_name']);
                    if($value['type'] != 100 && $value['type'] != 4 && $value['num'] > 1){
                        $result[$key]['user_name']  = $result[$key]['user_name'].'...等'.$value['num'].'人';
                    }
                    if($value['type'] == 4){
                        $result[$key]['user_name']  = '颜究院小秘书';
                        $result[$key]['img']        = 'photo/admin_user.png';
                        $result[$key]['content']    = '回复了你的意见反馈：'.$result[$key]['content'];
                    }
                    $result[$key]['img']            = Functions::get_image_path($result[$key]['img'],1);
                    $result[$key]['type']           = $value['type'] == 100  || $value['type'] == 4 ? '2' : '1';

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
        $captcha        = !empty(self::$data['captcha'])     ?  Functions::checkStr(self::$data['captcha']) :'' ; 
        $newPassword    = Functions::checkStr(self::$data['newPassword']);

        //验证帐号
        $time         = time();
        if(!preg_match("/^1\d{10}$/",$mobile)){  
            $data = ['status' => -200, 'msg' => '手机号格式有误'];
            return json_encode($data);
        }
        if ((mb_strlen($newPassword,"UTF-8")<6) || (mb_strlen($newPassword,"UTF-8")>15)) {
            $data = ['status' => -200, 'msg' => '密码字符长度6-15位'];
            return json_encode($data);
        }
        //验证验证码
        $isCaptcha   = Functions::checkCaptcha($mobile,$captcha,1);
        
        if(!$isCaptcha) {
            $data = ['status' => '-12', 'msg' => self::$ERROR['-12']];
            return  json_encode($data);
        }

        $user = User::find()
              ->where('mobile = :mobile', [':mobile' => $mobile])
              ->one();
        if ($user) {
            $user->setPassword($newPassword);
            if ($user->save()) {
                Functions::useCaptcha($mobile,$captcha,1);
                $data = ['status' => '1', 'msg' => self::$ERROR['1']];
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

        $requiredParameter  = array('user_id','attribute');
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
        $requiredParameter  = array('user_id');
        $this->completeParameter($requiredParameter);

        $userId     = intval(self::$data['user_id']);
        $type       = self::$data['type']     ? intval(self::$data['type']) : 0;
        $pageSize   = self::$data['pageSize'] ? intval(self::$data['pageSize']) : 10;
        $page       = self::$data['page']     ? intval(self::$data['page']) : 1;

        $time       = strtotime(date('Y-m-d'));
        $whereStr   = " user_id = '$userId' ";
        switch ($type) {
            case '1':
                $whereStr .= " AND  is_seal = '1' AND  overdue_time >=  '$time' ";
                break;
            case '2':
                $whereStr .= " AND  is_seal = '0' AND  overdue_time >=  '$time' ";
                break;
            case '3':
                $whereStr .= " AND  overdue_time <  '$time' ";
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
        $type       = !empty(self::$data['type'])         ? intval(self::$data['type']) : 1 ;
        $time       = time();
        $data = ['status' => 1, 'msg' => self::$ERROR['1']];
        if($type == 1){
            $sql = "UPDATE {{%user_product}} SET is_seal = 1,seal_time = '$time' WHERE id = '$Id'";
        }else{
            $sql = "delete from {{%user_product}} WHERE id = '$Id'";
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
        $categoryList = Functions::categoryList();
        $effectList = Functions::effectList();
        if($data['status'] != 1) return json_encode(['status' => 1,'msg' => ['categoryList'=>$categoryList,'effectList'=>$effectList],'pageTotal' => 0 , 'page'=>$page]);

        $return = [
            'status'    =>  '1',
            'msg'       =>  ['brand' => $data['brand'],'product' => $data['data']]
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
}
