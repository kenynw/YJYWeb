<?php
namespace api\controllers;

use yii;
use yii\web\Controller;
use common\functions\Functions;

class OauthController extends Controller
{
    private $webId          = '831999210';          //网站ID
    private $unionid        = '1000240804';         //联盟ID
    private $token          = 'd68a8068-e42b-44a9-a5ce-169038124a6a';  //token--注：有效期为一年。过期重新获取
    private $redirect_uri   = "http://api.yjyapp.com/oauth/jd";
    private $client_id      = "91A7479D8DA280BD400362278F912B28";
    private $client_secret  = "281ec09000d841638f3bcb230ecf863f";
    private $codeurl        = 'https://oauth.jd.com/oauth/authorize';
    private $tokenurl       = "https://oauth.jd.com/oauth/token?";
    private $baseurl        = "https://api.jd.com/routerjson?";

    public function actionJd(){
        $code           =   Yii::$app->request->get('code','');
        $response_type  =   "code";
        $grant_type     =   "authorization_code";
        $client_id      =   $this->client_id;
        $client_secret  =   $this->client_secret;
        $redirect_uri   =   $this->redirect_uri;
        $state          =   "jdunion";
        $codeurl        =   $this->codeurl;
        $tokenurl       =   $this->tokenurl;

        if ($code != "")
        {
            $fields = [
                "grant_type" => urlencode($grant_type),
                "client_id" => urlencode($client_id),
                "redirect_uri" => urlencode($redirect_uri),
                "code" => urlencode($code),
                "state" => urlencode($state),
                "client_secret" => urlencode($client_secret)
            ];

            $fields_string = "";
            foreach($fields as $key=>$value) {
                $fields_string .= $key.'='.$value.'&';
            }
            rtrim($fields_string, '&');

            //发送get请求
            // $ch = curl_init();
            // curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept:text/plain;charset=utf-8', 'Content-Type:application/x-www-form-urlencoded','charset=utf-8'));
            // curl_setopt($ch, CURLOPT_URL,$tokenurl.$fields_string);
            // // curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            // curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            // curl_setopt($ch, CURLOPT_POST, 1);
            // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            // $output = curl_exec($ch);
            // curl_close($ch);
            var_dump($tokenurl.$fields_string);
        }
        else
        {
          header("Location: ".$codeurl."?response_type=".$response_type."&client_id=".$client_id."&redirect_uri=".$redirect_uri."&state=".$state);
        }
    }
    public function actionGetJdUrl(){
        $sourceurl    =   Yii::$app->request->get('u','');

        if($sourceurl == ""){
            return false;
        }

        $method     = "jingdong.service.promotion.getcode";
        $channel    = "PC";
        $type       = 7;
        $unionId    = $this->unionid;
        $webId      = $this->webId;
        $token      = $this->token;
        $appkey     = $this->client_id;
        $appSecret  = $this->client_secret;
        $v          = "2.0";
        $time       = date('Y-m-d H:i:s',time());
        $baseurl    = $this->baseurl;

        //应用参数，json格式
        $_360buy_param_json ='{"channel":"'.$channel.'","materialId":"'.$sourceurl.'","promotionType":'.$type.',"unionId":"'.$unionId.'",
          "webId":"'.$webId.'"}';

        //系统参数
        $fields = [
            "360buy_param_json" => urlencode($_360buy_param_json),
            "access_token"      => urlencode($token),
            "app_key"           => urlencode($appkey),
            "method"            => urlencode($method),
            "timestamp"         => urlencode($time),
            "v"                 => urlencode($v)
        ];

        $fields_string = "";

        //用来计算md5，以appSecret开头
        $_tempString = $appSecret;

        foreach($fields as $key=>$value)
        {
            //直接将参数和值拼在一起
            $_tempString .= $key.$value;
            //作为url参数的字符串
            $fields_string .= $key.'='.$value.'&';
        }

        //最后再拼上appSecret
        $_tempString .= $appSecret;

        //计算md5，然后转为大写，sign参数作为url中的最后一个参数
        $sign = strtoupper(md5($_tempString));

        //加到最后
        $fields_string .= ("sign=".$sign);

        //最终请求的url
        $link = $baseurl.$fields_string;

        //发送get请求
        $output = Functions::http_judu($link);
        if($output){
            $return         = json_decode($output,true);
            $queryResult    = $return["jingdong_service_promotion_getcode_responce"]["queryjs_result"];
            $urlArr         = json_decode($queryResult,true);
            $url            = $urlArr['url'];
        }else{
            $url = $sourceurl;
        }
        header("Location: ".$url);
    }

}
?>