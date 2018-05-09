<?php
namespace common\components;


class Mobile {
    
    /**
     * 发送验证码
     * @param unknown $m
     * @param unknown $c
     * @return unknown
     */
    public static function captcha($m,$c)
    {
        $url = "https://sms.yunpian.com/v2/sms/single_send.json";
        $apikey = "842b480cd350eda35800598b6387fba4"; //修改为您的apikey(https://www.yunpian.com)登陆官网后获取
        $mobile = $m; //请用自己的手机号代替
        $text = "【她们说】您的验证码是：{$c}。请不要把验证码泄露给其他人。";
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
    
    
}