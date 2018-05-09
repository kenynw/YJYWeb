<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\functions;

use QL\QueryList;
use Yii;
use common\components\OssUpload;
/**
 * Description of Functions
 *
 * @author Administrator
 */
class Functions {
    public static function beforeRequest(){
        Yii::info('************请求信息***START*****');
        Yii::info($_GET);
        Yii::info($_POST);
        Yii::info('************请求信息***END*****');
    }
    public static function afterRequest(){
        Yii::info('**********返回信息****START*******');
        Yii::info(Yii::$app->response->data);
        Yii::info('**********返回信息****END*******');
    }
    /**
     * 加密函数
     *
     * @param string $txt
     * @param string $key
     * @return string
     */
    public static function passport_encrypt($txt, $key)
    {
        srand((double)microtime() * 1000000);
        $encrypt_key = md5(rand(0, 32000));
        $ctr = 0;
        $tmp = '';
        for($i = 0; $i < strlen($txt); $i++ )
        {
            $ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
            $tmp .= $encrypt_key[$ctr].($txt[$i] ^ $encrypt_key[$ctr++]);
        }
        return base64_encode(self::passport_key($tmp, $key));
    }
    /**
     * 编码函数
     *
     * @param string $txt
     * @param string $key
     * @return string
     */
    public static function passport_key($txt, $encrypt_key)
    {
        $encrypt_key = md5($encrypt_key);
        $ctr = 0;
        $tmp = '';
        for($i = 0; $i < strlen($txt); $i++)
        {
            $ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
            $tmp .= $txt[$i] ^ $encrypt_key[$ctr++];
        }
        return $tmp;
    }
    /**
     * 解密函数
     *
     * @param string $txt
     * @param string $key
     * @return string
     */
    public static function passport_decrypt($txt, $key)
    {
        $txt = self::passport_key(base64_decode($txt), $key);
        $tmp = '';
        for ($i = 0;$i < strlen($txt); $i++) {
            $md5 = $txt[$i];
            $tmp .= $txt[++$i] ^ $md5;
        }
        return $tmp;
    }
    /**
     * [getUserToken 获取TOKEN]
     * @param  [type] $userid  [token]
     * @return [type]         [description]
     */
    public static function getUserToken($userId){
        if(!$userId) return false;

        $sql        = "SELECT token FROM {{%login_token}} WHERE  user_id = '$userId'";

        $token      = Yii::$app->db->createCommand($sql)->queryScalar();
        return $token ? $token : false;
    }
    /**
     * [checkToken 验证TOKEN]
     * @param  [type] $token  [token]
     * @return [type]         [description]
     */
    public static function checkToken($token){
        $token  = self::checkStr($token);
        if(!$token) return false;

        $sql        = "SELECT user_id FROM {{%login_token}} WHERE  token = '$token'";

        $userId     = Yii::$app->db->createCommand($sql)->queryScalar();
        return $userId ? $userId : 0;
    }
    /**
     * [checkToken 获取TOKEN]
     * @param  [type] $userId [userId]
     * @return [type]         [description]
     */
    public static function getToken($params){
        $userId     = intval($params['user_id']);
        $referer    = self::checkStr($params['referer']);
        if(!$userId) return '';
        $time       = time();
        $rand       = rand(1,9999);
        $token      = md5($userId.$time.$rand);

        $sql        = "DELETE  FROM {{%login_token}} WHERE  user_id = '$userId'";
        Yii::$app->db->createCommand($sql)->execute();

        $insertSql = "INSERT INTO {{%login_token}} (user_id,token,referer) VALUES ('$userId','$token','$referer')";
        $return    = Yii::$app->db->createCommand($insertSql)->execute();

        return $return ? $token : '';
    }
    /**
     * 创建像这样的查询: "IN('a','b')";
     *
     * @access   public
     * @param    mix      $item_list      列表数组或字符串
     * @param    string   $field_name     字段名称
     *
     * @return   void
     */
    public static function db_create_in($item_list, $field_name = '',$isNot = '')
    {
        $isNot = $isNot ? ' NOT ' : '';
        if (empty($item_list))
        {
            return $field_name . $isNot ." IN ('') ";
        }
        else
        {
            if (!is_array($item_list))
            {
                $item_list = explode(',', $item_list);
            }
            $item_list = array_unique($item_list);
            $item_list_tmp = '';
            foreach ($item_list AS $item)
            {
                if ($item !== '')
                {
                    $item_list_tmp .= $item_list_tmp ? ",'$item'" : "'$item'";
                }
            }
            if (empty($item_list_tmp))
            {
                return $field_name . $isNot ." IN ('') ";
            }
            else
            {
                return $field_name . $isNot .' IN (' . $item_list_tmp . ') ';
            }
        }
    }
    /**
     * 发送HTTP请求方法
     * @param  string $url    请求URL
     * @param  array  $params 请求参数
     * @param  string $method 请求方法GET/POST
     * @return array  $data   响应数据
     */
    static function http_judu($url, $params = array(), $method = 'GET', $header = array(), $multi = false){
        $opts = array(
                CURLOPT_TIMEOUT        => 5,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_HTTPHEADER     => $header
        );

        /* 根据请求类型设置特定参数 */
        switch(strtoupper($method)){
            case 'GET':
                $opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
                break;
            case 'POST':
                //判断是否传输文件
                $params = $multi ? $params : http_build_query($params);
                $opts[CURLOPT_URL] = $url;
                $opts[CURLOPT_POST] = 1;
                $opts[CURLOPT_POSTFIELDS] = $params;
                break;
            default:
                throw new Exception('不支持的请求方式！');
        }

        /* 初始化并执行curl请求 */
        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data  = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if($error) throw new Exception('请求发生错误：' . $error);
        return  $data;
    }
    /**
     * 字符串过滤，过滤所有html代码
     * @param string $string
     * @return $string
     */
    static function checkStr($string, $length = 0) {
        $string = preg_replace('/[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F]/', '', $string);
        $string = str_replace(array("\0", "%00", "\r"), '', $string);
        if($length){
          $string = substr($string,0,$length);
        }
        $string = str_replace(array("%3C", '<'), '&lt;', $string);
        $string = str_replace(array("%3E", '>'), '&gt;', $string);
        $string = str_replace(array('"', "'", "\t"), array('&quot;', '&#39;', '    '), $string);
        return trim($string);
    }
    /**
     * 截取UTF-8编码下字符串的函数
     *
     * @param   string      $str        被截取的字符串
     * @param   int         $length     截取的长度
     * @param   bool        $append     是否附加省略号
     *
     * @return  string
     */
    static function sub_str($str, $length = 0, $append = true){
        $str        = trim($str);
        $strlength  = strlen($str);

        if ($length == 0 || $length >= $strlength)
        {
            return $str;
        }
        elseif ($length < 0)
        {
            $length = $strlength + $length;
            if ($length < 0)
            {
                $length = $strlength;
            }
        }

        if (function_exists('mb_substr'))
        {
            $newstr = mb_substr($str, 0, $length, 'utf-8');
        }
        elseif (function_exists('iconv_substr'))
        {
            $newstr = iconv_substr($str, 0, $length, 'utf-8');
        }
        else
        {
            //$newstr = trim_right(substr($str, 0, $length));
            $newstr = substr($str, 0, $length);
        }

        if ($append && $str != $newstr)
        {
            $newstr .= '...';
        }

        return $newstr;
    }
    /**
     * 获取用户年龄
     * @param  intval $year   出生年份
     * @return intval 用户当前年龄
     */
    public static function getUserAge($year){
        $nowYear    = date('Y');
        $year       = intval($year) ? intval($year) : $nowYear;
        $userYear   = intval($nowYear - $year);

        return $userYear;
    }
    /**
     * [is_moblie 验证手机]
     * @param  [type] $tel [电话号码]
     * @return [type]      [description]
     */
    public static function isMoblie($moblie)
    {
       return  preg_match("/^0?1((3|4|7|8)[0-9]|5[0-35-9]|4[57])\d{8}$/", $moblie);
    }
    /**
     * 验证输入的邮件地址是否合法
     *
     * @access  public
     * @param   string      $email      需要验证的邮件地址
     *
     * @return bool
     */
    public static function isEmail($email)
    {
        $chars = "/^([a-z0-9+_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,6}\$/i";
        if (strpos($email, '@') !== false && strpos($email, '.') !== false)
        {
            if (preg_match($chars, $email))
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }
    /**
     * [getFirstCharter 获取首字母]
     * @param  [type] $str [description]
     * @return [type]      [description]
     */
    public static function getFirstCharter($str){ 
         if(empty($str)){return '';} 
         $fchar=ord($str{0}); 
         if($fchar>=ord('A')&&$fchar<=ord('z')) return strtoupper($str{0}); 
         $s1=iconv('UTF-8','gbk',$str); 
         $s2=iconv('gbk','UTF-8',$s1); 
         $s=$s2==$str?$s1:$str; 
         $asc=ord($s{0})*256+ord($s{1})-65536; 
         if($asc>=-20319&&$asc<=-20284) return 'A'; 
         if($asc>=-20283&&$asc<=-19776) return 'B'; 
         if($asc>=-19775&&$asc<=-19219) return 'C'; 
         if($asc>=-19218&&$asc<=-18711) return 'D'; 
         if($asc>=-18710&&$asc<=-18527) return 'E'; 
         if($asc>=-18526&&$asc<=-18240) return 'F'; 
         if($asc>=-18239&&$asc<=-17923) return 'G'; 
         if($asc>=-17922&&$asc<=-17418) return 'H'; 
         if($asc>=-17417&&$asc<=-16475) return 'J'; 
         if($asc>=-16474&&$asc<=-16213) return 'K'; 
         if($asc>=-16212&&$asc<=-15641) return 'L'; 
         if($asc>=-15640&&$asc<=-15166) return 'M'; 
         if($asc>=-15165&&$asc<=-14923) return 'N'; 
         if($asc>=-14922&&$asc<=-14915) return 'O'; 
         if($asc>=-14914&&$asc<=-14631) return 'P'; 
         if($asc>=-14630&&$asc<=-14150) return 'Q'; 
         if($asc>=-14149&&$asc<=-14091) return 'R'; 
         if($asc>=-14090&&$asc<=-13319) return 'S'; 
         if($asc>=-13318&&$asc<=-12839) return 'T'; 
         if($asc>=-12838&&$asc<=-12557) return 'W'; 
         if($asc>=-12556&&$asc<=-11848) return 'X'; 
         if($asc>=-11847&&$asc<=-11056) return 'Y'; 
         if($asc>=-11055&&$asc<=-10247) return 'Z'; 
         return '#'; 
    }
    /**
     * 重新获得商品图片与商品相册的地址
     *
     * @param string $image 原商品相册图片地址
     * @param boolean $thumb 是否为缩略图
     *
     * @return string   $url
     */
    public static function get_image_path($image = '', $thumb = false , $width = 200, $height = 200)
    {
        $url = empty($image) ? '' : Yii::$app->params['uploadsUrl'].$image;
        $url = $thumb && $image ? $url.'@!'.$width.'x'.$height.'.jpg' : $url;
        return $url;
    }
    /** 
     * [checkIsuser description] 检查手机用户是否存在
     * @param  [type]  $mobile [description] 手机号
     * @param  integer $type   [description] 类型
     * @return [type]          [是否存在]
     */
    public static function checkIsuser($mobile,$type = 0){

        $sql    = " SELECT * FROM {{%user}} WHERE mobile = '$mobile'";
        $user = Yii::$app->db->createCommand($sql)->queryOne();
        //检测是否已注册
        if ($type == 3 && $user) {
            $type = 2;
        } elseif($type == 3 && !$user) {
            $type = 0;
        }
        if ($type == 0 && $user) {
            return true;
        }else{
            return false;
        }
    }
    /** 
     * [updateUserName description] 更新用户名信息
     * @param  [type]  $userId [description] 用户ID
     */
    public static function updateUserName($userId){
        $userId     = intval($userId);
        if(!$userId) return false;

        $sql        = "SELECT username FROM {{%user}} WHERE id = '$userId'";
        $username   = Yii::$app->db->createCommand($sql)->queryScalar();

        //检测是否已注册
        if(!$username) return false;

        $updateSql  = "UPDATE {{%comment}} SET author = '$username' WHERE user_id = '$userId'"; 
        $return     = Yii::$app->db->createCommand($updateSql)->execute();
        //修改反馈用户名
        $updateSql  = "UPDATE {{%user_feedback}} SET username = '$username' WHERE user_id = '$userId'"; 
        Yii::$app->db->createCommand($updateSql)->execute();

        //修改问答用户名
        $updateSql  = "UPDATE {{%ask}} SET username = '$username' WHERE user_id = '$userId'"; 
        Yii::$app->db->createCommand($updateSql)->execute();
        //修改问答评论用户名
        $updateSql  = "UPDATE {{%ask_reply}} SET username = '$username' WHERE user_id = '$userId'"; 
        Yii::$app->db->createCommand($updateSql)->execute();

        return $return;
    }

    /**
     * [checkCaptcha 验证验证码函数]
     * @param  [type]  $mobile  [手机号]
     * @param  [type]  $captcha [验证码]
     * @param  integer $type    [类型0注册，1重置密码， 2登录，3登录注册,4为绑定]
     * @return [type]           [是否通过]
     */
    public static function checkCaptcha($mobile,$captcha,$type = 0){
        $isMoblie = self::isMoblie($mobile);
        $captcha  = intval($captcha);
        $time     = time();

        $expire_time  = $time  + 60 * 30;
        // //入库
        // $sql    = " INSERT INTO {{%mobile_captcha}} (`mobile`, `captcha`, `expire_time`,`type`,`created_at`) 
        //             VALUES ('$mobile', '$captcha','$expire_time','$type','$time')";
        // Yii::$app->db->createCommand($sql)->execute();

        if(!$isMoblie) return false;
        //验证验证码 
        $sql    = " SELECT * FROM {{%mobile_captcha}} 
                    WHERE mobile = '$mobile' AND expire_time >= '$time' AND type = '$type' AND captcha = '$captcha'
                    ORDER BY id DESC";
        $return = Yii::$app->db->createCommand($sql)->queryOne();

        return $return ? true : false;
    }
    /**
     * [checkCaptcha 验证验证码函数]
     * @param  [type]  $mobile  [手机号]
     * @param  [type]  $captcha [验证码]
     * @param  integer $type    [类型0注册，1重置密码， 2登录，3登录注册,4为绑定]
     * @return [type]           [是否通过]
     */
    public static function checkMobile($mobile,$user_id){
        $isMoblie = self::isMoblie($mobile);

        if(!$isMoblie) return false;
        //验证验证码 
        $sql    = " SELECT * FROM {{%user}} WHERE id != '$user_id' AND mobile = '$mobile'";
        $return = Yii::$app->db->createCommand($sql)->queryOne();

        return $return ? true : false;
    }
    /**
     * [checkCaptcha 修改验证码状态函数]
     * @param  [type]  $mobile  [手机号]
     * @param  [type]  $captcha [验证码]
     * @param  integer $type    [类型0注册，1重置密码， 2登录，3登录注册,4为绑定]
     * @return [type]           [是否通过]
     */
    public static function useCaptcha($mobile,$captcha,$type = 0){

        $isMoblie = self::isMoblie($mobile);
        $captcha  = intval($captcha);
        $time     = time();

        if(!$isMoblie) return false;
        //修改验证码状态
        $sql    = " UPDATE  {{%mobile_captcha}} SET is_use = '1',using_time = '$time' 
                    WHERE mobile ='$mobile' AND captcha = '$captcha' AND type = '$type' AND expire_time >= '$time'";
        $return = Yii::$app->db->createCommand($sql)->execute();

        return $return ? true : false;
    }
    /**
     * [categoryList 获取栏目列表]
     * @return [type] [description]
     */
    public static function categoryList(){
        $cache     =    Yii::$app->cache;
        //分类列表和功效列表
        $categoryList = $cache->get('categoryList');
        if(!$categoryList){
            //分类信息
            $sql            =   "SELECT  id,cate_name,cate_h5_img,cate_app_img,budget FROM {{%product_category}} WHERE status = 1 AND is_old = 1 ORDER BY sort ASC";
            $categoryList   =   Yii::$app->db->createCommand($sql)->queryAll(); 
            $cache->set('categoryList',$categoryList,300);
        }
        return $categoryList;
    }
    /**
     * [categoryList 获取栏目列表]
     * @return [type] [返回ID,VALUE数组]
     */
    public static function cateList(){
        $cateList       =   [];
        $categoryList   =   self::categoryList();

        foreach ($categoryList as $key => $value) {
            $cateList[$value['id']] = $value['cate_name'];
        }
        return $cateList;     
    }
    /**
     * [effectList 功效列表]
     * @return [type] [description]
     */
    public static function effectList(){
        $cache     =    Yii::$app->cache;
        //分类列表和功效列表
        $effectList = $cache->get('effectList');
        if(!$effectList){
            //分类信息
            $sql            =   "SELECT effect_id,effect_name FROM {{%product_effect}}";
            $effectList     =   Yii::$app->db->createCommand($sql)->queryAll(); 
            $cache->set('effectList',$effectList,300);
        }
        return $effectList;     
    }
    /**
     * [userIsGras 判断是否收藏或长草]
     * @param  [type]  $userId [用户ID]
     * @param  [type]  $id     [文章或产品ID]
     * @param  integer $type   [1为产品，2为文章]
     * @return [type]          [返回0或1]
     */
    PUBLIC STATIC function userIsGras($userId,$id,$type = 1){

        $userId  = intval($userId);
        $id      = intval($id);
        $type    = intval($type);

        if(!$userId || !$id) return false;

        $sql        = "SELECT rec_id FROM {{%user_collect}} WHERE  user_id =  '$userId' AND type = '$type'  AND relation_id = '$id'";
        $isExsit    = Yii::$app->db->createCommand($sql)->queryScalar();
        $isGras     = $isExsit ? true : false;

        return $isGras;  
    }
    /**
     * [getArticle 文章详情]
     * @param  [type]  $userId [用户ID]
     * @param  [type]  $id     [文章ID]
     * @return [type]          [返回具体参数]
     */
    PUBLIC STATIC function getArticle($userId,$id){

        $userId  = intval($userId);
        $id      = intval($id);

        if(!$id) return false;

        $sql    = "SELECT id,title,article_img,like_num,comment_num,created_at FROM {{%article}} WHERE status = '1' AND id='$id'";
        $rows   = Yii::$app->db->createCommand($sql)->queryOne();

        if(!empty($rows)){
            $rows['article_img']  = Functions::get_image_path($rows['article_img']);
            $rows['created_at']   = Tools::HtmlDate($rows['created_at']);
            $rows['linkUrl']      = Yii::$app->params['mfrontendUrl'].'article/index?id='.$rows['id'];
            $rows['isGras']       = self::userIsGras($userId,$id,2) ? 1 : 0;
        }

        return $rows;
    }
    /**
     * [getAttachment 获取附件]
     * @param  [type]  $id   [ID]
     * @param  integer $type [1为评论]
     * @return [type]        [description]
     */
    PUBLIC STATIC function getAttachment($id,$type = 1,$isNew = 0){

        $type    = intval($type);
        $id      = intval($id);

        if(!$id) return '';

        $sql          = "SELECT attachment FROM {{%attachment}} WHERE cid='$id' AND type = '$type'";

        if($isNew){
            $return           = [];
            $attachmentList   = Yii::$app->db->createCommand($sql)->queryAll(); 
            foreach ($attachmentList as $key => $value) {
                $return[] = Functions::get_image_path($value['attachment']);
            }
            $return = !empty($return) ? $return : (object)[];
        }else{
            $return = '';
            $attachment   = Yii::$app->db->createCommand($sql)->queryScalar();

            if($attachment){
                $attachment  = Functions::get_image_path($attachment);
            }
            $return = $attachment ? $attachment : '';      
        }
        return $return;
    }
    /**
     * [getCommentReply 获取一条评论]
     * @param  [type]  $id   [ID]
     * @param  integer $type [1为评论]
     * @return [type]        [description]
     */
    PUBLIC STATIC function getCommentReply($id){
        $id      = intval($id);

        if(!$id) return false;

        $sql        = "SELECT user_id,author,comment FROM {{%comment}} WHERE parent_id = '$id' AND status = '1' ORDER BY like_num,created_at DESC";
        $replyInfo  = Yii::$app->db->createCommand($sql)->queryOne();

        if($replyInfo && $replyInfo['comment']){
            $replyInfo['comment'] = Tools::userTextDecode($replyInfo['comment']);
        }
    
        return $replyInfo;
    }
    /**
     * [getCommentReplyList 获取评论列表]
     * @param  [type]  $id   [ID]
     * @param  integer $type [1为评论]
     * @return [type]        [description]
     */
    PUBLIC STATIC function getCommentReplyList($id){
        $id      = intval($id);

        if(!$id) return false;

        $sql        = " SELECT C.user_id,C.author,C.comment,U.img FROM {{%comment}} C LEFT JOIN {{%user}}  U ON C.user_id = U.id
                        WHERE C.parent_id = '$id' AND C.status = '1' ORDER BY C.like_num,C.created_at DESC";
        $replyInfo  = Yii::$app->db->createCommand($sql)->queryAll();

        foreach ($replyInfo as $key => $value) {
            $replyInfo[$key]['user_img']  = Functions::get_image_path($value['img'],1,150,150);
            $replyInfo[$key]['comment']   = Tools::userTextDecode($value['comment']);
            unset($replyInfo[$key]['img']);
        }
    
        return $replyInfo;
    }
    /**
     * [skinLog 肤质参与记录]
     * @param  [type]  $userId [用户ID]
     * @param  [type]  $type   [类型]
     * @return [type]          [返回具体参数]
     */
    PUBLIC STATIC function skinLog($userId,$type,$referer = 'ios'){

        $userId  = intval($userId);
        $type    = self::checkStr($type);
        $time    = time();
        $referer = strtolower($referer);

        if(!$userId || !$type) return false;

        $insertSql = "INSERT INTO {{%log_skin}} (user_id,type,referer,created_at) VALUES ('$userId','$type','$referer','$time')";
        Yii::$app->db->createCommand($insertSql)->execute();

        return true;
    }
    /**
     * [lessonsLog 功课参与记录]
     * @param  [type]  $userId [用户ID]
     * @param  [type]  $type   [类型]
     * @return [type]          [返回具体参数]
     */
    PUBLIC STATIC function lessonsLog($userId,$referer = 'ios'){
        $userId  = intval($userId);
        $time    = time();
        $referer = strtolower($referer);

        $insertSql = "INSERT INTO {{%log_lessons}} (user_id,referer,created_at) VALUES ('$userId','$referer','$time')";
        Yii::$app->db->createCommand($insertSql)->execute();

        return true;
    }
    /**
     * [shareLog 分享记录]
     * @param  [type]  $userId [用户ID]
     * @param  [type]  $type   [类型1为产品,2为文章3分享页面]
     * @param  [type]  $id     [对应的ID]
     * @return [type]          [返回具体参数]
     */
    PUBLIC STATIC function shareLog($userId,$type,$id,$referer = 'ios'){

        $userId  = intval($userId);
        $type    = intval($type);
        $id      = intval($id);
        $time    = time();
        $referer = strtolower($referer);

        if(!$id || !$type) return false;

        $insertSql = "INSERT INTO {{%log_share}} (user_id,type,relation_id,referer,created_at) VALUES ('$userId','$type','$id','$referer','$time')";
        Yii::$app->db->createCommand($insertSql)->execute();

        return true;
    }
    /**
     * [bannerLog 幻灯记录]
     * @param  [type]  $userId [用户ID]
     * @param  [type]  $id     [对应的ID]
     * @return [type]          [返回具体参数]
     */
    PUBLIC STATIC function bannerLog($userId,$id,$referer = 'ios'){

        $userId  = intval($userId);
        $id      = intval($id);
        $time    = time();
        $referer = strtolower($referer);

        if(!$id) return false;

        $insertSql = "INSERT INTO {{%log_banner}} (user_id,banner_id,referer,created_at) VALUES ('$userId','$id','$referer','$time')";
        Yii::$app->db->createCommand($insertSql)->execute();

        return true;
    }
    /**
     * [userIsGras 判断是否点赞评论]
     * @param  [type]  $userId [用户ID]
     * @param  [type]  $id     [评论ID]
     * @return [type]          [返回0或1]
     */
    PUBLIC STATIC function userIsLike($userId,$id){

        $userId  = intval($userId);
        $id      = intval($id);

        if(!$userId || !$id) return false;

        $sql        = "SELECT id FROM {{%comment_like}} WHERE  user_id =  '$userId'  AND comment_id = '$id'";

        $isExsit    = Yii::$app->db->createCommand($sql)->queryScalar();
        $isLike     = $isExsit ? true : false;

        return $isLike;  
    }
     /**
     * 用户金币操作
     * @param int $uid 用户ID
     * @param int $money 金币
     * @param string $content 详情
     * @param int $type 1为帐户，2为积分
     * @return bool
     */
    static function updateMoney($userId,$money,$content,$type = 1) {
        $time       =  time();
        $typeIndex  =  $type  ==  1 ? '账户' : '积分';
        $index      =  $type  ==  1 ? 'user_money': 'rank_points';
        
        // 验证用户是否升级，如果升级，添加通知消息
        // self::checkGrade($type,$userId,$money);
        $checkMoney_arr = ['点评产品','肤质测试','注册','回答问题','分享页面'];
        if(in_array($content,$checkMoney_arr)){
            $return = self::checkMoney($userId,$content);
            if(!$return) return false;
        }

        //增加代码 
        $userSql    = "UPDATE  {{%user}} SET $index = $index + $money WHERE id ='$userId'";
        Yii::$app->db->createCommand($userSql)->execute();
        //记录日志
        $recharge   = $money >= 0 ? '1' : '-1';
        $money      = abs($money);
        $topicSql   = "INSERT INTO  {{%user_account}} (user_id,type,pay,content,money,created_at,updated_at) 
                      VALUES('$userId','$recharge','$typeIndex','$content','$money','$time','$time')";
        Yii::$app->db->createCommand($topicSql)->execute();

        //活动判断--失了智了
        Huodong::checkUserCosmetics($userId);
        return true;
    }

    /**
     * 验证是否升级
     * @param int $uid 用户ID
     * @param int $money 积分
     * @param int $type 1为帐户，2为积分
     * @return bool
     */
    static function checkGrade($type,$userId,$money){
        if($type != 1){
            $sql    = "SELECT rank_points  FROM {{%user}}  WHERE id = '$userId'";
            $num    = Yii::$app->db->createCommand($sql)->queryScalar();
            $old    = self::getVipGrade($num);
            $new    = self::getVipGrade($num+$money);
            if($old['grade'] != $new['grade']){
               NoticeFunctions::notice($userId, 20+$new['grade']);
                //推送升级提示
               NoticeFunctions::JPushOne(['Alias'=>$userId,'id' => $userId,'type' => '3' , 'option' => 'upgrade','replaceStr' => $new['name']]); 
            }
        }
    }
    /**
     * 用户金币操作上限检查
     * @param int $uid  用户ID
     * @param string $type 类型
     * @param int $money 检查的金额
     * @return bool
     */
    static function checkMoney($userId,$type) {

        $time       = time();
        $start_time = strtotime(date('Y-m-d',$time));
        $end_time   = strtotime(date('Y-m-d',$time + 24 * 3600));
        $typeArr    = array(
            '分享帖子' => array('num' => 3 , 'type' => 1),
            '点赞帖子' => array('num' => 2 , 'type' => 1),
            '点评产品' => array('num' => 5 , 'type' => 1),
            '注册'     => array('num' => 1 , 'type' => 2),
            '完善个人信息' => array('num' => 1 , 'type' => 2),
            '绑定手机' => array('num' => 1 , 'type' => 2),
            '修改头像' => array('num' => 1 , 'type' => 2),
            '肤质测试' => array('num' => 1 , 'type' => 2),
            '回答问题' => array('num' => 3 , 'type' => 1),
            '分享页面' => array('num' => 3 , 'type' => 1),
        );
        //验证是否有上限检查
        if($typeArr[$type]){
            $content      = $type;
            $upperNum     = $typeArr[$type]['num'];
        }else{
            return false;
        }
        $whereStr = " user_id = '$userId' AND type = '1' AND pay = '积分' AND content = '$content' ";
        if($typeArr[$type]['type'] == 1){
            $whereStr .= " AND created_at >= '$start_time' AND created_at < '$end_time' ";
        }
        //查询已增加的金币 
        $userSql    = "SELECT COUNT(*) FROM {{%user_account}} WHERE $whereStr ";
        $todayNum = Yii::$app->db->createCommand($userSql)->queryScalar();
        return $todayNum < $upperNum;
    }
    /**
     * [getProductColumn 获取产品的下级栏目（从上至下）]
     * @param  string $parent [description]
     * @return [type]         [description]
     */
    static function getProductColumn($parent = '0') {
        $arr            = [];
        $sql            = "SELECT id,`parent_id`,`cate_name`,`cate_h5_img`,`cate_app_img` FROM {{%product_category}} 
                           WHERE  status =  '1'  AND parent_id = '$parent'
                           ORDER BY `sort` ASC";
        $categoryList   = Yii::$app->db->createCommand($sql)->queryAll();
        foreach ($categoryList as $v) {
            $v['cate_h5_img']   = Functions::get_image_path($v['cate_h5_img'],1);
            $v['cate_app_img']  = Functions::get_image_path($v['cate_app_img'],1);
            $v['sub']           = self::getProductColumn($v['id']);
            $arr[] = $v;
        }
        return $arr;
    }
    /**
     *  [getProductParentColumn 获取产品的上级栏目（从下至上）]
     * @param  string $parent [description]
     * @return [type]         [description]
     */
    static function getProductParentColumn($son_id = '') {
        if(empty($son_id)) return []; 
        $arr            = [];
        $sql            = "SELECT id,`parent_id`,`cate_name`,`cate_h5_img`,`cate_app_img` FROM {{%product_category}} 
                           WHERE  id = '$son_id' AND status =  '1'";
        $categoryList   = Yii::$app->db->createCommand($sql)->queryOne();

        $res = [];
        if($categoryList){
            $categoryList['cate_h5_img']   = Functions::get_image_path($categoryList['cate_h5_img'],1);
            $categoryList['cate_app_img']  = Functions::get_image_path($categoryList['cate_app_img'],1);
            $categoryList['parent']        = self::getProductParentColumn($categoryList['parent_id']);
            $res = $categoryList;
        }

        
        return $res;
    }
    /**
     * [getBrandColumn 获取品牌的下级栏目（从上至下）]
     * @param  string $parent [description]
     * @return [type]         [description]
     */
    static function getBrandColumn($parent = '0') {
        $arr            = [];
        $sql            = "SELECT id,`parent_id`,`name`,`img` FROM {{%brand}} 
                           WHERE  status =  '1'  AND parent_id = '$parent'";
        $categoryList   = Yii::$app->db->createCommand($sql)->queryAll();
        foreach ($categoryList as $v) {
            $v['img']   = Functions::get_image_path($v['img'],1);
            $v['sub']   = self::getBrandColumn($v['id']);
            $arr[] = $v;
        }
        return $arr;
    }
    
    /**
     * [getProductCateArr 组装栏目字符串]
     * @param  string $arr [description]
     * @param  string $str [description]
     * @return [type]         [description]
     */
    static function getProductCateArr($arr = [],$str = '') {
        if(empty($arr)) return [];
        $str = '';
        foreach ($arr as $k=>$v) {
            $str    .= $str ? ','.$v['id'] : $v['id'];
            if($v['sub']){
                $str    .= self::getProductCateArr($v['sub'],$str);
            }
        }
        return $str;
    }
    /**
     * [cateIsHide 判断分类是否需要隐藏]
     * @param  string $arr [description]
     * @return [type]      [description]
     */
    static function cateIsHide($arr = '') {

        if(empty($arr)) return false;
        //依照正式库的产品分类 54彩妆55身体护理
        $hide_cate = ['54','55'];
        if(!in_array($arr['id'],$hide_cate)){
            if(!empty($arr['parent'])){
                return self::cateIsHide($arr['parent']);
            }else{
                return false;
            }
        }else{
            return true;
        }



    }
    /**
     * [getArticleColumn 获取文章栏目]
     * @param  string $parent [description]
     * @return [type]         [description]
     */
    static function getArticleColumn($parent = '0') {
        $arr            = [];
        $sql            = "SELECT id,parent_id,cate_name,cate_img,`describe` FROM {{%article_category}} 
                           WHERE  status =  '1'  AND parent_id = '$parent'
                           ORDER BY `order` ASC";
        $categoryList   = Yii::$app->db->createCommand($sql)->queryAll();
        foreach ($categoryList as $v) {
            $v['cate_img']  = Functions::get_image_path($v['cate_img'],1);
            $v['sub']       = self::getArticleColumn($v['id']);
            $arr[] = $v;
        }
        return $arr;
    }
    /**
     * 过滤和排序所有文章分类，返回一个带有缩进级别的数组
     *
     * @access  private
     * @param   int     $cat_id     上级分类ID
     * @param   array   $arr        含有所有分类的数组
     * @param   int     $level      级别
     * @return  void
     */
    static function get_article_subs($parent = 0)
    {
        if(!$parent) return false;
        $ids     = '';
        $sql     = "SELECT id,parent_id,cate_name,cate_img,`describe` FROM {{%article_category}} 
                    WHERE  status =  '1'  AND parent_id = '$parent'
                    ORDER BY `order` ASC";
        $result  = Yii::$app->db->createCommand($sql)->queryAll();
 
        foreach ($result as $key=>$val){
           $ids .= ','.$val['id'];
           $ids .= self::get_article_subs($val['id']);
        }
        return $ids;
    }
    /**
     * [getReplyNum 获取问题回复数]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    static function getReplyNum($id){
        $id = intval($id);
        if(!$id) return 0;
        $sql     = "SELECT COUNT(*) FROM {{%ask_reply}} WHERE askid = '$id' ";
        $num     = Yii::$app->db->createCommand($sql)->queryScalar();
        return  $num ? $num : 0;
    }
    /**
     * [getReplyInfo 获取问题回复详情]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    static function getAskReplyInfo($id){
        $id = intval($id);
        if(!$id) return false;
        $sql        = "SELECT * FROM {{%ask_reply}} WHERE askid = '$id' ORDER BY add_time DESC ";
        $replyInfo  = Yii::$app->db->createCommand($sql)->queryOne();
        if($replyInfo){
            $replyInfo['reply'] = Tools::userTextDecode($replyInfo['reply']);
        }
        return  $replyInfo;
    }
    /**
     * [getRecommendComment 精华点评]
     * @return [type] [description]
     */
    static function getRecommendComment($param){
        $uid        = isset($param['uid'])  ? intval($param['uid']) : '' ;
        $page       = isset($param['page']) ? intval($param['page']) : 1 ;
        $pageSize   = isset($param['pageSize']) ? intval($param['pageSize']) : 10 ;
        //非上班期间随机生成几条
        $randIds    = Tools::getRandComment();
        
        //首页文章
        $orderBy  = " updated_at DESC ";
        $whereStr = " C.status = '1' AND C.is_digest = '1' AND C.type = '1' ";
        
        $groupBy  = " post_id ";
        

        $sql      = "SELECT COUNT(*) FROM (SELECT C.id AS num FROM {{%comment}} C  WHERE $whereStr GROUP BY $groupBy) AS T";

        $num      = Yii::$app->db->createCommand($sql)->queryScalar();
        //判断是否是随机范围
        if($randIds && $page == '1'){
            $whereStr .=  ' AND '.self:: db_create_in($randIds,'C.id');
            $pageSize  =  COUNT($randIds);
        }elseif($randIds && $page > '1'){
            $whereStr .=  ' AND '.self:: db_create_in($randIds,'C.id','1');
        }
        $pageMin  = ($page - 1) * $pageSize;

        $sql      =   "SELECT * FROM (SELECT P.id AS product_id,P.product_name,P.product_img,C.star as cStar,P.star,P.price,P.form,C.id AS comment_id,C.post_id,C.comment,C.like_num,C.user_id,C.updated_at
                    FROM {{%comment}} C LEFT JOIN {{%product_details}} P ON C.post_id = P.id
                    WHERE $whereStr
                    ORDER BY $orderBy
                    ) AS T
                    GROUP BY $groupBy
                    ORDER BY $orderBy
                    LIMIT $pageMin,$pageSize";

        $digestList = Yii::$app->db->createCommand($sql)->queryAll();
        $data       = [];

        if(!empty($digestList)){
            foreach ($digestList as $key => $value) {
                $data[$key]['user']       = [];
                $data[$key]['product']    = [];

                $commentUserId  = $value['user_id'];
                $userInfo       = self::getUserInfo($commentUserId);
                $data[$key]['user']['user_id']      = $userInfo['id'];
                $data[$key]['user']['username']     = $userInfo['username'];
                $data[$key]['user']['user_img']     = $userInfo['img'];
                $data[$key]['user']['age']          = $userInfo['age'];
                $data[$key]['user']['skin']         = $userInfo['skin'];

                $data[$key]['product']['product_id']   = $value['product_id'];
                $data[$key]['product']['product_name'] = $value['product_name'];
                $data[$key]['product']['product_img']  = Functions::get_image_path($value['product_img'],1);
                $data[$key]['product']['star']    = $value['star'];
                $data[$key]['product']['cStar']   = $value['cStar'];
                $data[$key]['product']['price'] = (float)$value['price'];
                $data[$key]['product']['form']    = $value['form'];

                $data[$key]['comment_id'] = $value['comment_id'];
                $data[$key]['comment']    = Tools::userTextDecode($value['comment']);
                $data[$key]['like_num']   = $value['like_num'];
                $data[$key]['isLike']     = Functions::userIsLike($uid,$value['comment_id']) ? 1 : 0;

                unset($commentUserId);
                unset($userInfo);
            }
        }
        return ['data' => $data,'num' => $num];
    }
    /**
     * [getRecommendAsk 首页提问信息]
     * @return [type] [description]
     */
    static function getRecommendAsk($param){
        $uid        = isset($param['uid'])  ? intval($param['uid']) : '' ;
        $page       = isset($param['page']) ? intval($param['page']) : 1 ;
        $pageSize   = isset($param['pageSize']) ? intval($param['pageSize']) : 1 ;
        if(empty($uid)){
            return [];
        }
        $pageMin= ($page - 1) * $pageSize;

        $c_sql    = "SELECT post_id FROM {{%comment}} WHERE type = 1 AND user_id = '$uid' GROUP BY post_id";
        $c_info   = Yii::$app->db->createCommand($c_sql)->queryAll();

        if(empty($c_info)){
            return [];
        }
        foreach ($c_info as $key => $value) {
            $arr[] = $value['post_id'];
        }
        $where_sql = self::db_create_in($arr,'product_id');
        $base_sql = "SELECT b.*,count(*) num FROM (
                        SELECT a.askid,subject,product_id,product_img,r.reply FROM {{%ask}} a 
                        LEFT JOIN {{%ask_reply}} r ON a.askid = r.askid 
                        LEFT JOIN {{%product_details}} pd ON a.product_id = pd.id
                        WHERE $where_sql ) b GROUP BY b.askid";

        $num_sql= "SELECT COUNT(*) FROM ($base_sql) t";
        $num    = Yii::$app->db->createCommand($num_sql)->queryScalar();

        //分页判断
        $maxPage            = ceil($num/$pageSize);
        if($page > $maxPage){
            return [];
        }

        $ask_sql = "SELECT * FROM ($base_sql) t ORDER BY askid DESC LIMIT $pageMin,$pageSize";
        $askinfo = Yii::$app->db->createCommand($ask_sql)->queryOne();
        if($askinfo){
            $askinfo['num'] = (empty($askinfo['reply']) && $askinfo['num'] == 1) ? '0' : $askinfo['num'];
            $askinfo['product_img'] = Functions::get_image_path($askinfo['product_img'],1);
            unset($askinfo['reply']);
        }
        return $askinfo;
    }
    /**
     * [getUserInfo 获取用户信息]
     * @param  [type] $uid [用户ID]
     * @return [type]      [description]
     */
    static function getUserInfo($userId){
        if(!$userId) return false;
        $userId     = intval($userId);

        $userSql    = "SELECT U.id,U.username,U.img,U.mobile,U.birth_year,U.rank_points,S.dry,S.tolerance,S.pigment,S.compact,S.skin_name 
        FROM {{%user}} U 
        LEFT JOIN {{%user_skin}} S  ON U.id = S.uid
        WHERE U.id = '$userId'";

        $userInfo   = Yii::$app->db->createCommand($userSql)->queryOne();
        if(!$userInfo) return false;

        $userInfo['img']    = Functions::get_image_path($userInfo['img'],1,150,150);
        $userInfo['username']  = Tools::userTextDecode($userInfo['username']);
        $userInfo['mobile'] = self::isMoblie($userInfo['mobile']) ? $userInfo['mobile'] : '';
        $userSkin           = Skin::evaluateSkin($userInfo);
        $userInfo['skin']   = '';
        $userInfo['age']    = isset($userInfo['birth_year'])  ? Functions::getUserAge($userInfo['birth_year']) : '';
        if($userSkin){
            unset($userSkin['skin_name']);
            $userInfo['skin'] = join(" | ",$userSkin);
        }
        return $userInfo;
    }
    /**
     * [getRankCategory 排行榜分类信息]
     * @param  [type]       $cate_id [产品分类id]
     * @param  [type]       $keyword [关键词]
     * @param  [type]       $except_rankid [除了这个之外的排行榜id]
     * @param  [type]       $num [显示数量]
     * @return [type]      [description]
     */
    static function getRankCategory($cate_id='',$keyword='',$except_rankid='',$num = 3){
        if(empty($cate_id) && empty($except_rankid)) return [];
        if(!empty($keyword)) return [];
        $cate_id     = intval($cate_id);

        //分类排行榜
        $whereStr = " category_id = $cate_id ";
        if(!empty($except_rankid)){
            $whereStr = " r.id != $except_rankid ";
        }
        $rankinfo = [];
        $selectSql  = "SELECT r.id AS rank_id,r.title,r.banner,rl.product_id,pd.product_img FROM {{%ranking}} r 
                        LEFT JOIN {{%ranking_list}} rl ON r.id = rl.ranking_id
                        LEFT JOIN {{%product_details}} pd ON pd.id = rl.product_id AND pd.status = 1
                        WHERE $whereStr
                        ORDER BY r.update_time DESC, rl.order ASC";
        $dbrank   = Yii::$app->db->createCommand($selectSql)->queryAll();
        if($dbrank){
            foreach ($dbrank as $key => $value) {
               $rankinfo[$value['rank_id']]['rank_id'] = $value['rank_id'];
               $rankinfo[$value['rank_id']]['num'] = isset($rankinfo[$value['rank_id']]['num']) ? $rankinfo[$value['rank_id']]['num']+1 : 1;
                $rankinfo[$value['rank_id']]['rank_name'] = $value['title'];
                if(isset($rankinfo[$value['rank_id']]['img_list'])){
                    if(count($rankinfo[$value['rank_id']]['img_list']) <= $num){
                        $rankinfo[$value['rank_id']]['img_list'][] = Functions::get_image_path($value['product_img'],1);
                    }
                }else{
                    $rankinfo[$value['rank_id']]['img_list'][] = Functions::get_image_path($value['product_img'],1);
                }
            }
            $rankinfo = array_values($rankinfo);
        }
        return array_slice($rankinfo,0,$num);
    }

    /**
     * 获取排行榜首页列表
     * @param int $num 获取排行榜个数
     * @return array
     */
    static function getRankIndex($num = NULL){
        $rankingSql  = "SELECT id, title, banner FROM {{%ranking}} WHERE status = '1'  ORDER BY update_time DESC";
        $num AND $rankingSql .= " LIMIT $num";
        $rankingList = Yii::$app->db->createCommand($rankingSql)->queryAll();
        foreach ($rankingList as $key => $ranking){
            $productSql = "SELECT rl.product_id, pd.product_name, pd.product_img FROM {{%ranking_list}} rl
                          LEFT JOIN {{%product_details}} pd ON rl.product_id = pd.id
                          WHERE rl.ranking_id = {$ranking['id']}
                          ORDER BY rl.order ASC LIMIT 3";
            $productList = Yii::$app->db->createCommand($productSql)->queryAll();
            foreach($productList as $item => $product){
                $productList[$item]['product_img'] = Functions::get_image_path($product['product_img']);
            }
            $rankingList[$key]['banner'] = Functions::get_image_path($ranking['banner']);
            $rankingList[$key]['product'] = $productList;
        }

        return $rankingList;
    }
    /**
     * [getCommentInfo 获取评论信息]
     * @param  [type] $id [评论ID]
     * @param  [type] $user_id [用户ID]
     * @return [type]      [description]
     */
    static function getCommentInfo($id,$user_id = 0,$isNew = 0){
        if(!$id) return false;
        //参数
        $id         = intval($id);
        $uid        = $user_id  ?  intval($user_id) : '';
        $rows       = ['user' => []];
        //排序
        $whereStr   = " C.id = '$id' AND C.status = '1' ";
        $rows       = ['user' => [],'comment' => []];

        $sql    = "SELECT C.id,C.type,C.first_id,C.parent_id,C.user_id,C.post_id,C.comment,C.like_num,C.is_digest,C.created_at,C.star
                   FROM {{%comment}} C 
                   WHERE $whereStr";
        $commentInfo   =    Yii::$app->db->createCommand($sql)->queryOne();

        if(!$commentInfo){
            return false;
        }
        $userInfo  = Functions::getUserInfo($commentInfo['user_id']);
        $rows['user']['user_id']      = $userInfo['id'];
        $rows['user']['username']     = $userInfo['username'];
        $rows['user']['user_img']     = $userInfo['img'];
        $rows['user']['age']          = $userInfo['age'];
        $rows['user']['skin']         = $userInfo['skin'];
        $rows['id']        = $id;
        $rows['type']      = $commentInfo['type'];
        $rows['post_id']   = $commentInfo['post_id'];
        $rows['attachment']= Functions::getAttachment($id,1,$isNew);
        $rows['isLike']    = Functions::userIsLike($uid,$id) ? 1 : 0;
        $rows['like_num']  = $commentInfo['like_num'];
        $rows['is_digest'] = $commentInfo['is_digest'];
        $rows['star']      = $commentInfo['star'];
        $rows['level']     = $commentInfo['parent_id'] == $commentInfo['first_id'] ? 1 : 2;
        $rows['comment']   = Tools::userTextDecode($commentInfo['comment']);
        $rows['created_at']= $commentInfo['created_at'];
        return $rows;
    }
    /**
     * [commentReplyList 评论回复列表]
     * @param  [type]  $id  [评论ID]
     * @param  [type]  $uid [用户ID]
     * @param  integer $page   [页数]
     * @param  integer $pageSize [每页条数]
     * @return [type]          [description]
    */
    PUBLIC STATIC function commentReplyList($params){
        //参数
        $id         = isset($params['id']) ? intval($params['id']) : '';
        $uid        = isset($params['uid']) ? intval($params['uid']) : '';
        $pageSize   = isset($params['pageSize']) ? intval($params['pageSize']) : 20;
        $page       = isset($params['page']) ? intval($params['page']) : 1;

        $replyData  = [];
        $return     = [];
        $pageMin    = ($page - 1) * $pageSize;
        $whereStr   = " C.first_id = '$id' AND C.status = '1' ";
        $orderBy    = " C.created_at DESC";

        $sql    = "SELECT COUNT(*) AS num FROM {{%comment}} C WHERE $whereStr";
        $num    = Yii::$app->db->createCommand($sql)->queryScalar();

        $commentSql = "SELECT C.id,C.user_id,C.parent_id,C.comment,C.like_num FROM {{%comment}} C 
                       WHERE     $whereStr 
                       ORDER BY  $orderBy
                       LIMIT $pageMin,$pageSize";
        $replyList  =  Yii::$app->db->createCommand($commentSql)->queryAll();

        
        if(!empty($replyList)){
            foreach ($replyList as $key => $value) {
                $replyData[$key] = Functions::getCommentInfo($value['id'],$uid);
                $replyInfo       = Functions::getCommentInfo($value['parent_id'],$uid);
                $replyData[$key]['replyUserId']   = $replyInfo['user']['user_id'];
                $replyData[$key]['replyUserName'] = $replyInfo['user']['username'];
                unset($replyInfo);
            }
        }
        if($replyData) $rows['replyList'] = $replyData;

        $return = ['data' => $replyData,'total' => $num];
        return $return;
    }
    /**
     * [getRankingInfo 排行榜信息]
     * @param  [type] $id [排行ID]
     * @return [type]     [description]
     */
    PUBLIC STATIC function getRankingInfo($id){
        //参数
        $id         = intval($id);
        if(!$id) return false;

        $return     = [];
        $whereStr   = "R.id = '$id'";
        $sql        = "SELECT * FROM {{%ranking}} R  WHERE $whereStr";
        $rankingInfo= Yii::$app->db->createCommand($sql)->queryOne();
        
        if(!$rankingInfo) return false;
        $rankingInfo['banner'] = Functions::get_image_path($rankingInfo['banner']);

        $rankingSql = " SELECT R.product_id,P.product_name,P.product_img,P.star,P.form,P.price,P.product_explain FROM {{%ranking_list}} R 
                        LEFT JOIN {{%product_details}} P ON R.product_id = P.id
                        WHERE R.ranking_id = '$id' ORDER BY  R.order";
        $rankingIist= Yii::$app->db->createCommand($rankingSql)->queryAll();

        if(!empty($rankingIist)){
            foreach ($rankingIist as $key => $value) {
                $rankingIist[$key]['price']     = $value['price'] ? $value['price'] : '暂无报价' ;
                $rankingIist[$key]['form']      = $value['form'] ? $value['form'] : '暂无规格' ;
                $rankingIist[$key]['product_img'] = Functions::get_image_path($value['product_img'],1);
            }
        }

        $return = ['rankingInfo' => $rankingInfo,'productList' => $rankingIist];
        return $return;
    }
    /**
     * [getRankingRelation 排行榜关联列表]
     * @param  [type] $id [排行ID]
     * @param  [type] $num[显示条数]
     * @return [type]     [description]
     */
    PUBLIC STATIC function getRankingRelation($id,$num = '3',$is_rand = true){
        //参数
        $id         = intval($id);
        $num        = intval($num);
        if(!$id) return false;

        $return     = [];
        $whereStr   = "R.id = '$id'";
        $sql        = "SELECT * FROM {{%ranking}} R  WHERE $whereStr";
        $rankingInfo= Yii::$app->db->createCommand($sql)->queryOne();
        
        if(!$rankingInfo) return false;

        $whereStr   = "id != '$id'";
        if($is_rand){
            $whereStr .= " ORDER BY RAND() " ;
        }
        $sql        = "SELECT id,title FROM {{%ranking}} R  WHERE $whereStr LIMIT $num";
        $rankingList= Yii::$app->db->createCommand($sql)->queryAll();

        foreach ($rankingList as $key => $value) {
            $list     = self::getRelationInfo($value['id']);
            $value['rank_id'] = $value['id'];
            $return[] = ['rankingInfo' => $value,'productList' => $list];
            unset($list);
        }
        return $return;
    }
    /**
     * [getRelationInfo 排行榜关联信息]
     * @param  [type] $id [排行ID]
     * @param  [type] $num[显示条数]
     * @return [type]     [description]
     */
    PUBLIC STATIC function getRelationInfo($id){
        //参数
        $id         = intval($id);
        if(!$id) return false;

        $rankingSql = " SELECT P.product_name,P.product_img,P.star,P.form,P.price FROM {{%ranking_list}} R 
                        LEFT JOIN {{%product_details}} P ON R.product_id = P.id
                        WHERE R.ranking_id = '$id' ORDER BY  R.order LIMIT 3";
        $rankingIist= Yii::$app->db->createCommand($rankingSql)->queryAll();

        if(!empty($rankingIist)){
            foreach ($rankingIist as $key => $value) {
                $rankingIist[$key]['product_img'] = Functions::get_image_path($value['product_img'],1);
            }
        }

        return $rankingIist;
    }
    /**
     * [getbaikeList 百科列表]
     * @param  [type] $id [百科ID]
     * @return [type]     [description]
     */
    PUBLIC STATIC function getbaikeList($skin_id=0,$is_rand=true,$pageinfo=[]){
        //参数
        $skin_id    = intval($skin_id);
        // $num        = intval($num);
        
        if(!$skin_id){
            $whereStr  = "1";
        } else{
            $whereStr   = "K.skin_id = '$skin_id'";
        };

        if($is_rand){
            $whereStr .= " ORDER BY RAND() " ;
        }else{
            $whereStr .= " ORDER BY K.update_time DESC " ;
        }

        if($pageinfo){
            $whereStr .= " limit $pageinfo[pageMin],$pageinfo[pageSize]" ;
        }
        $sql        = " SELECT K.id,K.question,K.skin_id,K.skin_name,W.content,W.picture,W.shortcontent FROM {{%skin_baike}} K  
                        LEFT JOIN {{%skin_baike_answer}} W ON K.id = W.qid
                        WHERE $whereStr";
        $baikeAll   = Yii::$app->db->createCommand($sql)->queryAll();
        
        return $baikeAll;
    }
    /**
     * [getbaikeInfo 百科信息]
     * @param  [type] $id [百科ID]
     * @return [type]     [description]
     */
    PUBLIC STATIC function getbaikeInfo($id){
        //参数
        $id         = intval($id);
        if(!$id) return false;

        $whereStr   = "K.id = '$id'";
        $sql        = " SELECT K.id,K.question,K.skin_id,K.skin_name,W.content,W.picture,K.like_num,W.shortcontent FROM {{%skin_baike}} K  
                        LEFT JOIN {{%skin_baike_answer}} W ON K.id = W.qid
                        WHERE $whereStr ";
        $baikeInfo  = Yii::$app->db->createCommand($sql)->queryOne();
        
        return $baikeInfo;
    }
    /**
     * [getBaikeRelation 百科关联]
     * @param  [type] $id [排行ID]
     * @param  [type] $num[显示条数]
     * @return [type]     [description]
     */
    PUBLIC STATIC function getBaikeRelation($id,$num = '3'){
        //参数
        $id         = intval($id);
        $num        = intval($num);
        
        $baikeInfo  = self::getbaikeInfo($id);
        if(!$id || !$baikeInfo) return false;

        $skinId     = $baikeInfo['skin_id'];
        $baikeId    = $baikeInfo['id'];

        $whereStr   = "K.skin_id = '$skinId' AND K.id != '$baikeId'";

        $sql        = " SELECT K.id,K.question,K.skin_id,K.skin_name,W.content,W.picture,W.shortcontent,K.like_num FROM {{%skin_baike}} K  
                        LEFT JOIN {{%skin_baike_answer}} W ON K.id = W.qid
                        WHERE $whereStr
                        ORDER BY rand()
                        LIMIT $num";
        $baikeAll   = Yii::$app->db->createCommand($sql)->queryAll();
        $count = count($baikeAll);
        if($count < 3){
            $num = 3 - $count;
            $otherSql = " SELECT K.id,K.question,K.skin_id,K.skin_name,W.content,W.picture,W.shortcontent,K.like_num FROM {{%skin_baike}} K  
                        LEFT JOIN {{%skin_baike_answer}} W ON K.id = W.qid
                        WHERE K.id != {$baikeId}
                        ORDER BY rand()
                        LIMIT $num";
            $baikeOther = Yii::$app->db->createCommand($otherSql)->queryAll();
            $baikeAll = array_merge($baikeAll, $baikeOther);
        }
        
        return $baikeAll;
    }
    /**
     * 发送短信接口
     * @param string $m --号码
     * @return $c --内容
     * @auth lzj
     */
    public static function captcha($m,$c)
    {

        $url = "https://sms.yunpian.com/v2/sms/single_send.json";
        $apikey = "842b480cd350eda35800598b6387fba4"; //修改为您的apikey(https://www.yunpian.com)登陆官网后获取
        $mobile = $m; //请用自己的手机号代替
        $text = "【颜究院】您的验证码是：{$c}。请不要把验证码泄露给其他人。";
        $data = array('text' => $text, 'apikey' => $apikey, 'mobile' => $mobile);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept:text/plain;charset=utf-8', 'Content-Type:application/x-www-form-urlencoded','charset=utf-8'));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $output = curl_exec($ch);
        curl_close($ch);
        $output = json_decode($output);
        return $output; 
    }
    /**
     * [uploadUrlimg 上传图片到oss]
     * @param  [type]  $imgUrl  [图片地址]
     * @param  [type]  $is_del  [是否删除]
     * @return [str]           [图片地址字符串]
     */
    public static function uploadUrlimg($imgUrl,$type = 'photo'){

        if(!$imgUrl) return false;

        $imgData    = self::http_judu($imgUrl);
        $imgType    = self::check_image_type($imgData);
        $imgTypeArr = array('jpg','gif','png');

        if(!in_array($imgType,$imgTypeArr)){
            return false;
        }
        //准备上传
        $serverName =  Yii::$app->params['isOnline']; //$_SERVER['SERVER_NAME'];

        if($serverName){
            $savePath   =   'uploads';
        }else{
            $savePath   =   'cs/uploads';
        }
        $img_name   =   $type .'/'.date('Ymd').'/'.time().rand(100000,999999).".".$imgType;                                       
        $filename   =   $savePath.'/'.$img_name;

        $ossFile    =   $filename;
        $dirname    =   dirname($filename);

        if (!file_exists($dirname) && !mkdir($dirname, 0777, true)) {
          return false;
        }

        try {
            file_put_contents($filename,$imgData);
            $oss_obj = new OssUpload();
            $is_upload = $oss_obj->upload($filename,$filename);
            if(!$is_upload){
                return false;
            }
            $return = $img_name;
        } catch (Exception $e) {
            $return = $e->getMessage();
        }
        return $return;
    }
    /**
     * [uploadOssimg 上传图片到服务器]
     * @param  [type]  $imgUrl  [图片地址]
     * @param  [type]  $is_del  [是否删除]
     * @return [str]           [图片地址字符串]
     */
    public static function uploadOssimg($imgUrl){

        if(!$imgUrl) return false;

        $imgData    = self::http_judu($imgUrl);
        $imgType    = self::check_image_type($imgData);
        $imgTypeArr = array('jpg','gif','png');

        if(!in_array($imgType,$imgTypeArr)){
            return false;
        }
        //准备上传
        $serverName =  Yii::$app->params['uploadsUrl'];

        $filename   = '';
        $filename   = str_replace($serverName,'uploads/',$imgUrl);
        $dirname    = dirname($filename);

        if (!file_exists($dirname) && !mkdir($dirname, 0777, true)) {
          return false;
        }
        try {
            $return = file_put_contents($filename,$imgData);
        } catch (Exception $e) {
            $return = $e->getMessage();
        }
        return $return;
    }

    PUBLIC STATIC function getLinkPrice($goods_ids,$type=1){
        if(!$goods_ids) return false;

        $ids_str = self::link_create_id($goods_ids,$type);
        switch ($type) {
            case '1':
                //文档出处：http://open.taobao.com/docs/api.htm?spm=a219a.7395905.0.0.6yR5qW&apiId=24518
                $url = 'https://eco.taobao.com/router/rest';
                $arr  = array(
                    'method'=>'taobao.tbk.item.info.get',
                    'app_key'=>Yii::$app->params['ProductLink']['taobao']['AppKey'],
                    'sign_method'=>'md5',
                    'sign'=>'1',
                    'timestamp'=>date('Y-m-d H:i:s'),
                    'v'=>'2.0',
                    'format'=>'json',
                    'fields'=>'num_iid,title,reserve_price,zk_final_price,pict_url',
                    'num_iids'=>$ids_str,
                    );
                $sub_type = 'post';
                $arr['sign'] = self::getTaobaoSign($arr);
                break;
            case '2':
                //文档出处：http://open.taobao.com/docs/api.htm?spm=a219a.7395905.0.0.6yR5qW&apiId=24518
                //https://jos.jd.com/api/detail.htm?apiName=jingdong.ware.price.get&id=386
                $url = 'https://api.jd.com/routerjson';
                $arr  = array(
                    'method'=>'jingdong.ware.price.get',
                    'app_key'=>Yii::$app->params['ProductLink']['jd']['AppKey'],
                    'timestamp'=>date('Y-m-d H:i:s'),
                    'v'=>'2.0',
                    'format'=>'json',
                    'sku_id'=>$ids_str,
                );
                $sub_type = 'get';
                break;
            default:
                break;
        }

        $return = self::http_judu($url,$arr,$sub_type);
        $arr    = json_decode($return,true);

        $res    = [];
        if(!empty($arr)){
            switch ($type) {
                case '1':
                    if(isset($arr['tbk_item_info_get_response']['results']['n_tbk_item'])){
                        $res_arr = $arr['tbk_item_info_get_response']['results']['n_tbk_item'];
                        foreach ($res_arr as $k => $v) {
                            $num_iid = (string)$v['num_iid'];
                            $res[$num_iid] = $v['zk_final_price'];
                        }
                    }
                    break;
                case '2':
                    if(isset($arr['jingdong_ware_price_get_responce']['price_changes'])){
                        $res_arr = $arr['jingdong_ware_price_get_responce']['price_changes'];
                        foreach ($res_arr as $k => $v) {
                            if(isset($v['price']) && $v['price'] > 0){
                                $id = substr($v['sku_id'],2);
                                $kid = (string)$id;
                                $res[$kid] = $v['price'];
                            }
                        }
                    }
                    break;
                default:
                    break;
            }

        }
        return $res;
    }

    /**
     * 获取淘宝客商品信息
     * @param $goods_ids
     * @param int $type
     * @return array
     */
    PUBLIC STATIC function getProductLink($goods_ids,$type=1){
        if(!$goods_ids) return false;

        $ids_str = self::link_create_id($goods_ids,$type);

        //文档出处：http://open.taobao.com/docs/api.htm?spm=a219a.7395905.0.0.6yR5qW&apiId=24518
        $url = 'https://eco.taobao.com/router/rest';
        $arr  = array(
            'method'=>'taobao.tbk.item.info.get',
            'app_key'=>Yii::$app->params['ProductLink']['taobao']['AppKey'],
            'sign_method'=>'md5',
            'sign'=>'1',
            'timestamp'=>date('Y-m-d H:i:s'),
            'v'=>'2.0',
            'format'=>'json',
            'fields'=>'num_iid,title,zk_final_price,pict_url,nick',
            'num_iids'=>$ids_str,
        );
        $sub_type = 'post';
        $arr['sign'] = self::getTaobaoSign($arr);


        $return = self::http_judu($url,$arr,$sub_type);
        $arr    = json_decode($return,true);

        $arr = isset($arr['tbk_item_info_get_response']['results']['n_tbk_item']) ? $arr['tbk_item_info_get_response']['results']['n_tbk_item'] : '';

        return $arr;
    }

    /**
     * 获取商品优惠券链接
     * @param int $id 商品淘宝id
     * @return array
     */
    public static function getCoupon($id)
    {
        $url = 'https://m.680ju.com/api/taobao/urlTrans.json';
        $data['appKey'] = '8927519291';
        $data['appSecret'] = 'pBopquSDQnZeKfrYsZiprU6UoAe7cyrf';
        $data['itemId'] = $id;
        $data['pid'] = 'mm_124287267_25890794_99532920';

        $result = self::http_judu($url,$data);
        $arr = json_decode($result,true);
        $arr = $arr['code'] == 1 && isset($arr['item']) ? $arr['item'] : [];

        return $arr;
    }
    /**
     * 获取商品优惠券信息
     * @param int $id 商品淘宝id
     * @return array
     */
    public static function getCouponInfo($id)
    {
        if(!$id) return false;
        $return = ['title' => '' ,'amountList' => [],'url' => ''];

        $params = ['q' => 'https://item.taobao.com/item.htm?id='.$id];
        $url    = 'http://pub.alimama.com/items/search.json';

        $result = self::http_judu($url,$params);
        $data   = json_decode($result,true);

        if($data['data']['pageList']){
            //获取优惠券列表
            foreach ($data['data']['pageList'] as $key => $value) {
                $return['title'] = $value['title'];
                $value['couponAmount'] ? $return['amountList'][] = $value['couponAmount'] : '';
            }
        }else{
            return false;
        }
        return $return;
    }
    /**
     * 拼接推广的商品的id
     * @param  [type]  $goods_arr [id数组]
     * @param  integer $type      [1淘宝2京东]
     * @return [type]             [description]
     */
    PUBLIC STATIC function link_create_id($goods_arr=[],$type=1){
        if(!$goods_arr) return false;

        if(!is_array($goods_arr) ) {
            return $type!=2 ? $goods_arr : 'J_'.$goods_arr;
        }

        if($type==1) return implode(',', $goods_arr);

        $str = 'J_';
        $str .=implode(',J_', $goods_arr);
        return $str;
    }
    /**
     *  作用：淘宝客的生成签名
    */
    PUBLIC STATIC function getTaobaoSign($Obj) {
        $key = Yii::$app->params['ProductLink']['taobao']['AppSecret'];
        foreach ($Obj as $k => $v) {
            $Parameters[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = self::formatBizQueryParaMap($Parameters, false);
        $String = $key.$String.$key;
        //签名步骤三：MD5加密
        $String = md5($String);
        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
        return $result_;
    }
    /**
     *  作用：格式化参数，签名过程需要使用
     */
    PUBLIC STATIC function formatBizQueryParaMap($paraMap, $urlencode) {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if($urlencode) {
                $v = urlencode($v);
            }
            if ($k != 'sign' && $k != 'sign_type' && $k != 'code'){
                $buff .= $k . $v ;
            }
        }
        $reqPar;
        if (strlen($buff) > 0) {
            $reqPar = $buff;//substr($buff, 0, strlen($buff)-1);
        }
        return $reqPar;
    }
    /**
     * [check_image_type 判断文件流类型]
     * @param  [type] $image [description]
     * @return [type]        [description]
     */
    PUBLIC STATIC  function check_image_type($image){
        $bits = array(
            'jpg' => "\xFF\xD8\xFF",
            'gif' => "GIF",
            'png' => "\x89\x50\x4e\x47\x0d\x0a\x1a\x0a",
            'mbp' => 'BM',
        );
        foreach ($bits as $type => $bit) {
            if (substr($image, 0, strlen($bit)) === $bit) {
                return $type;
            }
        }
        return 'UNKNOWN IMAGE TYPE';
    }
    /**
     * 获取banner列表
     * @param  integer $position [位置id]
     * @return array[res]    [数组]
     */
    PUBLIC STATIC  function getBannerList($position=1){
        $time   = time();
        $res = [];
        //查询轮播
        $sql = "SELECT id, title, img, url,type,relation_id 
                FROM {{%banner}} 
                WHERE  status = '1' AND position = '$position' AND start_time < '$time' AND end_time > '$time'  
                ORDER BY sort_id ASC,start_time DESC";

        $res   = Yii::$app->db->createCommand($sql)->queryAll();
        if(!empty($res)){
            foreach ($res as $key => $value) {
                $res[$key]['img']  = Functions::get_image_path($value['img']);
            }
        }
        return $res;
    }
    /**
     * 获取回答相关信息
     * @param  string $whereStr [description]
     * @param  string $orderby  [description]
     * @param  [type] $page     [description]
     * @param  [type] $pageSize [description]
     * @return [type]           [description]
     */
    PUBLIC STATIC  function getAskList($whereStr='',$orderby = 't.add_time DESC',$page,$pageSize){
        $res = [];

        $pageMin= ($page - 1) * $pageSize; 

        $selectSql      = " SELECT * , COUNT(*) num FROM (
                                SELECT A.askid,A.subject,AR.reply,AR.replyid,AR.like_num,A.add_time,PD.product_img FROM {{%ask}} A 
                                lEFT JOIN {{%product_details}} PD ON PD.id = A.product_id
                                lEFT JOIN {{%ask_reply}} AR ON A.askid = AR.askid WHERE A.status = 1 $whereStr ORDER BY AR.like_num DESC) t
                            GROUP BY t.askid ORDER BY $orderby LIMIT $pageMin,$pageSize";//再建临时表的原因是里面的数据可控可排序
        $res         = Yii::$app->db->createCommand($selectSql)->queryAll();
        if(!empty($res)){
            foreach($res as $k=>$v){
                $res[$k]['product_img'] = self::get_image_path($v['product_img']);
                $res[$k]['askReplyId']    = $v['replyid'] ? $v['replyid'] : 0;
                $res[$k]['like_num']      = $v['like_num'] ? $v['like_num'] : 0;
                $res[$k]['reply']         = $v['reply'] ? $v['reply'] :'';
                unset($res[$k]['replyid']);
                if(empty($v['reply'])){
                    $res[$k]['num']         = $v['num'] - 1;//提问的问题没有回答时num要减一
                }
            }
        }
        
        return $res;
    }
    /**
     * [strIsImg 正则匹配字符串是否是图片样式]
     * @param  [type] $str [description]
     * @return [type]      [description]
     */
    PUBLIC STATIC  function strIsImg($str){
        if(!$str) return '';
        preg_match('/<img (.*?) src=\"(.+?)\".*?>/',$str,$match);
        if($match){
            return '图片';//$match['2']
        }else{
            return Tools::userTextDecode($str);;
        }
    }
    /**
     * [getAppMenu 获取APP首页菜单]
     * @return [type] [description]
     */
    PUBLIC STATIC function getAppMenu(){
        $cache     =    Yii::$app->cache;
        //获取APP首页菜单
        // $appMenu    = $cache->get('appMenu');
        // if(!$appMenu){
            //分类信息
            $sql            =   "SELECT * FROM {{%app_menu}} WHERE status = 1  ORDER BY sort ASC";
            $appMenu        =   Yii::$app->db->createCommand($sql)->queryAll();
            foreach ($appMenu as $key => $value) {
                $newAdditional  = '';
                $additional     = ['id' => '' ,'thumb_img' => ''];
                switch ($value['type']) {
                    case '3':
                        $newAdditional = self::getNewBonus();
                        break;
                    case '4':
                        $newAdditional = self::getNewVideo();
                        break;
                    case '6':
                        $newAdditional = self::getNewHuodong();
                        break;
                }
                $appMenu[$key]['img'] = self::get_image_path($value['img']);
                $appMenu[$key]['additional'] = $newAdditional ? $newAdditional : $additional;
                unset($additional);
            }
            // $cache->set('appMenu',$appMenu,60);
        // }
        return $appMenu; 
    }
    /**
     * [getNewBonus 获取最新优惠券]
     * @return [type] [description]
     */
    PUBLIC STATIC  function getNewBonus(){
        //条件
        $time     = date('Y-m-d');
        $orderBy  = " B.updated_at DESC, B.id ASC ";
        $whereStr = " B.status = '1' AND is_off = 1";

        $sql      =   "SELECT B.id,B.goods_id,B.price,B.start_date,B.end_date FROM {{%product_bonus}} B WHERE $whereStr  ORDER BY $orderBy  LIMIT 1";

        $goodsInfo= Yii::$app->db->createCommand($sql)->queryOne();

        if($goodsInfo){
            $info = self::getProductLink($goodsInfo['goods_id']);
            $goodsInfo['thumb_img']  = $info ? $info['0']['pict_url'] : '';
            $goodsInfo['start_date'] > $time || $goodsInfo['end_date'] < $time ? $goodsInfo['price'] = 0 : '';
            unset($goodsInfo['goods_id']);
            unset($goodsInfo['start_date']);
            unset($goodsInfo['end_date']);
        }
        //VAR_DUMP($goodsInfo);DIE;
        return $goodsInfo;
    }   
    /**
     * [getNewVideo 获取最新视频]
     * @return [type] [description]
     */
    PUBLIC STATIC  function getNewVideo(){
        //条件
        $orderBy  = " V.update_time DESC ";
        $whereStr = " V.status = '1' AND V.type = '1' AND V.is_complete = '1'";

        $sql      =   "SELECT V.id,V.icon_img FROM {{%video}} V WHERE $whereStr  ORDER BY $orderBy  LIMIT 1";

        $videoInfo= Yii::$app->db->createCommand($sql)->queryOne();

        if($videoInfo){
            $videoInfo['thumb_img']  = self::get_image_path($videoInfo['icon_img']);
        }
        
        return $videoInfo;
    }
    /**
     * [getNewHuodong 获取最新活动]
     * @return [type] [description]
     */
    PUBLIC STATIC  function getNewHuodong(){
        //条件
        $time     = time();
        $orderBy  = " H.id DESC ";
        $whereStr = " H.status = '1' AND H.starttime <= '$time' AND H.endtime >= '$time'";

        $sql      =   "SELECT H.id,H.picture FROM {{%huodong_special_config}} H WHERE $whereStr  ORDER BY $orderBy  LIMIT 1";

        $Huodong  = Yii::$app->db->createCommand($sql)->queryOne();
        if($Huodong){
            $Huodong['thumb_img']  = self::get_image_path($Huodong['picture']);
            unset($Huodong['picture']);
        }
        return $Huodong;
    }
    /**
     * [getVideosInfo 获取视频详情]
     * @param  [type] $id [description]
     * @return [type]         [description]
     */
    PUBLIC STATIC  function getVideosInfo($id){
        $id         = intval($id);
        //条件
        $whereStr   = " V.status = '1' AND V.id = '$id' AND  V.is_complete = '1'";

        $sql        = "SELECT V.id,V.title,V.video,V.desc,V.product_id,V.thumb_img,V.comment_num,V.duration FROM {{%video}} V WHERE $whereStr";

        $videoInfo  = Yii::$app->db->createCommand($sql)->queryOne();

        if(empty($videoInfo)){
            return false;
        }

        $videoInfo['video']      = self::get_image_path($videoInfo['video']);
        $videoInfo['thumb_img']  = self::get_image_path($videoInfo['thumb_img']);
        $videoInfo['linkUrl']    = Yii::$app->params['mfrontendUrl'].'app/video?id='.$videoInfo['id'];

        //关联的产品
        $videoInfo['products']   = [];
        if($videoInfo['product_id']){
            $whereStr = self::db_create_in($videoInfo['product_id'],'P.id');

            $sql    = "SELECT P.id, P.product_name,P.price,P.form,P.star,P.product_img
                       FROM {{%product_details}} P 
                       WHERE $whereStr";
            $rows       = Yii::$app->db->createCommand($sql)->queryAll();

            if($rows){
                foreach ($rows as $key => $value) {
                    $rows[$key]['specifications'] = $value['price'] ? '¥'.$value['price'] : '';
                    $rows[$key]['specifications'] .= $value['form'] &&  $value['price'] ? '／' : '';
                    $rows[$key]['specifications'] .= $value['form'] ? $value['form'] : '';
                    $rows[$key]['product_img']  = $value['product_img'] ? Functions::get_image_path($value['product_img'],1) : Functions::get_image_path('default.jpg');
                }
            }
            $videoInfo['products'] = $rows;
        }
        unset($videoInfo['product_id']);
        return $videoInfo;
    }
    /**
     * [getVideosList 获取视频列表]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    PUBLIC STATIC  function getVideosList($param){
        $page       = isset($param['page']) ? intval($param['page']) : 1 ;
        $pageSize   = isset($param['pageSize']) ? intval($param['pageSize']) : 10 ;

        //条件
        $orderBy  = " V.update_time DESC ";
        $whereStr = " V.status = '1' AND V.type = '1' AND V.is_complete = '1'";

        $sql      = "SELECT COUNT(*) FROM  {{%video}} V WHERE $whereStr";
        $num      = Yii::$app->db->createCommand($sql)->queryScalar();

        $pageMin  = ($page - 1) * $pageSize;

        $sql      =   "SELECT V.id,V.title,V.thumb_img,V.duration FROM {{%video}} V WHERE $whereStr  ORDER BY $orderBy  LIMIT $pageMin,$pageSize";

        $videosList     = Yii::$app->db->createCommand($sql)->queryAll();
        $newVideosList  = [];
        foreach ($videosList as $key => $value) {
            $videoInfo               =  $value;
            $videoInfo['username']   = '颜究院';
            $videoInfo['headerImg']  = self::get_image_path('photo/admin_user.png');
            $videoInfo['thumb_img']  = self::get_image_path($value['thumb_img']);
            $videoInfo['linkUrl']    = Yii::$app->params['mfrontendUrl'].'app/video?id='.$value['id'];
            //$videoInfo['duration']   = Tools::secondsToHour($value['duration']);
            $newVideosList[]         = $videoInfo;
            unset($videoInfo); 
        }

        return ['data' => $newVideosList,'num' => $num];
    }
    /**
     * [indexVideosList 获取视频列表]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    PUBLIC STATIC  function indexVideosList($params){
        $page       = isset($params['page']) ? intval($params['page']) : 1 ;
        $pageSize   = isset($params['pageSize']) ? intval($params['pageSize']) : 10 ;

        //首页文章
        $orderBy  = " V.update_time DESC ";
        $whereStr = " V.status = '1' AND V.type = '1' AND V.is_complete = '1'";

        //计算开始数，及显示数
        $start  = ($page - 1 ) * $pageSize;
        $end    = $page  * $pageSize;
        $pageMin= $start ? intval($start / 3) : 0;

        $size   = 0;
        for ($i = $start; $i <= $end; $i++) { 
            if($i % 3 == 0) $size++;                 
        }

        $sql      =   "SELECT V.id,V.title,V.thumb_img,V.duration FROM {{%video}} V WHERE $whereStr  ORDER BY $orderBy  LIMIT $pageMin,$size";
        $videosList     = Yii::$app->db->createCommand($sql)->queryAll();
        $newVideosList  = [];
        foreach ($videosList as $key => $value) {
            $videoInfo               =  $value;
            $videoInfo['username']   = '颜究院';
            $videoInfo['headerImg']  = self::get_image_path('photo/admin_user.png');
            $videoInfo['thumb_img']  = self::get_image_path($value['thumb_img']);
            $videoInfo['linkUrl']    = Yii::$app->params['mfrontendUrl'].'app/video?id='.$value['id'];
            //$videoInfo['duration']   = Tools::secondsToHour($value['duration']);
            $newVideosList[]         = $videoInfo;
            unset($videoInfo); 
        }

        return $newVideosList;
    }
    /**
     * [videoClick 视频点击]
     * @param  [type] $id [视频ID]
     * @return [type]     [description]
     */
    PUBLIC STATIC  function  videoClick($id){
        $id  = intval($id);
        if(!$id) return false;

        $sql = "UPDATE {{%video}} SET click_num = click_num + 1 WHERE id = '$id'";
        Yii::$app->db->createCommand($sql)->execute();
        return true;
    }

    /**
     * [getStickArticle 获取所有置顶文章]
     * @param  [type] $id [视频ID]
     * @return [type]     [description]
     */
    PUBLIC STATIC  function  getStickArticle($field = ''){
        $field_str = $field ? $field : '*';
        $stick_sql = "SELECT ".$field_str." FROM {{%article}} WHERE stick = 1 ORDER BY created_at DESC";
        $stick_res  = Yii::$app->db->createCommand($stick_sql)->queryAll();
        return $stick_res;
    }
    /**
     * [getNewUserProduct 获取用户最新的我在用信息]
     * @param  [type] $uid [description]
     * @return [type]      [description]
     */
    PUBLIC STATIC function  getNewUserProduct($uid){
        $uid = intval($uid);
        if(!$uid) return false;

        $totalSql   = "SELECT COUNT(*) AS total FROM {{%user_product}} WHERE user_id = '$uid'";
        $total      = Yii::$app->db->createCommand($totalSql)->queryScalar();

        $sql        = "SELECT id,img FROM {{%user_product}} WHERE user_id = '$uid' ORDER BY id DESC";
        $info       = Yii::$app->db->createCommand($sql)->queryOne();

        if($info) $info['img'] = self::get_image_path($info['img']);

        $return = [
            'total' => $total ? $total : 0,
            'id'    => isset($info['id']) ? $info['id'] : 0,
            'img'   => $info['img'],
        ];
        return $return;
    }
    /**
     * [getProductTags 获取产品标签]
     * @param  [type] $id [产品ID]
     * @return [type]     [description]
     */
    PUBLIC STATIC function getProductTags($id,$idtype = 1){
        if(!$id) return false;

        $whereStr = " TI.idtype = '$idtype' AND TI.itemid = '$id'";

        $sql    = " SELECT TI.itemid,TG.tagname 
                    FROM {{%common_tagitem}} TI 
                    LEFT JOIN {{%common_tag}} TG ON TI.tagid = TG.tagid  
                    WHERE  $whereStr";

        $tagList= Yii::$app->db->createCommand($sql)->queryAll();

        return $tagList ? $tagList : [];
    }
    /**
     * [getTopicInfo 获取话题详情]
     * @param  [type] $id [description]
     * @return [type]         [description]
     */
    PUBLIC STATIC  function getTopicInfo($id){
        $id         = intval($id);
        //条件
        $whereStr   = " T.status = '1' AND T.id = '$id'";

        $sql        = "SELECT T.id,T.title,T.desc,T.picture,T.share_pic FROM {{%topic}} T WHERE $whereStr";

        $topicInfo  = Yii::$app->db->createCommand($sql)->queryOne();

        if(empty($topicInfo)){
            return false;
        }

        $topicInfo['picture']   = self::get_image_path($topicInfo['picture']);
        $topicInfo['share_pic'] = self::get_image_path($topicInfo['share_pic']);
        $topicInfo['linkUrl']   = Yii::$app->params['mfrontendUrl'].'app/topic-info?id='.$topicInfo['id'];

        return $topicInfo;
    }
    /**
     * [getPostAttachment 获取话题帖附件]
     * @param  [type]  $id   [ID]
     * @return [type]        [description]
     */
    PUBLIC STATIC function getPostAttachment($id){
        $id      = intval($id);
        if(!$id) return '';

        $sql    = "SELECT attachment FROM {{%attachment}} WHERE cid='$id' AND type = '2' ORDER BY aid";
        $list   = Yii::$app->db->createCommand($sql)->queryAll();

        if($list){
            foreach ($list as $key => $value) {
                $list[$key]  = Functions::get_image_path($value['attachment']);
            }
        }

        return $list;
    }
    /**
     * [postIslike 判断是否点赞话题帖]
     * @param  [type]  $userId [用户ID]
     * @param  [type]  $id     [评论ID]
     * @return [type]          [返回0或1]
     */
    PUBLIC STATIC function postIslike($userId,$id){

        $userId  = intval($userId);
        $id      = intval($id);

        if(!$userId || !$id) return false;

        $sql        = "SELECT id FROM {{%post_like}} WHERE  user_id =  '$userId'  AND post_id = '$id'";

        $isExsit    = Yii::$app->db->createCommand($sql)->queryScalar();
        $isLike     = $isExsit ? true : false;

        return $isLike;  
    }
    /**
     * [getTopicInfo 获取话题帖详情]
     * @param  [type] $id [description]
     * @return [type]         [description]
     */
    PUBLIC STATIC  function getPostInfo($id,$userId = 0){
        $id         = intval($id);
        //条件
        $whereStr   = " P.status = '1' AND P.id = '$id'";

        $sql        = "SELECT P.id,P.user_id,P.topic_id,P.content,T.title,P.like_num,P.comment_num,P.picture,P.ratio,P.created_at 
                       FROM {{%post}} P  LEFT JOIN {{%topic}} T ON P.topic_id = T.id
                       WHERE $whereStr";

        $postInfo  = Yii::$app->db->createCommand($sql)->queryOne();

        if(empty($postInfo)){
            return false;
        }

        $postInfo['user']   = [];
        $userInfo           = Functions::getUserInfo($postInfo['user_id']);
        $postInfo['user']['user_id']      = $userInfo['id'];
        $postInfo['user']['username']     = $userInfo['username'];
        $postInfo['user']['user_img']     = $userInfo['img'];
        $postInfo['user']['age']          = $userInfo['age'];
        $postInfo['user']['skin']         = $userInfo['skin'];
        $postInfo['picture']        = Functions::get_image_path($postInfo['picture']);
        $postInfo['attachment']     = Functions::getPostAttachment($postInfo['id']);
        $postInfo['isLike']         = Functions::postIsLike($userId,$postInfo['id']) ? 1 : 0;
        $postInfo['linkUrl']        = Yii::$app->params['mfrontendUrl'].'app/post-info?id='.$postInfo['id'];

        return $postInfo;
    }
    /**
     * [array_sort 二维数组排序]
     * @param  [type] $array [数组]
     * @param  [type] $row   [排序列]
     * @param  [type] $type  [规则]
     * @return [type]        [description]
     */
    PUBLIC STATIC  function array_sort($array,$key,$orderBy = SORT_ASC){
        $array_temp = [];
        foreach($array as $v){
            $array_temp[] = $v[$key];
        }
        array_multisort($array_temp,$orderBy,$array);
        return $array;
    }
}
