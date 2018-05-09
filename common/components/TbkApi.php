<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
//文档出处：http://open.taobao.com/docs/api.htm?spm=a219a.7395905.0.0.6yR5qW&apiId=24518
namespace common\components;
use Yii;
/**
 * Description of Functions
 *
 * @author Administrator
 */
class TbkApi {
    public static $tbUrl = 'https://eco.taobao.com/router/rest';
    public static $jhUrlTrans   = 'https://m.680ju.com/api/taobao/urlTrans.json';//'http://m.680ju.com/api/taobao/urlTrans.json';
    public static $tbCouponUrl  = 'http://pub.alimama.com/items/search.json?q=https%3A%2F%2Fitem.taobao.com%2Fitem.htm%3Fid%3D';
    public static $proxyUrl     = 'http://miguanvpn.f3322.net:18000/get_all/';
    public static $TbkApiArr=[
        '1'=>'taobao.tbk.item.get',//(淘宝客商品查询)
        '2'=>'taobao.tbk.item.info.get',// (淘宝客商品详情（简版）)
        '3'=>'taobao.tbk.dg.item.coupon.get',//(好券清单API【导购】)
        '4'=>'taobao.tbk.shop.recommend.get',//(淘宝客商品关联推荐查询)
        '5'=>'taobao.tbk.item.convert',// (淘宝客商品链接转换) 
        '6'=>'taobao.tbk.coupon.get',// (阿里妈妈推广券信息查询)
        '7'=>'taobao.tbk.item.recommend.get', //(淘宝客商品关联推荐查询)
    ];

    PUBLIC STATIC function getCommonArr($apiId){
        return $arr  = array(
            'method'=> self::$TbkApiArr[$apiId],
            'app_key'=>'24577978',
            'sign_method'=>'md5',
            'timestamp'=>date('Y-m-d H:i:s'),
            'v'=>'2.0',
            'format'=>'json',
            'adzone_id'=>'99532920',
            );
    }

    PUBLIC STATIC function getGoodsList($params=[]){
        if(!$params) return ;
        $combineArr  = array(
            'fields'=>'num_iid,title,pict_url,small_images,reserve_price,zk_final_price,user_type,provcity,item_url,seller_id,volume,nick',
            'q'=>isset($params['q']) ? $params['q'] : '',
            'cat'=>isset($params['cat']) ? $params['cat'] : '',
            'sort'=>isset($params['sort']) ? $params['sort'].'_des' : 'total_sales_des',//排序_des（降序），排序_asc（升序），销量（total_sales），淘客佣金比率（tk_rate）， 累计推广量（tk_total_sales），总支出佣金（tk_total_commi）
            'is_tmall'=>isset($params['is_tmall']) ? $params['is_tmall'] : 'false',//是否商城商品，设置为true表示该商品是属于淘宝商城商品，设置为false或不设置表示不判断这个属性
            'page_no'=>isset($params['page_no']) ? $params['page_no'] : 1,
            'page_size'=>isset($params['page_size']) ? $params['page_size'] : 100,
            );
        $arr = array_merge(self::getCommonArr(1),$combineArr);
        $sub_type = 'post';
        $arr['sign'] = self::getTaobaoSign($arr);

        $return = self::http_judu(self::$tbUrl,$arr,$sub_type);
        $arr    = json_decode($return,true);
        $res    = [];

        if(!empty($arr['tbk_item_get_response']['results'])){
            $res = $arr['tbk_item_get_response']['results']['n_tbk_item'];
        }
        return $res;

    }
    //(好券清单API【导购】列表)
    PUBLIC STATIC function getDgCouponInfo($params=[]){
        $combineArr  = array(
            'q'=>isset($params['q']) ? $params['q'] : '',
            'page_size'=>isset($params['pageSize']) ? $params['pageSize'] : 20,
            );
        $arr = array_merge(self::getCommonArr(3),$combineArr);
        $sub_type = 'post';
        $arr['sign'] = self::getTaobaoSign($arr);
        $return = self::http_judu(self::$tbUrl,$arr,$sub_type);
        $arr    = json_decode($return,true);
        $res    = [];

        if(!empty($arr['tbk_dg_item_coupon_get_response']['results'])){
            $res = $arr['tbk_dg_item_coupon_get_response']['results']['tbk_coupon'];
        }
        return $res;
    }

    //(好券清单API【导购】单个)
    PUBLIC STATIC function getDgCouponOne($params=[]){
        $combineArr  = array(
            'q'=>isset($params['q']) ? $params['q'] : '',
            'page_no'=>isset($params['page_no']) ? $params['page_no'] : 1,
            'page_size'=>isset($params['page_size']) ? $params['page_size'] : 100,
            );
        $arr = array_merge(self::getCommonArr(3),$combineArr);
        $sub_type = 'post';
        $arr['sign'] = self::getTaobaoSign($arr);
        $return = self::http_judu(self::$tbUrl,$arr,$sub_type);
        $arr    = json_decode($return,true);
        $ret = [];
        if(!empty($arr['tbk_dg_item_coupon_get_response']['results'])){
            $res = $arr['tbk_dg_item_coupon_get_response']['results']['tbk_coupon'];
            if($res){
                if(count($res) == 1) return $res;
                if(isset($params['num_iid'])){
                    foreach ($res as $key => $value) {
                        if($value['num_iid'] == $params['num_iids']){
                            $ret = [
                            'coupon_total_count'=>$value['coupon_total_count'],
                            'commission_rate'=>$value['commission_rate'],
                            'coupon_info'=>$value['coupon_info'],
                            'coupon_remain_count'=>$value['coupon_remain_count'],
                            'coupon_end_time'=>$value['coupon_end_time'],
                            'coupon_click_url'=>$value['coupon_click_url'],
                            ];
                        }
                    }
                }
            }
        }
        return $ret;
    }

    PUBLIC STATIC function getCouponInfo($params=[]){
        if(!$params['me']) return ;
        $combineArr  = array(
            'me'=>isset($params['me']) ? $params['me'] : '',
            );
        $arr = array_merge(self::getCommonArr(6),$combineArr);
        $sub_type = 'post';
        $arr['sign'] = self::getTaobaoSign($arr);

        $return = self::http_judu(self::$tbUrl,$arr,$sub_type);
        $arr    = json_decode($return,true);

        return $arr;
    }

    PUBLIC STATIC function getGoodsInfo($params=[]){
        if(!$params['num_iids']) return false;

        $ids_str = is_array($params['num_iids']) ? self::link_create_id($params['num_iids'],$type) : $params['num_iids'];
        //文档出处：http://open.taobao.com/docs/api.htm?spm=a219a.7395905.0.0.6yR5qW&apiId=24518
        $url = self::$tbUrl;
        $combineArr  = array(
            'fields'=>'num_iid,title,pict_url,small_images,reserve_price,zk_final_price,user_type,provcity,item_url,seller_id,volume,nick,cat_name',
            'num_iids'=>$ids_str,
            );
        $arr = array_merge(self::getCommonArr(2),$combineArr);
        $arr['sign'] = self::getTaobaoSign($arr);
        $return = self::http_judu($url,$arr);
        $arr    = json_decode($return,true);
        var_dump($arr);die;
        $res    = 0;
        if(!empty($arr['tbk_item_info_get_response']['results'])){
            $res = $arr['tbk_item_info_get_response']['results']['n_tbk_item'];
        }
        return $res[0];
    }

    PUBLIC STATIC function getShopRecommend($params=[]){
        if(!$params['user_id']) return ;
        $combineArr  = array(
            'fields'=>'user_id,shop_title,shop_type,seller_nick,pict_url,shop_url ',
            'user_id'=>isset($params['user_id']) ? $params['user_id'] : '',
            'count'=>isset($params['count']) ? $params['count'] : 20,
            );
        $arr = array_merge(self::getCommonArr(4),$combineArr);
        $sub_type = 'post';
        $arr['sign'] = self::getTaobaoSign($arr);

        $return = self::http_judu(self::$tbUrl,$arr,$sub_type);
        $arr    = json_decode($return,true);
        $res    = [];
        if(!empty($arr['tbk_shop_recommend_get_response']['results'])){
            $res = $arr['tbk_shop_recommend_get_response']['results']['n_tbk_shop'];
        }
        return $res;
    }

    PUBLIC STATIC function getGoodRecommend($params=[]){
        if(!$params['num_iids']) return ;
        $combineArr  = array(
            'fields'=>'num_iid,title,pict_url,small_images,reserve_price,zk_final_price,user_type,provcity,item_url',
            'num_iid'=>isset($params['num_iids']) ? $params['num_iids'] : '',
            'count'=>isset($params['count']) ? $params['count'] : 20,
            );
        $arr = array_merge(self::getCommonArr(7),$combineArr);
        $sub_type = 'post';
        $arr['sign'] = self::getTaobaoSign($arr);

        $return = self::http_judu(self::$tbUrl,$arr,$sub_type);
        $arr    = json_decode($return,true);
        $res    = [];
        if(isset($arr['tbk_item_recommend_get_response']['results'])){
            $res = $arr['tbk_item_recommend_get_response']['results']['n_tbk_item'];
        }
        return $res;
    }

    PUBLIC STATIC function getConvertLink($params=[]){
        if(!$params['num_iids']) return ;

        $ids_str = is_array($params['num_iids']) ? self::link_create_id($params['num_iids'],$type) : $params['num_iids'];

        $combineArr  = array(
            'fields'=>'num_iid,click_url',
            'num_iids'=>$ids_str,
            'dx'=>1,
            );
        $arr = array_merge(self::getCommonArr(5),$combineArr);
        $sub_type = 'post';
        $arr['sign'] = self::getTaobaoSign($arr);

        $return = self::http_judu(self::$tbUrl,$arr,$sub_type);
        $arr    = json_decode($return,true);
        return $arr;
    }

    //聚惠心选 获取优惠券链接
    PUBLIC STATIC function getJuCouponInfo($params=[]){
        if(!$params['num_iids']) return ;

        $ids_str    = is_array($params['num_iids']) ? self::link_create_id($params['num_iid'],$type) : $params['num_iids'];
        $url        = self::$tbCouponUrl.$ids_str;
        $return     = file_get_contents($url);
        $arr        = json_decode($return,true);
        $res        = $arr['data']['pageList'][0];
        if(!empty($arr['data']['pageList'])){
            foreach ($arr['data']['pageList'] as $key => $value) {
                if($res['couponAmount'] < $value['couponAmount']) $res = $value;
            }
        }
        $ret        = [];
        if(!empty($res) && !empty($res['couponAmount'])){
            $ret['couponStartFee']          = $res['couponStartFee'] ? $res['couponStartFee'] : '';
            $ret['couponTotalCount']        = $res['couponTotalCount'] ? $res['couponTotalCount'] : '';
            $ret['couponLeftCount']         = $res['couponLeftCount']? $res['couponLeftCount'] : '';
            $ret['couponAmount']            = $res['couponAmount']? $res['couponAmount'] : '';
            $ret['couponEffectiveStartTime']= $res['couponEffectiveStartTime']? strtotime($res['couponEffectiveStartTime']) : '';
            $ret['couponEffectiveEndTime']  = $res['couponEffectiveEndTime']? strtotime($res['couponEffectiveEndTime'])+86399 : '';
            $ret['couponInfo']              = $res['couponInfo']? $res['couponInfo'] : '';
            $ret['couponLeftAmount']        = $res['couponStartFee'] ? $res['couponStartFee']-$ret['couponAmount'] : '';
        }
        
        return $ret;
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
        $key = 'de94a61849f1a3391cb99c1a1e880d7e';
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
        if(!empty($params['proxy'])){
            $opts[CURLOPT_PROXY] = $params['proxy'];
        }
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
        if($error) {var_dump($error);die;}
        return  $data;
    }
    

}
