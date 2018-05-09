<?php
namespace m\controllers;

use yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\base\Object;
use yii\db\Command;
use common\components\Wechat;

/**
 * 公共加载的控制器
 */
class BaseController extends Controller
{
    public $GLOBALS;
    public $weixinObj;
    //初使化加载
    function  init(){
        $this->GLOBALS = Yii::$app->params;
        $this->GLOBALS['equipment']        = self::getEquipment();
        $this->GLOBALS['userInfo']         = [];
        
        if (!Yii::$app->user->isGuest) {
            $this->GLOBALS['userInfo'] = ['uid' => Yii::$app->user->identity->id,'username' =>  Yii::$app->user->identity->username];
        }
        $this->GLOBALS['csrfToken']    = Yii::$app->request->csrfToken;
        $this->GLOBALS['controller']   = $this->id;
        //统计相关
        $this->GLOBALS['statistics']   = ['id' => ''];
        //微信相关
        $this->GLOBALS['OfficialAccounts']['share'] = array(
            "title" => '这里有最全的护肤知识',
            "desc" => "海量的护肤知识和技巧，让你美容护肤更加得心应手",
            "imgUrl"  => $this->GLOBALS['static_path'].'h5/images/logo/147.png',
        );
        $appId      = Yii::$app->params['OfficialAccounts']['APPID'];
        $appSecret  = Yii::$app->params['OfficialAccounts']['APPKEY'];
        $option     = ['appid' => $appId, 'appsecret' => $appSecret];
        $this->weixinObj  = new Wechat($option);
        $this->GLOBALS['jsApi']            = [];
        $this->weixinShare();

    }
    /*
        获取当前设备
     */
    static public function getEquipment(){
        $equipment = 'PC';
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') || strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')){
            $equipment = 'IOS';
        }elseif(strpos($_SERVER['HTTP_USER_AGENT'], 'Android')){
            $equipment = 'Android';
        }
        return $equipment;
    }
    /*
        微信公共登录方法 
     */
    public function wxLogin(){
        //获取微信分享接口参数
        $url        = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $this->weixinShare();
        //判断是否登陆
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') && Yii::$app->user->isGuest) {
            // 此处为自动登录
            $data   =  $this->weixinObj->getOauthAccessToken();
            if (empty($data['unionid'])) {
                return $this->weixin($url);
            }
            $this->GLOBALS['userInfo']   =    Yii::$app->runAction('/weixin/wxlogin',['data'=> json_encode($data)]);
        }
       
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
    //微信分享-不登录下
    public function weixinShare()
    {
        //获取微信分享接口参数
        $url        = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $type       = 'share';
        $this->GLOBALS['jsApi']      =    $this->weixinObj->getToken($url,$type);
    }
}