<?php
/**
 *
 * 微信服务类(无需修改)
*/
namespace common\components;

use Yii;
use yii\web\Controller;
use yii\web\Session;
use common\functions\Functions;

class WeixinService {

    //用于生成签名的token(申请接口时填写的)
    public $token           = '';

    //用户发送的消息
    private $request        = false;
    //回复的消息
    private $dataRecord     = false; //XML格式
    //回复的类型
    private $msgType        = 'text' ; //默认为文本型，可能值: text, music, news
    //appID，appsecret
    private $appId          = 'wx7bd199e4bd20490a';
    private $appsecret      = '4ce405a4be4c720ed76cff3bb92fe3a7';
    private $access_token   = '';
    private $template_id    = 'GXWkgHMJ0aOEmA8vAJmfgEq0lx2W_GzYy9Af3Sb9c-w';
    //通用回复模板
    private $commonTpl = "<xml>
        <ToUserName><![CDATA[%s]]></ToUserName>
        <FromUserName><![CDATA[%s]]></FromUserName>
        <CreateTime>%s</CreateTime>
        <MsgType><![CDATA[%s]]></MsgType>
        <FuncFlag>%s</FuncFlag>
        %s
    </xml>";
    private $message_default   = '';
    private $config            = [];
    //回复数组模板
    private $dataTpl = [
        'id'            =>  0,
        'answer'        =>  '',
        'request'       =>  '',
        'publicAccount' =>  '',
        'type'          =>  1,
        'description'   =>  '',
        'url1'          =>  '',
        'url2'          =>  '',
        'funcflag'      =>  0
    ];
    //匹配历史价格网址
    private $ruleArr    = [
                [
                    'path'  => 'http:\/\/zmnxbc.com\/',
                    'rule'  => '/\"bizId\":\"(\d+)\"/'
                ]
            ];

    //初使化加载
    public function  __construct(){
        $this->message_default = $this->getDefault();
        $this->config = $this->getEventConfig();
    }
    //获取服务器提交过来的信息
    public function getRequest() {
        //接收用户发送的信息
        $postStr = isset($GLOBALS["HTTP_RAW_POST_DATA"]) ? $GLOBALS["HTTP_RAW_POST_DATA"]: '';
        if (!empty($postStr)){
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $snType = strtolower($postObj->MsgType);
            if($snType == 'text') {
                //接受文本信息
                $this->request = array(
                    'publicAccount' =>  $postObj->ToUserName, //公众账号
                    'account'       =>  $postObj->FromUserName, //普通微信账号
                    'content'       =>  trim($postObj->Content), //发送的文本指令  
                    'type'          =>  'text'
                );
            } elseif($snType == 'image'){
                //图片消息
                $this->request = array(
                    'publicAccount' =>  $postObj->ToUserName, //公众账号
                    'account'       =>  $postObj->FromUserName, //普通微信账号
                    'picurl'        =>  $postObj->PicUrl  , //图片地址
                    'content'       =>  '',
                    'type'          =>  'image'
                );
            } elseif($snType == 'location') {
                //地理信息
                $this->request = array(
                    'publicAccount' =>  $postObj->ToUserName, //公众账号
                    'account'       =>  $postObj->FromUserName, //普通微信账号
                    'type'          =>  'location',
                    'content'       =>  '',
                    'x'             =>  $postObj->Location_X, //维度
                    'y'             =>  $postObj->Location_Y, //经度
                    'label'         =>  $postObj->Label     //地理位置信      
                );
            } elseif($snType == 'link') {
                //链接
                $this->request = array(
                    'publicAccount' =>  $postObj->ToUserName, //公众账号
                    'account'       =>  $postObj->FromUserName, //普通微信账号
                    'type'          =>  'link',
                    'content'       =>  '',
                    'title'         =>  $postObj->Title,  //消息标题 
                    'desc'          =>  $postObj->Description , //消息描述 
                    'url'           =>  $postObj->Url
                 );
            } elseif($snType == 'event') {
                //事件
                $this->request =  array(
                    'publicAccount' =>  $postObj->ToUserName, //公众账号
                    'account'       =>  $postObj->FromUserName, //普通微信账号
                    'content'       =>  '',
                    'type'          =>  'event',
                    'event'         =>  $postObj->Event,
                    'eventkey'      =>  $postObj->EventKey
                );
            }
        }
        return $this->request;
    }

    //网址接入校验
    public function valid() {
        $echoStr = isset($_GET["echostr"]) ? $_GET["echostr"] : '';        
        if($this->checkSignature()) {          
            echo $echoStr;          
            return true;
        }
        return false;
    }

    //设置回复内容
    public function setDataRecord($data = false) {
        $this->dataRecord = $data;
    }


    //自动回复
    public function answer() {
        // if($this->request == false) return ''; //不需要回复
        if(empty($this->dataRecord))    return ''; //不回复
        $tmpResult = $this->dataRecord;
     
        //获取回复数组中回复类型，若回复的数组为二维数组则回复图文,一维数组则获取类型
        $msgType = $this->is2Array($tmpResult) ? 3 : $tmpResult['type'];
        $msgType = intval($msgType);
        $msgType = ($msgType <= 0) ? 1 : $msgType;

        switch($msgType) {
            case 2 : //音乐型
                $this->msgType = 'music';
                $title = $tmpResult['answer'];
                $desc  = $tmpResult['description'];
                $murl  = $tmpResult['url1'];
                $hurl  = $tmpResult['url2'];
                $funcFlag = empty($tmpResult['funcflag']) ? 0 : 1;
                $privStr = "<Music>
            <Title><![CDATA[${title}]]></Title>
            <Description><![CDATA[${desc}]]></Description>
            <MusicUrl><![CDATA[${murl}]]></MusicUrl>
            <HQMusicUrl><![CDATA[${hurl}]]></HQMusicUrl>
        </Music>";
                return sprintf($this->commonTpl, $this->request['account'], $this->request['publicAccount'], time(), $this->msgType, $funcFlag, $privStr);

            break;

            case 3 : //图文型
                $this->msgType = 'news';
                $totalNews = count($tmpResult); //新闻个数
                $totalNews = $totalNews < 10 ? $totalNews : 10;
                $funcFlag = 0;

                $privTpl = "<ArticleCount>${totalNews}</ArticleCount>
        <Articles>
            %s
        </Articles>";

                $itemTpl = "<item>
                <Title><![CDATA[%s]]></Title>
                <Description><![CDATA[%s]]></Description>
                <PicUrl><![CDATA[%s]]></PicUrl>
                <Url><![CDATA[%s]]></Url>
            </item>";
            
               $itemStr = '';
                for($ti = 0; $ti < $totalNews; $ti++) {
                    $tmpStr = sprintf($itemTpl, $tmpResult[$ti]['answer'], $tmpResult[$ti]['description'], $tmpResult[$ti]['url1'], $tmpResult[$ti]['url2']);
                    $itemStr .= $tmpStr;
                    unset($tmpStr);
                }
                $privStr = sprintf($privTpl, $itemStr);
                return sprintf($this->commonTpl, $this->request['account'], $this->request['publicAccount'], time(), $this->msgType, $funcFlag, $privStr);
            break;
            case 4 :// 图片型 
                $this->msgType = 'image';
                $MediaId = $tmpResult['answer'];
                $funcFlag = empty($tmpResult['funcflag']) ? 0 : 1;
                $privStr = "<Image>
            <MediaId><![CDATA[${MediaId}]]></MediaId>
        </Image>";
                return sprintf($this->commonTpl, $this->request['account'], $this->request['publicAccount'], time(), $this->msgType, $funcFlag, $privStr);
            break;
            default : //文本型
                $this->msgType = "text";
                $funcFlag = empty($tmpResult['funcflag']) ? 0 : 1;
                $privStr = "<Content><![CDATA[" . $tmpResult['answer'] . "]]></Content>";   
                return sprintf($this->commonTpl, $this->request['account'], $this->request['publicAccount'], time(), $this->msgType, $funcFlag, $privStr);
            break;
        }
    }
    //向用户发模板消息
    public function sendTempMsg(){
        $getRequest    =   $this->getRequest();
        $time          =   date('m月d日');
        $account       =   (string)$this->request['account'];
        $string        =   (string)$this->request['content'];
        //首次加关注,欢迎消息
        if($this->request['type'] == 'event') {
            $reply      = '';
            $eventKey   =  (string)$this->request['eventkey'];
            $event      =  (string)$this->request['event'];
            switch ($event) {
                 case 'subscribe':
                    $reply = $this->config['subscribe'];
                    break;
                 default:
                    $reply = isset($this->config[$eventKey]) ? $this->config[$eventKey] : $this->message_default;
                    break;
            }
            $tmpArr = $this->dataTpl;
            $tmpArr['answer'] = $reply;
            $this->setDataRecord($tmpArr);
            return $this->answer();
        }

        if($this->request['type'] == 'text'){
            $tmpArr = $this->getGoods($string);
            if(!$tmpArr){
                //匹配关键字回复
                $tmpArr = $this->getReply($string);
            }
            $this->setDataRecord($tmpArr);
            return $this->answer();
        }
    }
    //生成签名
    private function checkSignature() {
        $signature = isset($_GET["signature"]) ? $_GET["signature"] : '';     
        $timestamp = isset($_GET["timestamp"]) ? $_GET["timestamp"] : '';     
        $nonce = isset($_GET["nonce"]) ? $_GET["nonce"] : '';  

        $sql   = "SELECT svalue FROM {{%common_setting}} WHERE skey = 'weixin_token'";
        $token =  Yii::$app->db->createCommand($sql)->queryScalar();               

        $tmpArr = array($token, $timestamp, $nonce);        
        sort($tmpArr, SORT_STRING);      
        $tmpStr = implode( $tmpArr );       
        $tmpStr = sha1( $tmpStr );              
        if( $tmpStr == $signature ){            
            return true;        
        } else {          
            return false;       
        }   
    }

    //是否为二维数组
    private function is2Array($d) {
        if(is_array($d)) {
            foreach($d as $e) {
                return is_array($e);
            }
        }
        return false;
    }
    //获取access_token
    PUBLIC function getToken(){
        $time       = time();
        $baseUrl    = dirname(Yii::$app->BasePath).'/frontend/web/uploads/';
        $data       = json_decode(@file_get_contents($baseUrl."api_access_token.json"),true);

        if (empty($data) || $data['expire_time'] < $time) {
            $params =  [
                'grant_type'=> 'client_credential',
                'appid'     => $this->appId,
                'secret'    => $this->appsecret
            ];
            $url    =   "https://api.weixin.qq.com/cgi-bin/token";
            $result =   Functions::http_judu($url,$params,'POST');

            if ($result) {
                $json = json_decode($result, true);
                if (!$json || isset($json['errcode'])) {
                    return false;
                }
                $access_token = $json['access_token'];

                $data                 = [];
                $data['expire_time']  = $time + 100;
                $data['access_token'] = $access_token;

                file_put_contents($baseUrl."api_access_token.json",json_encode($data));
            }
        } else {
            $access_token = $data['access_token'];
        }
        return $access_token;
    }
    /**
     * [getGoods 获取商品信息]
     * @return [type] [description]
     */
    private function getGoods($string){
        if(!$string) return false;

        $goodsId    = 0;
        $goodsUrl   = '';
        $rule       = '';
        $title      = '查看商品历史价格';

        preg_match('/(https|http|ftp|rtsp|mms):\/\/[^\s]+/', $string, $matches);
        if(!$matches){ return false;}

        $goodsUrl = $matches['0'];
        $rule     = '/var url = \'https:\/\/item\.taobao\.com\/item\.htm.*&id=(\d+)&/';

        foreach ($this->ruleArr as $key => $value) {
            preg_match('/'.$value['path'].'[^\s]+/', $string, $matches);
            if($matches){
                $goodsUrl = $matches['0'];
                $rule     = $value['rule'];
            }
        }

        if(!$goodsUrl){ return false;}
        $html = Functions::http_judu($goodsUrl);
        //匹配
        if(preg_match($rule, $html, $info)){
            $goodsId  = $info['1'];
        }

        if(!$goodsId){ return false;}
        //匹配标题
        preg_match('/【(.*)】/', $string, $nameInfo);

        if($nameInfo) $title = $nameInfo['1'];
        $url = Yii::$app->params['mfrontendUrl'].'app/historical?id='.$goodsId;

        //拼接方案
        $eachTmpArr = [];
        $returnData = [];
        $eachTmpArr['answer'] = $title;
        $eachTmpArr['description'] = '历史价格图';
        $eachTmpArr['url1'] = '';
        $eachTmpArr['url2'] = $url;
        $returnData[] = $eachTmpArr;

        return $returnData;
    }
    /**
     * [getReply 获取关键字回复]
     * @return [type] [description]
     */
    private function getReply($string){
        if(!$string) return false;

        $selectSql  = " SELECT `type`,`reply`,`keyword` FROM {{%weixin_reply}}  WHERE  type != '2'  AND keyword = '$string'  ORDER BY id DESC";

        $replyArr   = Yii::$app->db->createCommand($selectSql)->queryOne();

        //如果全匹无结果，搜索半匹配
        if(!$replyArr){
            $keyWords  = $this->getKeyWords();
            foreach ($keyWords as $key => $value) {
                if(preg_match("/$value[keyword]/",$string)){
                    $replyArr = $value;
                }
            }
        }

        $reply      = [];
        switch ($replyArr['type']) {
            case '1':
                $reply['type']     = 1;
                $reply['answer']   = htmlspecialchars_decode($replyArr['reply']);
                break;
            case '3':
                $reply['type']     = 4;
                $reply['answer']   = $replyArr['reply'];
                break;
            case '4':
                $reply             = $this->getArticleList($replyArr['reply']);
                break;  
            default:
                $reply['type']     = 1;
                $reply['answer']   = $this->message_default;
                break;
        }

        return $reply;
    }
    /**
     * [getKeyWords 获取关键字]
     * @return [type] [description]
     */
    private function getKeyWords(){
        $cache          =    Yii::$app->cache;
        $results        =    $cache->get('weixin_keywords_reply');
        if(!$results){
            $results    =  [];
            $selectSql  =  "SELECT `type`,`reply`,`keyword` FROM {{%weixin_reply}}  WHERE  type != '2' AND match_mode = 'contain' ORDER BY id DESC";

            $replyArr   = Yii::$app->db->createCommand($selectSql)->queryAll();

            foreach ($replyArr as $key => $value) {
                $results[] = $value;
            }   

            $cache->set('weixin_keywords_reply',$results,300);
        }
        return $results;
    }
    /**
     * [getConfig 获取配置信息]
     * @return [type] [description]
     */
    private function getEventConfig(){
        $cache          =    Yii::$app->cache;
        $results        =    $cache->get('weixin_event_config');
        if(!$results){
            $results    =  [];
            $selectSql  =  "SELECT `reply`,`keyword` FROM {{%weixin_reply}}  WHERE  type = '2'";

            $replyArr   = Yii::$app->db->createCommand($selectSql)->queryAll();

            foreach ($replyArr as $key => $value) {
                $results[$value['keyword']] = $value['reply'];
            }   

            $cache->set('weixin_event_config',$results,300);
        }
        return $results;
    }
    /**
     * [getDefault 默认的回复]
     * @return [type] [description]
     */
    private function  getDefault(){
        $cache          =    Yii::$app->cache;
        $reply          =    $cache->get('message_default_autoreply_info');
        if(!$reply){
            $selectSql  =  "SELECT `reply` FROM {{%weixin_reply}}  WHERE  type = '0'";

            $reply      = Yii::$app->db->createCommand($selectSql)->queryScalar();

            $cache->set('message_default_autoreply_info',$reply,300);
        }
        return $reply;
    }
    /**
     * 获取资讯内容
     * @param   int $catid [分类ID,默认为news,可多个ID]
     * @param   string $game [指定游戏缩略名,默认为所有游戏,可多个游戏]
     * @param   int $start [开始的记录数号，默认为0]
     * @param   int $limit [要获取的记录总数，默认为5条]
     * @return  Array [微信接口对应的数组]
     */
    public function getArticleList($keyword) {
        if(!$keyword)  return false;
        $allListArr = $this->getMaterial($keyword);
        $returnData = [];
        foreach($allListArr as $eachArt) {  
            $eachTmpArr['answer'] = $eachArt['title'];
            $eachTmpArr['description'] = $eachArt['digest'];
            $eachTmpArr['url1'] = $eachArt['thumb_url'];
            $eachTmpArr['url2'] = $eachArt['url'];
            $returnData[] = $eachTmpArr;
            unset($eachTmpArr);
        }   
        return $returnData;
    }
    /**
     * [getMaterial 获取素材列表]
     * @return [type] [description]
     */
    PUBLIC function  getBatchgetMaterial($type = 'image',$offset = 0,$count = 20){
        $access_token = $this->getToken();
        $url    = 'https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token='.$access_token;
        $params = [
            'type'      =>  $type,
            'offset'    =>  $offset,
            'count'     =>  $count
        ];
        $params = json_encode($params);
        $return = Functions::http_judu($url,$params,'POST',[],true);
        $return = json_decode($return,true);
        return isset($return['item']) ? $return['item'] : '';
    }
    /**
     * [getMaterial 获取素材总数]
     * @return [type] [description]
     */
    PUBLIC function  getMaterialCount(){
        $access_token = $this->getToken();
        $url    = 'https://api.weixin.qq.com/cgi-bin/material/get_materialcount?access_token='.$access_token;
        $params = [
        ];
        $params = json_encode($params);
        $return = Functions::http_judu($url,$params,'POST',[],true);
        $return = json_decode($return,true);
        return $return;
    }
    /**
     * [getMaterial 获取素材详情--只支持文章]
     * @return [type] [description]
     */
    PUBLIC function  getMaterial($mid){
        $access_token = $this->getToken();
        $url    = 'https://api.weixin.qq.com/cgi-bin/material/get_material?access_token='.$access_token;
        $params = [
            'media_id'  =>  $mid
        ];

        $params = json_encode($params);
        $return = Functions::http_judu($url,$params,'POST',[],true);
        $return = json_decode($return,true);
        return isset($return['news_item']) ? $return['news_item'] : '';
    }
}
?>
