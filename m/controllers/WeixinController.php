<?php

namespace m\controllers;

use Yii;
use yii\web\Controller;
use common\models\User;
use common\models\LoginForm;
use common\models\ThirdLogin;
use common\components\Wechat;
use common\functions\Functions;
use common\functions\Tools;
use common\functions\NoticeFunctions;

class WeixinController extends BaseController
{
    /**
     * 微信登录记录
     *
     * @access  public
     * @param   array       $data       微信KEY等参数
     * @return  void
     */
    public function actionWxlogin($data){
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')) {
            $data           = json_decode($data,true);
            $appId          = Yii::$app->params['OfficialAccounts']['APPID'];
            $appSecret      = Yii::$app->params['OfficialAccounts']['APPKEY'];
            $option         = ['appid' => $appId, 'appsecret' => $appSecret];
            $wexin          = new Wechat($option);
            $openid         = $data['openid'];
            $accessToken    = $data['access_token'];
            $unionid        = $data['unionid'];
            $userData       = $this->getWxuserinfo($wexin,$openid,$accessToken);
            //数据问题
            $thirdInfo      = [];
            $thirdLogin     = ThirdLogin::find()
                ->where("type = 'weixin' AND unionid = :unionid", [':unionid' => $unionid])
                ->asArray()
                ->orderBy('id ASC')
                ->one();
            $userObj = User::find()
                ->where("id = :userId", [':userId' => $thirdLogin['user_id']])
                ->one();

            if (empty($thirdLogin)){
                $thirdObj           = new ThirdLogin();
                $thirdObj->type     = 'weixin';
                $thirdObj->openid   = $openid;
                $thirdObj->unionid  = $data['unionid'];
                $thirdObj->save();
                $userObj            = new User();
                $userObj->username  = Tools::userTextEncode($userData['nickname'],1);
                $userObj->img       = !empty($userData['headimgurl']) ? Functions::uploadUrlimg($userData['headimgurl']) : 'photo/member.png';
                $userObj->mobile    = 'weixin';
                $userObj->status    = '1';
                $userObj->referer   = 'H5';
                $userObj->setPassword($openid);
                $userObj->generateAuthKey();
                $userObj->save();

                $userObj->mobile = 'weixin' . $userObj->id;
                $userObj->save();

                $thirdObj->user_id = $userObj->id;
                $thirdObj->save();
                $uid        = $userObj->id;
                $img        = $userObj->img;
                $username   = $userObj->username; 
                $mobile     = $userObj->mobile;
                $password   = $openid;
                //注册送积分
                NoticeFunctions::notice($uid, NoticeFunctions::$SIGN_UP);
                //Functions::updateMoney($uid,200,'注册',2);
            }else{
                $userArr = User::find()
                        ->where("id = :userId", [':userId' => $thirdLogin['user_id']])
                        ->one();
                $uid        = $userArr['id'];
                $img        = Functions::get_image_path($userArr['img'],1);
                $username   = $userArr['username'];
                $mobile     = $userArr['mobile']; 
                $password   = $thirdLogin['openid'];
            }
            $model = new LoginForm();
            $login = [];

            $login['LoginForm']['mobile']   = $mobile;
            // $login['LoginForm']['password'] = $password;
            if ($model->load($login) && $model->login()) {
                
            }
            $userInfo = ['username' => $username,'uid' => $uid,'img'=> $img,'subscribe' => $userData['subscribe'] ];
            return $userInfo;
        }
    
    }
    /**
     * 微信用户信息
     *
     * @access  public
     * @param   array       $data       微信KEY等参数
     * @return  void
     */
    public function actionWeixinlogin($data){
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')) {
            $data           = json_decode($data,true);
            $appId          = Yii::$app->params['OfficialAccounts']['APPID'];
            $appSecret      = Yii::$app->params['OfficialAccounts']['APPKEY'];
            $option         = ['appid' => $appId, 'appsecret' => $appSecret];
            $wexin          = new Wechat($option);
            $openid         = $data['openid'];
            $accessToken    = $data['access_token'];
            $unionid        = $data['unionid'];
            $userData       = $this->getWxuserinfo($wexin,$openid,$accessToken);
            if($userData && $userData['unionid']){
                $thirdLogin   = ThirdLogin::find()
                    ->where("type = 'weixin' AND unionid = :unionid", [':unionid' => $unionid])
                    ->asArray()
                    ->orderBy('id ASC')
                    ->one();
                $userObj = User::find()
                    ->where("id = :userId", [':userId' => $thirdLogin['user_id']])
                    ->one();
                $uid        = $userObj ? $userObj['id'] : 0;
                $adminId    = $userObj ? $userObj['admin_id'] : 0;
                return  ['status' => '1','msg' => 
                            [
                                'uid'           => $uid,
                                'admin_id'      => $adminId,
                                'username'      => $userData['nickname'] ,
                                'img'           => $userData['headimgurl'],
                                'unionid'       => $userData['unionid'] 
                            ]
                        ]; 
           }else{
               return ['status' => '-1','msg' => '授权失败'];
           }
        }
    }
    /*
        获取用户信息
    */
    private function getWxuserinfo($wx_ob,$openid,$access_token)
    {
        $user_info = $wx_ob->getUserinfo($openid);
        if($user_info['subscribe'] == 0){
            $ret_info = $wx_ob->getOauthUserinfo($access_token,$openid);
            $ret_info['subscribe'] = 0;
            return $ret_info;
        }
        return $user_info;
    }
    /**
     * 其他微信公众号关注
     *
     * @access  public
     * @return  void
     */
    public function actionCoopPublic()
    {
        return $this->renderPartial('public.htm', ['GLOBALS' =>  $this->GLOBALS]);
    }
}