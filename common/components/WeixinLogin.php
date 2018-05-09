<?php

namespace common\components;

/*
	第三方登录类_微信登录
*/
	
class WeixinLogin{
	
     /**
     * 获取QQconnect Login 跳转到的地址值
     * @return array 返回包含code state
     * 
    **/ 
    public static function login($app_id, $callback){
        $_SESSION['state'] = md5(uniqid(rand(), TRUE)); //CSRF protection
        $login_url = "https://open.weixin.qq.com/connect/qrconnect?appid=" 
            .$app_id. "&response_type=code&scope=snsapi_login&redirect_uri=" . urlencode($callback)
            . "&state=1#wechat_redirect";
        // var_dump($login_url);
        //显示出登录地址
         // header('Location:'.$login_url);
        return $login_url;
    }
    /**
     * 获取access_token值
     * @return array 返回包含access_token,过期时间的数组
     * */
    private function get_token($app_id,$app_key,$code,$callback,$state){

        $url = "https://api.weixin.qq.com/sns/oauth2/access_token";
        $param = array(
            "appid"     =>    $app_id,
            "secret" =>    $app_key,
			"grant_type"    =>    "authorization_code",
			"redirect_uri"  =>    $callback,
            "code"          =>    $code
        );
        $response = json_decode($this->get_url($url, $param));
        if($response == false) {
            return false;
        }
        return $response;
    }
    
    
    /***
     * 检验授权凭证（access_token）是否有效
     */
    public function check_token($access_token,$openid){
        
        $url="https://api.weixin.qq.com/sns/auth?access_token=$access_token&openid=$openid";
        
        $ret = $this->get_url($url);
        
        if(empty($ret) || $ret=false){
            return false;
        }else{
            $arr = json_decode($str,true);
            if($arr['errcode']==0 && $arr['errmsg']=="ok"){
                return true;
            }else{
                return false;
            }
        }
        
    }
     
    /**
     * 获取用户信息
     * @param $client_id
     * @param $access_token
     * @param $openid
     * @return array 用户的信息数组
     * */
    public function get_user_info($app_id,$token,$openid){
        $url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$token.'&openid='.$openid;
        $str = $this->get_url($url);
        if($str == false) {
        return false;
        }
        $arr = json_decode($str,true);
        return $arr;
    }
 
     /**
     * 请求URL地址，返回callback得到返回字符串
     * @param $url qq提供的api接口地址
     * */
    
    public function callback($app_id, $app_key, $callback) {
        $code = $_GET['code'];
        $state = $_GET['state'];
        $token = $this->get_token($app_id,$app_key,$code,$callback,$state);
    	$userinfo = $this->get_user_info($app_id,$token->access_token,$token->openid);
        if(!$token) {
            return false;
            exit();
        }
        return array('openid' => $token->openid, 'token' => $token->access_token, 'nickname' => $userinfo['nickname'],'img' => $userinfo['headimgurl']);
    }
    
    
    /*
     * HTTP GET Request
    */
    private  function get_url($url, $param = null) {
        if($param != null) {
            $query = http_build_query($param);
            $url = $url . '?' . $query;
        }
        $ch = curl_init();
        if(stripos($url, "https://") !== false){
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
        $content = curl_exec($ch);
        $status = curl_getinfo($ch);
        curl_close($ch);
        if(intval($status["http_code"]) == 200) {
            return $content;
        }else{
        echo $status["http_code"];
            return false;
        }
    }
    
    /*
     * HTTP POST Request
    */
    private  function post_url($url, $params) {
        $ch = curl_init();
        if(stripos($url, "https://") !== false) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
    
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        $content = curl_exec($ch);
        $status = curl_getinfo($ch);
        curl_close($ch);
        if(intval($status["http_code"]) == 200) {
            return $content;
        } else {
            return false;
        }
    }
}
?>