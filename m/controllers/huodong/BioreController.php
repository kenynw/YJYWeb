<?php
namespace m\controllers\huodong;

use Yii;
use yii\web\Controller;
use common\models\ThirdLogin;
use common\models\User;
use common\components\Wechat;
use yii\web\NotFoundHttpException;
use common\functions\Functions;
use common\functions\Tools;
use common\functions\Huodong;
use common\functions\NoticeFunctions;
use yii\web\Session;

class BioreController extends Controller
{
    public  $enableCsrfValidation   =   false;
    public  $layout                 =   "app";
    public  $static_path            =   '';
    private $huoDongdId             =   '3';                        //活动ID
    private $huoDongdInfo           =   [];                         //活动信息
    private $userInfo               =   null;                       //用户信息
    private $draw_turnOn            =   '1';                        //开启为1，关闭为0
    private $receiveNum             =   '1';                        //需要的邀请数
    private $assistsNum             =   '2';                        //能助攻次数
    private $fictitious             =   '999';                      //双倍邀请人数
    private $statisticalCode        =   '';

    private $startTime              =   '';                         //活动时间
    private $endTime                =   '';                         //截止时间
    STATIC  $errorNo                =   [
        '1'     =>  '操作成功',
        '2'     =>  '已领取',
        '-1'    =>  '请求错误',
        '-2'    =>  'TOKEN已过期',
        '-3'    =>  '已邀请',
        '-4'    =>  '奖品已领完',
        '-5'    =>  '活动还未开始',
        '-6'    =>  '活动已结束',
        '-7'    =>  '活动已关闭',
        '-8'    =>  '未达到完成要求',
        '-9'    =>  '参数 %s 格式有误',
        '-10'   =>  '缺少参数 %s',
        '-11'   =>  '参数 %s 为空或超出200限制',
        '-12'   =>  '邀请卡未找到',
        '-13'   =>  '您还未登录',
        '-14'   =>  '只能为 %s 个好友助攻哦~',
        '-15'   =>  '不能为自己助攻哦~',
        '-16'   =>  '您已领取奖品',
        '-17'   =>  '满足条件未领取',
        '-18'   =>  '助攻已完成',
        '-19'   =>  '授权发生错误'
    ];

    //初使化加载
    function  init(){
        $this->huoDongdInfo = $this->getHuodongInfo();
        $this->startTime    = $this->huoDongdInfo['starttime'];
        $this->endTime      = $this->huoDongdInfo['endtime'];
        //$this->draw_turnOn  = $this->huoDongdInfo['status'];
        $this->receiveNum   = $this->huoDongdInfo['re_number'];
        $this->fictitious   = $this->huoDongdInfo['fictitious'] ? $this->huoDongdInfo['fictitious'] - 1: $this->fictitious ;

        $userInfo           = Yii::$app->Session->get('userInfo');
        if ($userInfo) {
            $this->userInfo     = $userInfo;
            $this->assistsNum   = $userInfo['admin_id'] > 0 ? '9999' : $this->assistsNum;
        }
        //静态地址
        $this->static_path      =  Yii::$app->params['static_path'].'h5/huodong/biore/';
        $this->statisticalCode  =  Yii::$app->params['statisticalCode'];
    }
    //微信授权
    public function weixin($back_url = '')
    {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')) {
            $appId = Yii::$app->params['OfficialAccounts']['APPID'];
            $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$appId.'&redirect_uri='.urlencode($back_url).'&response_type=code&scope=snsapi_userinfo&state=getbaseinfo#wechat_redirect';
            $this->redirect($url);
        }
    }
    /**************************************************************************************************************/
    /************************************************* 页面展示   *************************************************/
    /**************************************************************************************************************/
    public function actionIndex(){
        $playedNum  = $this->getClickNum();
        //已领次数
        $drawNum    =  intval($this->getDrawNum()) * 2;
        $path       =  Yii::$app->params['mfrontendUrl'];

        return $this->renderPartial('index.htm', [
            'path'          => $path,
            'drawNum'       => $drawNum,
            'playedNum'     => $playedNum,
            'huoDongdInfo'  => $this->huoDongdInfo,
            'userInfo'      => $this->userInfo,
            'static_path'   => $this->static_path,
            'statisticalCode'=> $this->statisticalCode,
        ]);
    }
    /**************************************************************************************************************/
    /************************************************* 收货地址入库   *************************************************/
    /**************************************************************************************************************/
    public function actionAddAddress(){
        //获取参数
        // $token      = $_REQUEST['token'];
        $username   = $_REQUEST['username'];
        $mobile     = $_REQUEST['mobile'];
        $address    = $_REQUEST['address'];
        $callback   = $_REQUEST['callback'];

        // $token      = Functions::checkStr($token);
        $username   = Functions::checkStr($username);
        $mobile     = Functions::checkStr($mobile);
        $address    = Functions::checkStr($address);
        $callback   = Functions::checkStr($callback);

        $huoDongdId = $this->huoDongdId;

        $params     = ['username' => '用户名','mobile' => '手机','address' => '地址']; 
        $paramsArr  = $_REQUEST;

        $userId     = Yii::$app->Session->get('user_id');
        //检验参数
        foreach ($params as $key => $value) {
            if(!isset($paramsArr[$key])){
                $return = ['status' => '-10' ,'msg' => sprintf(self::$errorNo['-10'], $key)];
                echo $callback.'('.json_encode($return).')';die; 
            }
            $length     =  mb_strlen($key);
            if($length >= 200 || $length == 0){
                $return = ['status' => '-11' ,'msg' => sprintf(self::$errorNo['-11'], $key)];
                echo $callback.'('.json_encode($return).')';die;
            }
            unset($length);
        }
        //是否已登录
        if(!$userId) { 
            $return = ['status' => '-2' ,'msg' => self::$errorNo['-2']];
            echo $callback.'('.json_encode($return).')';die;
        }
        $isMoblie   = Functions::isMoblie($mobile);
        if(!$isMoblie) { 
            $return = ['status' => '-9' ,'msg' => sprintf(self::$errorNo['-3'], '手机号')];
            echo $callback.'('.json_encode($return).')';die;
        }

        //是否已完成
        $isComplete = $this->isComplete($userId);
        if(!$isComplete){
            $return = ['status' => '-8' ,'msg' => self::$errorNo['-8']];
            echo $callback.'('.json_encode($return).')';die;
        }
        //开始入库 
        $data = array(
            'tel'       =>  $mobile,
            'user_id'   =>  $userId,
            'name'      =>  $username,
            'hid'       =>  $this->huoDongdId,
            'address'   =>  $address
        );
        $isSuccess  = Yii::$app->db->createCommand()->insert('{{%huodong_address}}', $data)->execute();
        if($isSuccess == true){
            // 添加成功通知
            NoticeFunctions::notice($userId, NoticeFunctions::$HUODONG,$this->huoDongdId);
            $return     = array(
                'status'   => '1', 
                'msg'      => self::$errorNo['1']
            );
        }else{
            $return = array('success' => '-1', 'msg' => self::$errorNo['-1']);
        }
        echo $callback.'('.json_encode($return).')';
    }
    /**************************************************************************************************************/
    /*************************************************    助攻页面     ************** *****************************/
    /**************************************************************************************************************/
    public function actionAssists(){
        //获取微信分享接口参数
        $appId      = Yii::$app->params['OfficialAccounts']['APPID'];
        $appSecret  = Yii::$app->params['OfficialAccounts']['APPKEY'];
        $option     = ['appid' => $appId, 'appsecret' => $appSecret];
        $weixin     = new Wechat($option);
        $id         = Yii::$app->request->get('id');
        $id         = intval($id);
        $url        = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $type       = 'share';
        $jsApi      =  $weixin->getToken($url,$type);
        //活动状态
        $isOver     =   '';
        $huoDongdId =   $this->huoDongdId;
        $startTime  =   $this->startTime;
        $endTime    =   $this->endTime;
        $now        =   time();
        $endHuoDong =   0;

        if($now < $startTime) {
           $isOver = self::$errorNo['-5'];
        }
        if($now >= $endTime) {
           $isOver = self::$errorNo['-6'];
           $endHuoDong = 1;
        } 
        if ($this->draw_turnOn != 1) {
            $isOver = self::$errorNo['-7'];
        }
        //判断是否登陆
        if (!$this->userInfo) {
            // 此处为自动登录
            $data   =  $weixin->getOauthAccessToken();
            if (empty($data['unionid'])) {
                return $this->weixin($url);
            }
            $userData   =    Yii::$app->runAction('/weixin/weixinlogin',['data'=> json_encode($data)]);
            if($userData['status'] == '1'){
                $this->userInfo = $userData['msg'];
                Yii::$app->Session->set('userInfo',$this->userInfo);
            }else{
                throw new NotFoundHttpException(self::$errorNo['-19']);
            }
        }
        if(!$id){
            throw new NotFoundHttpException(self::$errorNo['-12']);
        }
        $invitationInfo = $this->getInvitation($id);

        if(!$invitationInfo){
            throw new NotFoundHttpException(self::$errorNo['-12']);
        }
        //是否已助攻
        $isCheck        = $this->userIsAssists($id);
        $isCheckAssists = true;
        if(!$isCheck){
            $isCheckAssists = false;
        }
        //已助攻次数
        $drawNum    = $this->getAssistsNum($id);
        $receiveNum = $this->getreceiveNum($id);

        switch ($receiveNum) {
            case '100':
                if($drawNum >= 51){
                    $ratio = 60 + floor(($drawNum- 50) * 0.8);
                }elseif($drawNum >= 21){
                    $ratio = 30 + ($drawNum- 20) * 1;
                }else{
                    $ratio = floor($drawNum * 1.5);
                }
                break;
            default:
                if($drawNum >= 26){
                    $ratio = 60 + floor(($drawNum- 25) * 1.6); 
                }elseif($drawNum >= 11){
                    $ratio = 30 + ($drawNum- 10) * 2;
                }else{
                    $ratio = $drawNum * 3;
                }
            break;
        }


        $invitationList = $this->getInvitationAll($id);
        $downloadUrl    = Yii::$app->urlManager->createUrl(['site/button-click','id'=>'5','hid'=> $huoDongdId,'type'=>'3']);
        $downloadUrl2   = Yii::$app->urlManager->createUrl(['site/download-guide','id'=>'1','hid'=>$huoDongdId,'type'=>'3']);
        return $this->renderPartial('assists.htm', [
            'url'               => $url,
            'downloadUrl'       => $downloadUrl,
            'downloadUrl2'      => $downloadUrl2,
            'appId'             => $appId,
            'invitationInfo'    => $invitationInfo,
            'isCheckAssists'    => $isCheckAssists,
            'id'                => $id,
            'ratio'             => $ratio,
            'invitationList'    => $invitationList,
            'jsApi'             => $jsApi,
            'isOver'            => $isOver,
            'endHuoDong'        => $endHuoDong,
            'huoDongdInfo'      => $this->huoDongdInfo,
            'userInfo'          => $this->userInfo,
            'static_path'       => $this->static_path,
            'statisticalCode'   => $this->statisticalCode,
        ]);
    }
    /**************************************************************************************************************/
    /*************************************************  判断当前状态   *******************************************/
    /**************************************************************************************************************/
    public function actionIsReceive(){
        $token      =   $_REQUEST['token'];
        $token      =   Functions::checkStr($token);
        $userId     =   Functions::checkToken($token);
        $callback   =   Yii::$app->request->get('callback');

        // $equipment  =   Yii::$app->request->get('equipment');
        // $equipment  =   Functions::checkStr($equipment);

        $huoDongdId = $this->huoDongdId;

        $check      =   $this->news_check();
        if($check && is_array($check)){ 
            echo $callback.'('.json_encode($check).')';die;
        }
        //是否已登录
        if(!$userId) { 
            $return = ['status' => '-2' ,'msg' => self::$errorNo['-2']];
            echo $callback.'('.json_encode($return).')';die;
        }
        Yii::$app->Session->set('user_id',$userId);
        //是否已领取
        $sql          = "SELECT id FROM {{%huodong_special_draw}}  
                         WHERE  hdid = '$huoDongdId' AND giftid = '1' AND uid = '$userId'";
        $isReceive    = Yii::$app->db->createCommand($sql)->queryScalar();
        //是否已填地址
        $addressSql   = "SELECT id FROM {{%huodong_address}}  
                         WHERE  hid = '$huoDongdId' AND  user_id = '$userId'";
        $isAddress    = Yii::$app->db->createCommand($addressSql)->queryScalar();

        if($isReceive && $isAddress) {
            $return  = ['status' => '-16' ,'msg' => self::$errorNo['-16']];
            echo $callback.'('.json_encode($return).')';die;
        }elseif($isReceive && !$isAddress){
            $return = ['status' => '-17' ,'msg' => self::$errorNo['-17']]; 
            echo $callback.'('.json_encode($return).')';die;
        }
        //是否已领完
        $drawNum    = $this->getDrawNum();
        $isReceive  = $this->huoDongdInfo['prize_num'] - $drawNum > 0 ? true : false;
        if(!$isReceive){
            $return = ['status' => '-4' ,'msg' => self::$errorNo['-4']];
            echo $callback.'('.json_encode($return).')';die;
        }
        $username   = $this->getUserInfo($userId);
        $return     = ['status' => '1' ,'msg' => self::$errorNo['1']];
        $username ? $return['username'] = $username : '';
        echo $callback.'('.json_encode($return).')';
    }
    /**************************************************************************************************************/
    /************************************************* 验证TOKEN是否有效   *******************************************/
    /**************************************************************************************************************/
    public function actionCheckToken(){
        $token      = $_REQUEST['token'];
        $token      = Functions::checkStr($token);
        $userId     = Functions::checkToken($token);
        $callback   = $_REQUEST['callback'];

        $return     = ['status' => '-2' ,'msg' => $token];
        if($userId) { 
            $return = ['status' => '1' ,'msg' => self::$errorNo['1']];
        }
        echo $callback.'('.json_encode($return).')';
    }
    /***************************************************************************************************************/
    /************************************************* 入库领取信息  ***********************************************/
    /***************************************************************************************************************/
    public function actionGetdraw(){
        $check      =   $this->news_check();
        // $token      =   $_REQUEST['token'];
        // $token      =   Functions::checkStr($token);
        $callback   =   Yii::$app->request->get('callback');

        $huoDongdId =   $this->huoDongdId;
        $userId     =   Yii::$app->Session->get('user_id');
        // $userId     =   Functions::checkToken($token);
        if($check && is_array($check)){ 
            echo $callback.'('.json_encode($check).')';die;
        }
        //是否已登录
        if(!$userId) { 
            $return  = ['status' => '-2' ,'msg' => self::$errorNo['-2']];
            echo $callback.'('.json_encode($return).')';die;
        }
        $userSql     = "SELECT U.id,U.username,U.img  FROM {{%user}} U WHERE U.id = '$userId'";
        $this->userInfo   = Yii::$app->db->createCommand($userSql)->queryOne();
        //是否已邀请
        $sql         = "SELECT id,giftid FROM {{%huodong_special_draw}}  WHERE  hdid = '$huoDongdId' AND uid = '$userId'";
        $drawInfo    = Yii::$app->db->createCommand($sql)->queryOne();

        if(!empty($drawInfo)){
            $drawID         = $drawInfo['id'];
            // $nowAssistsNum  = $this->getAssistsNum($drawID);
            // $receiveNum     = $this->getreceiveNum($drawInfo['id']);
            // if($nowAssistsNum >= $receiveNum  && $drawInfo['giftid'] > 0){
            if($drawInfo['giftid'] > 0){
                //是否已填地址
                $addressSql   = "SELECT id FROM {{%huodong_address}}  
                                 WHERE  hid = '$huoDongdId' AND  user_id = '$userId'";
                $isAddress    = Yii::$app->db->createCommand($addressSql)->queryScalar();
                if($isAddress){
                    $return = ['status' => '2' ,'msg' => self::$errorNo['2']];
                }else{
                    $playedNum  = $this->getClickNum();
                    $return = ['status' => '-17' ,'msg' => self::$errorNo['-17'],'playedNum' => $playedNum]; 
                }
            }else{
                $return = ['status' => '-8' ,'msg' => self::$errorNo['-8'],'id' => $drawID];
            }
            echo $callback.'('.json_encode($return).')';die;
        }
        //是否已领完
        $drawNum    = $this->getDrawNum();
        $isReceive  = $this->huoDongdInfo['prize_num'] - $drawNum > 0 ? true : false;
        if(!$isReceive){
            $return = ['status' => '-4' ,'msg' => self::$errorNo['-4']];
            echo $callback.'('.json_encode($return).')';die;
        }
        //开始入库 
        $data = array(
            'giftid'    =>  '0',
            'giftname'  =>  $this->huoDongdInfo['prize'],
            'uid'       =>  $this->userInfo['id'],
            'username'  =>  $this->userInfo['username'],
            'addtime'   =>  time(),
            'hdid'      =>  $this->huoDongdId,
            'ip'        =>  $_SERVER["REMOTE_ADDR"],
            //'ext'       =>  $equipment
        );
        $isSuccess  = Yii::$app->db->createCommand()->insert('{{%huodong_special_draw}}', $data)->execute();
        if($isSuccess == true){
            $receiveId  = Yii::$app->db->getLastInsertId();
            $return     = array(
                'status'   => '1', 
                'id'       => $receiveId
            );
        }else{
            $return = array('success' => '-1', 'msg' => self::$errorNo['-1']);
        }
        echo $callback.'('.json_encode($return).')';
    }
    /***************************************************************************************************************/
    /************************************************* 统计日志写入  ***********************************************/
    /***************************************************************************************************************/
    public function actionWriteLog(){
        // $token      =   isset($_REQUEST['token']) ? Functions::checkStr($_REQUEST['token']) : '';
        // $userId     =   $token ? Functions::checkToken($token) : '';
        $userId     =  Yii::$app->Session->get('user_id');
        $type       =  Yii::$app->request->get('type');
        $callback   =  Yii::$app->request->get('callback');
        $typeArr    =  ['click_num'];
        $userId     =  intval($userId);
        // if($this->userInfo['uid']){
        //     $userId = $this->userInfo['uid'];
        // }

        if(in_array($type,$typeArr)){
            $file       = 'static/huodong/'.$this->huoDongdId.'/'.$type.'.txt';
            $dirname    =  dirname($file);
            if (!file_exists($dirname) && !mkdir($dirname, 0777, true)) {
              return false;
            }
            if($userId){
                //文件锁
                $fp         = fopen($file, 'a+');
                if(!is_writable($file)){  
                    return false; 
                }
                $nowTime    = date('Y-m-d');
                $str        = $userId.'|'.$nowTime.'#';  
                flock($fp, LOCK_EX);// 加锁  
                fwrite($fp, $str);  
                flock($fp, LOCK_UN);// 解锁
                fclose($fp);
            }
        }
        $return = ['status' => 1 ,'msg' => self::$errorNo['1']];
        echo $callback.'('.json_encode($return).')';
    }
    /***************************************************************************************************************/
    /*************************************************      助攻提交方法      *************************************/
    /***************************************************************************************************************/
    public function actionAddAssists(){
        //获取接口参数
        $id         = Yii::$app->request->get('id');
        $id         = intval($id);
        $callback   = Yii::$app->request->get('callback');
        $time       = time();
        if(!$id){
            $return = ['status' => '-12' ,'msg' => self::$errorNo['-12']];
            echo $callback.'('.json_encode($return).')';die;
        }
        $invitationInfo = $this->getInvitation($id);
        if(!$invitationInfo){
            $return = ['status' => '-12' ,'msg' => self::$errorNo['-12']];
            echo $callback.'('.json_encode($return).')';die;
        }
        $check    =   $this->news_check();

        if($check && is_array($check)){ 
            echo $callback.'('.json_encode($check).')';die;
        }
        //判断是否登陆
        if (!$this->userInfo) {
            $return = ['status' => '-13' ,'msg' => self::$errorNo['-13']];
            echo $callback.'('.json_encode($return).')';die;
        }
        $unionid  =   $this->userInfo['unionid'];
        //是否是本人
        if($invitationInfo['unionid'] == $unionid){
            $return = ['status' => '-15' ,'msg' => self::$errorNo['-15']];
            echo $callback.'('.json_encode($return).')';die;
        }
        //助攻次数是否已满
        $isCheck        = $this->isCheckAssists();
        $userIsAssists  = $this->userIsAssists($id);
        if(!$isCheck || !$userIsAssists){
            $return = ['status' => '-14' ,'msg' => sprintf(self::$errorNo['-14'], $this->assistsNum)];
            echo $callback.'('.json_encode($return).')';die;
        }
        $nowAssistsNum  = $this->getAssistsNum($id);
        $receiveNum = $this->getreceiveNum($id);

        if($nowAssistsNum >= $receiveNum){
            $return = ['status' => '-18' ,'msg' => self::$errorNo['-18']];
            echo $callback.'('.json_encode($return).')';die;
        }
        //开始入库 
        $data = array(
            'hid'           =>  $this->huoDongdId,
            'unionid'       =>  $this->userInfo['unionid'],
            'nickname'      =>  Tools::userTextEncode($this->userInfo['username'],1),
            'headimgurl'    =>  $this->userInfo['img'],
            'relation_id'   =>  $id
        );
        $isSuccess      = Yii::$app->db->createCommand()->insert('{{%huodong_draw_log}}', $data)->execute();
        //如果完成，修改完成时间
        if($nowAssistsNum + 1 >= $receiveNum){
            $updateSql    = "UPDATE  {{%huodong_special_draw}} SET endtime = '$time',giftid = '1' WHERE id ='$id'";
            Yii::$app->db->createCommand($updateSql)->execute();
        }
        $return = ['status' => '1' ,'msg' => self::$errorNo['1'],'num' => $nowAssistsNum];
        echo $callback.'('.json_encode($return).')';
    }
    /***************************************************************************************************************/
    /*************************************************    其他方法函数    ******************************************/
    /***************************************************************************************************************/
    /**
     * [news_check 基本验证]
     * @return [type] [description]
     */
    private function news_check() {
        $huoDongdId =   $this->huoDongdId;
        $startTime  =   $this->startTime;
        $endTime    =   $this->endTime;
        $now        =   time();

        if($now < $startTime) {
           return  array('status' => '-5', 'msg' => self::$errorNo['-5']);
        }
        if($now >= $endTime) {
           return  array('status' => '-6', 'msg' => self::$errorNo['-6']);
        } 
        if ($this->draw_turnOn != 1) {
            return $return = array('status' => '-7', 'msg' => self::$errorNo['-7']);
        }
        return true;
    }
    /**
     * [getHuodongInfo 获取活动信息]
     * @return [type] [description]
     */
    private function getHuodongInfo() {
        $huoDongdId     = $this->huoDongdId;
        $sql            = "SELECT * FROM {{%huodong_special_config}}  WHERE  id = '$huoDongdId'";
        $huoDongdInfo   = Yii::$app->db->createCommand($sql)->queryOne();

        if(!$huoDongdInfo){
            throw new NotFoundHttpException('The huodong not exist.');
        }
        return $huoDongdInfo;
    }
    /**
     * [getClickNum 获取参与数]
     * @return [type] [description]
     */
    private function getClickNum() {
        $total      =  500;
        $userArr    =  [];
        $file       =  'static/huodong/'.$this->huoDongdId.'/'.'click_num.txt';
        $newsStr    =  @file_get_contents($file);
        if (!$newsStr) {
            return $total;
        }
        $clickArr = explode('#',$newsStr);
        foreach ($clickArr as $key => $value) {
            $uid    = current(explode('|',$value));
            $uid ? $userArr[] = $uid : '';
            unset($uid);
        }
        return COUNT(array_unique($userArr)) + $total;
    }
    /**
     * [getDrawNum 获取用户数]
     * @return [type] [description]
     */
    private function getDrawNum(){
        $huoDongdId   = $this->huoDongdId;
        $sql          = "SELECT COUNT(*) FROM {{%huodong_special_draw}}  WHERE  hdid = '$huoDongdId' AND giftid = '1'";
        $drawNum      = Yii::$app->db->createCommand($sql)->queryScalar();
        return $drawNum;
    }
    /**
     * [getAssistsNum 获取已助攻次数]
     * @return [type] [description]
     */
    private function getAssistsNum($receiveId){
        $huoDongdId   = $this->huoDongdId;
        if(!$receiveId) return false;
        $sql          = "SELECT COUNT(*) FROM {{%huodong_draw_log}}  WHERE  hid = '$huoDongdId' AND relation_id = '$receiveId'";
        $receiveNum   = Yii::$app->db->createCommand($sql)->queryScalar();
        return $receiveNum;
    }
    /**
     * [isComplete 是否已完成任务]
     * @return [type] [description]
     */
    private function isComplete($userId){
        $huoDongdId   = $this->huoDongdId;
        if(!$userId) return false;
        $sql          = "SELECT id FROM {{%huodong_special_draw}}  WHERE  hdid = '$huoDongdId' AND uid = '$userId' AND giftid = '1'";
        $receiveId    = Yii::$app->db->createCommand($sql)->queryScalar();
        return $receiveId ? true : false;
    }
    /**
     * [getreceiveNum 获取需要助攻数]
     * @param  [type] $id [邀请ID]
     * @return [type]     [description]
     */
    private function  getreceiveNum($id){
        if(!$id) return false;
        $huoDongdId   = $this->huoDongdId;
        $fictitious   = $this->fictitious;
        $sql          = "SELECT addtime FROM {{%huodong_special_draw}}  WHERE  hdid = '$huoDongdId' ORDER BY id  LIMIT  $fictitious , 1";
        $addtime      = Yii::$app->db->createCommand($sql)->queryScalar();
        $selectSql    = "SELECT addtime FROM {{%huodong_special_draw}}  WHERE  id = '$id'";
        $drawTime     = Yii::$app->db->createCommand($selectSql)->queryScalar();

        return $addtime && ($drawTime > $addtime) ? $this->receiveNum * 2 : $this->receiveNum;
    }
    /**
     * [getInvitation 邀请信息]
     * @return [type] [description]
     */
    private function getInvitation($id){
        if(!$id) return false;
        $sql          = "SELECT D.uid,D.username,D.giftid,T.unionid FROM {{%huodong_special_draw}} D 
                        LEFT JOIN {{%third_login}} T  ON D.uid = T.user_id
                        WHERE  D.id = '$id'";
        $invitation   = Yii::$app->db->createCommand($sql)->queryOne();
        return $invitation;
    }
    /**
     * [getInvitationAll 助力列表]
     * @return [type] [description]
     */
    private function getInvitationAll($id){
        if(!$id) return false;
        $sql    = "SELECT * FROM {{%huodong_draw_log}} L WHERE  L.relation_id = '$id'";
        $list   = Yii::$app->db->createCommand($sql)->queryAll();

        foreach ($list as $key => $value) {
            $list[$key]['img']      = $value['headimgurl'] ? substr($value['headimgurl'],0,-1).'46' : '';
            $username               = Tools::userTextDecode($value['nickname']);
            $list[$key]['username'] = strlen($username) >= 18 ? mb_substr($username,0,6,'utf-8') .'...' : $username;
            unset($username);
        }
        return $list;
    }
    /**
     * [isCheckAssists 助攻次数限制]
     * @return [type] [description]
     */
    private function isCheckAssists(){
        $assistsNum     = $this->assistsNum;
        $unionid        = $this->userInfo['unionid'];

        if(!$unionid) return false;

        $adminId = $this->userInfo['admin_id'];
        if($adminId == '1') return true;
        
        $sql    = "SELECT COUNT(*) FROM {{%huodong_draw_log}}  WHERE  unionid = '$unionid'";
        $num    = Yii::$app->db->createCommand($sql)->queryScalar();

        return $num >= $assistsNum ? false :true ;
    }
    /**
     * [isCheckAssists 是否已助攻]
     * @return [type] [description]
     */
    private function userIsAssists($id){
        $unionid = $this->userInfo['unionid'];
        
        if(!$id || !$unionid) return false;

        $adminId = $this->userInfo['admin_id'];
        if($adminId == '1') return true;

        $sql        = "SELECT id,unionid,relation_id FROM {{%huodong_draw_log}}  WHERE  unionid = '$unionid' AND relation_id = '$id'";
        $isTrue     = Yii::$app->db->createCommand($sql)->queryOne();

        return $isTrue ? false : true;
    }
    /**
     * [getUserInfo 获取用户信息]
     * @param  [type] $uid [用户ID]
     * @return [type]      [description]
     */
    private function getUserInfo($userId){
        if(!$userId) return false;
        $userId     = intval($userId);

        $userSql    = "SELECT U.id,U.username,L.unionid
                FROM {{%user}} U 
                LEFT JOIN {{%third_login}} L  ON U.id = L.user_id
                WHERE U.id = '$userId'";

        $userInfo   = Yii::$app->db->createCommand($userSql)->queryOne();
        if(!$userInfo) return false;

        $username =  $userInfo['unionid'] ? Tools::userTextDecode($userInfo['username']) : false;
        return $username;
    }
}