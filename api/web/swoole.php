<?php
require_once __DIR__ .'/../../vendor/jpush/autoload.php';
class Server
{
    private $serv;
    private $serviceId  = 1;
    private $conn       = null;
    private static $fd  = null;
    private $type       = 4;
    private $config     = [];

    public function __construct()
    {
        $this->config = include(__DIR__ . '/../../common/config/params.php'); 
        $this->initDb();
        $this->serv = new swoole_websocket_server("0.0.0.0", 9502);
        $this->serv->set(array(
            'daemonize'     => 1,                       // 是否是守护进程
            'max_request'   => 10000,                   // 最大连接数量
            'dispatch_mode' => 2,
            'debug_mode'    => 1,
            'heartbeat_check_interval' => 30,           //心跳检测的设置，自动踢掉掉线的fd
            'heartbeat_idle_time'      => 600,          //TCP连接的最大闲置时间，单位s
            'log_file'      => '/data0/logs/cron/swoole.log',  //错误日志
            'pid_file'      => __DIR__.'/server.pid',
        ));

        $this->serv->on('Open', array($this, 'onOpen'));
        $this->serv->on('Message', array($this, 'onMessage'));
        $this->serv->on('Close', array($this, 'onClose'));

        $this->serv->start();

    }
    /**
     * [onOpen 开启聊天进程]
     * @param  [type] $server [description]
     * @param  [type] $frame  [description]
     * @return [type]         [description]
     */
    function onOpen($server, $frame)
    {

    }
    /**
     * [onMessage 消息推送]
     * @param  [type] $server [description]
     * @param  [type] $frame  [description]
     * @return [type]         [description]
     */
    public function onMessage($server, $frame)
    {
        $data       = [];
        $pData      = json_decode($frame->data,true);
        $content    = isset($pData['content']) ? $pData['content'] : '';
        $first      = isset($pData['first']) ? intval($pData['first']) : 0;
        $token      = isset($pData['token']) ? self::checkStr($pData['token']) : '';
        $number     = isset($pData['number']) ? self::checkStr($pData['number']) : '1';
        $system     = isset($pData['system']) ? self::checkStr($pData['system']) : '';
        $model      = isset($pData['model']) ? self::checkStr($pData['model']) : '';
        $source     = isset($pData['source']) ? self::checkStr($pData['source']) : 'android';
        $source     = $source == 'android' ? 1 : 2;
        $receiveId  = isset($pData['receiveId']) ? intval($pData['receiveId']) : $this->serviceId;
        $adminId    = isset($pData['adminId']) ? intval($pData['adminId']) : 0;

        //如果用户不存在
        session_start();
        $userInfo       = [];
        if($token){
            $userInfo   = $token  ? $this->checkToken($token) : '';
            $userInfo   = !empty($userInfo) ?  $userInfo : '';
        }elseif($receiveId != $this->serviceId){
            $sql  = "SELECT username FROM  yjy_admin  WHERE  id = '$adminId'";
            if ($query = $this->conn->query($sql)) {
                $data       = mysqli_fetch_assoc($query);
                $username   = $data['username'];
            }
            $userInfo   = ['id' => '1','username' => $username];
            }else{
                $userInfo   = [];
            }

        if(empty($userInfo)){
            $data = array('status'=>'0','msg' => '用户未登录');
            $server->push($frame->fd, json_encode($data));  //推送到发送者
            return true;
        }

        $userId     = $userInfo['id'];
        $username   = $userInfo['username'];
        $img        = $userInfo['img'];
        //参数
        $params = [
            'username'  => $username,
            'content'   => $content,
            'userId'    => $userId,
            'number'    => $number,
            'source'    => $source,
            'system'    => $system,
            'model'     => $model,
            'receiveId' => $receiveId,
            'adminId'   => $adminId,
            'img'       => $img
        ];

        if ($first) {
            $pmId = $adminId ? $receiveId : $userId;
            $this->unBind($userId,$receiveId); //首次接入，清除绑定数据
            $this->bind($userId,$receiveId,$frame->fd);
            $data = $this->loadHistory($pmId); //加载历史记录
            // } else {
                // $data = array('status'=>'0','msg' => '开启失败');
            // }
        } else {
            $tfd  = $this->getFd($userId,$receiveId);   //获取绑定的fd
            $data = $this->add($params);                //保存消息
            if($tfd){
               $server->push($tfd, json_encode($data));    //推送到接收者 
            }
        }
        $server->push($frame->fd, json_encode($data));  //推送到发送者
    }

    /**
     * [onClose 进程关闭]
     * @param  [type] $server [description]
     * @param  [type] $fd     [description]
     * @return [type]         [description]
     */
    public function onClose($server, $fd)
    {
        //$this->unBind($fd);
        // echo "connection close: " . $fd;
    }

    /**
     * [initDb 连接数据]
     * @return [type] [description]
     */
    function initDb()
    {
        //$conn = mysqli_connect("127.0.0.1", "root", "136007");
        $conn = mysqli_connect("127.0.0.1", "yjyapp", "2qHrEigSFkdqMfvg");
        if (!$conn) {
            die('Could not connect: ' . mysql_error());
        } else {
            mysqli_select_db($conn, "yjyapp");
        }
        $this->conn = $conn;
    }
    /**
     * [add 添加数据]
     * @param [type] $fid     [description]
     * @param [type] $tid     [description]
     * @param [type] $type    [description]
     * @param [type] $content [description]
     */
    public function add($params)
    {
        $time       = time();
        $receiveId  = $params['receiveId'];
        $userId     = $params['userId'];
        $message    = $params['content'];
        $dataTime   = date('Y-m-d H:i:s');
        $adminId    = $params['adminId'];
        $endTime    = 0;

        $isPirce    = $this->strIsImg($message);
        if($isPirce != '图片'){
            $message = self::linkToUrl($message);
        }
        $insert_message    = self::userTextEncode($message,1);

        if($receiveId == $this->serviceId){
            $timeSql = "SELECT created_at FROM yjy_user_feedback WHERE user_id='$userId' ORDER BY created_at DESC LIMIT 1";
            $sql     = "INSERT INTO yjy_user_feedback (user_id,username,source,`number`,`system`,`model`,content,created_at) 
                VALUES ('$userId','$params[username]','$params[source]','$params[number]','$params[system]','$params[model]','$insert_message','$time')"; 
        }else{
            $type       = $this->type;
            $timeSql    = "SELECT created_at FROM yjy_pms WHERE receive_id='$userId' AND type = '$type' ORDER BY created_at DESC LIMIT 1";

            $sql        = "INSERT INTO yjy_pms (type,from_id,receive_id,`message`,created_at,updated_at) 
                VALUES ('$type','$adminId','$receiveId','$insert_message','$time','$time')"; 
            $feedSql    = "UPDATE yjy_user_feedback SET is_feedback = 1  WHERE user_id = '$receiveId'";
            $this->conn->query($feedSql);
            //推送
            $this->JPushOne(['Alias' => $receiveId, 'id' => 0, 'type' => 0, 'option' => 'feedback', 'replaceStr' => $isPirce]);
        }
        //查最后时间
        if ($query = $this->conn->query($timeSql)) {
            $data       = mysqli_fetch_assoc($query);
            $endTime    = $data['created_at'];
            $dataTime   = $time - $endTime >= 600 ? $dataTime : '';
        }

        if ($this->conn->query($sql)) {
            $id         = $this->conn->insert_id;
            $img        = $adminId ? $this->get_image_path('swoole/xy-tx.png') : $this->get_image_path($params['img']);
            $data       = ['fid' => $userId,'username'=>$params['username'],'tid' => $receiveId,'aid' => $adminId,'img' =>$img,'content' => $message,'created_at' => $dataTime];
            $return     = ['status' => '1','msg' => [$data]];
            return $return;
        }
    }
    /**
     * [bind 绑定用户进程]
     * @param  [type] $uid [description]
     * @param  [type] $fd  [description]
     * @return [type]      [description]
     */
    public function bind($fid,$tid,$fd)
    {

        $sql = "SELECT * FROM yjy_swoole_fd WHERE fid ='$fid' AND tid = '$tid' limit 1";
        $query = $this->conn->query($sql);
        $data = mysqli_fetch_assoc($query);
        if(empty($data)){
            $sql = "INSERT INTO yjy_swoole_fd (fid,tid,fd) VALUES ('$fid','$tid','$fd')";
        }else{
            $sql = "UPDATE yjy_swoole_fd SET fd ='$fd' WHERE fid='$fid' AND tid='$tid'";
        }
        if ($this->conn->query($sql)) {
            return true;
        }
    }
    /**
     * [getFd 获取用户进程]
     * @param  [type] $uid [description]
     * @return [type]      [description]
     */
    public function getFd($fid,$tid)
    {
        if(!$fid || !$tid) return false;
        $row = "";
        $sql = "SELECT * FROM yjy_swoole_fd WHERE tid ='$fid' AND fid = '$tid' limit 1";
        if ($query = $this->conn->query($sql)) {
            $data = mysqli_fetch_assoc($query);
            $row = $data['fd'];
        }
        return $row;
    }
    /**
     * [unBind 解决绑定进程]
     * @param  [type] $fd  [description]
     * @param  [type] $uid [description]
     * @return [type]      [description]
     */
    public function unBind($fid, $tid)
    {
        if(!$fid || !$tid) return false;
        $sql = "DELETE FROM yjy_swoole_fd WHERE fid='$fid' AND tid='$tid'";
        if ($this->conn->query($sql)) {
            return true;
        }
    }
    /**
     * [loadHistory 聊天历史记录]
     * @param  [type] $fid [description]
     * @param  [type] $tid [description]
     * @return [type]      [description]
     */
    public function loadHistory($userId)
    {
        $type     = $this->type;
        $pms_sql  = "SELECT P.message AS content,A.username,P.created_at,P.status FROM yjy_pms P 
                    LEFT JOIN yjy_admin A  ON P.from_id = A.id
                    WHERE P.type = '$type' AND P.receive_id = '$userId'";
        $feed_sql = "SELECT U.id,U.username,F.content,F.created_at,U.img,F.status FROM yjy_user_feedback F 
                    LEFT JOIN yjy_user U  ON F.user_id = U.id
                    WHERE F.user_id = '$userId'";

        $data     = [];
        if ($query = $this->conn->query($pms_sql)) {
            while ($row = mysqli_fetch_assoc($query)) {
                $newRow                 = [];
                $newRow['fid']          = $this->serviceId;
                $newRow['tid']          = $userId;
                $newRow['aid']          = 1;
                $newRow['status']       = $row['status'];
                $newRow['img']          = $this->get_image_path('swoole/xy-tx.png');
                $newRow['username']     = self::userTextDecode($row['username']);
                $newRow['content']      = self::userTextDecode($row['content']);
                $newRow['created_at']   = date('Y-m-d H:i:s',$row['created_at']);
                $data[] = $newRow;
                unset($newRow);
            }
        }
        if ($feedQuery = $this->conn->query($feed_sql)) {
            while ($feedRow = mysqli_fetch_assoc($feedQuery)) {
                $newfeedRow             = [];
                $newfeedRow['fid']      = $feedRow['id'];
                $newfeedRow['tid']      = $this->serviceId;
                $newfeedRow['aid']      = 0;
                $newfeedRow['status']   = $feedRow['status'];
                $newfeedRow['img']      = $this->get_image_path($feedRow['img']);
                $newfeedRow['username'] = self::userTextDecode($feedRow['username']);
                $newfeedRow['content']  = self::userTextDecode($feedRow['content']);
                $newfeedRow['created_at'] = date('Y-m-d H:i:s',$feedRow['created_at']);
                $data[] = $newfeedRow;
                unset($newfeedRow);
            }
        }
        // 取得列的列表
        $volume = [];
        foreach ($data as $key => $row)
        {
            $volume[$key]  = $row['created_at'];
        }

        array_multisort($volume, SORT_ASC,$data);
      
        return ['status' => '1','msg' => $data];
    }
    /**
     * 字符串过滤，过滤所有html代码
     * @param string $string
     * @return $string
     */
    STATIC function checkStr($string, $length = 0) {
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
     * [checkToken 验证TOKEN]
     * @param  [type] $token  [token]
     * @return [type]         [description]
     */
    public function checkToken($token){
        $token  = self::checkStr($token);
        if(!$token) return false;
        $userInfo   = '';
        $sql        = "SELECT U.id,U.username,U.img FROM yjy_login_token K 
                        LEFT JOIN  yjy_user U ON U.id = K.user_id
                        WHERE  K.token = '$token' LIMIT 1";

        if ($query = $this->conn->query($sql)) {
            $data       = mysqli_fetch_assoc($query);
            $userInfo   = $data ? $data : '';
        }
        return $userInfo;
    }
    /**
     * [userTextEncode 把用户输入的文本转义（主要针对特殊符号和emoji表情）]
     * @param  [type] $str [需要转换的字符]
     * @return [type]      [转换后的字符]
     */
    static function userTextEncode($str,$insert = '0'){
        if(!is_string($str))return $str;
        if(!$str || $str=='undefined')return $str;

        $text = json_encode($str); //暴露出unicode
        $text = preg_replace_callback("/(\\\u[ed][0-9a-f]{3})/i",function($str){
            return addslashes($str[0]);
        },$text); //将emoji的unicode留下，其他不动，这里的正则比原答案增加了d，因为我发现我很多emoji实际上是\ud开头的，反而暂时没发现有\ue开头。
        return $insert ? addslashes(json_decode($text)) : json_decode($text);
    }
    /**
     * [userTextDecode 反解码转义]
     * @param  [type] $str [转义后的字符]
     * @return [type]      [解析后的字符]
     */
    static function userTextDecode($str){
      
        $text = preg_replace_callback('/\\\\\\\\(u[ed][0-9a-f]{3})/i',function($str){
            return '\\' . $str[1];
        },$str); //将两条斜杠变成一条，其他不动
        $text = json_encode($text); //暴露出unicode
       
        $text = preg_replace_callback('/\\\\\\\\(u[ed][0-9a-f]{3})/i',function($str){
            return '\\' . $str[1];
        },$text); //将两条斜杠变成一条，其他不动
        return json_decode($text);
    }
    /**
     * 重新获得商品图片与商品相册的地址
     *
     * @param string $image 原商品相册图片地址
     * @param boolean $thumb 是否为缩略图
     *
     * @return string   $url
     */
    PUBLIC function get_image_path($image = '', $thumb = false , $width = 200, $height = 200)
    {
        $url = empty($image) ? '' : $this->config['uploadsUrl'].$image;
        $url = $thumb && $image ? $url.'@!'.$width.'x'.$height.'.jpg' : $url;
        return $url;
    }
    /**
     * [getUserToken 获取TOKEN]
     * @param  [type] $userid  [token]
     * @return [type]         [description]
     */
    public  function getUserToken($userId){
        if(!$userId) return false;

        $sql        = "SELECT token FROM yjy_login_token WHERE  user_id = '$userId'";
        if ($query = $this->conn->query($sql)) {
            $data   = mysqli_fetch_assoc($query);
            $token  = !empty($data) ? $data['token'] : '';
        }
        return $token ? $token : false;
    }
    /**
     * [linkToUrl 自动链接加A标签]
     * @param  [type] $str [description]
     * @return [type]      [description]
     */
    static public  function linkToUrl($str){
        $regex='/((http:\/\/|www\.|https:\/\/)(\w+|\.|\?|\=|\/|\-|\&|\:|\d+)+)/';
        $return = preg_replace_callback($regex,function($matches){
          if(!empty($matches[0]) && (strstr($matches[0],'http://')||strstr($matches[0],'https://'))){
                return '<a href="'.$matches[0].'" target="_blank">'.$matches[0].'</a>';
          }else{
                return '<a href="http://'.$matches[0].'" target="_blank">'.$matches[0].'</a>';
          }
        },$str);
        return $return;
    }
    /**
     * [strIsImg 正则匹配字符串是否是图片样式]
     * @param  [type] $str [description]
     * @return [type]      [description]
     */
    public  function strIsImg($str){
        if(!$str) return '';
        preg_match('/<img (.*?) src=\"(.+?)\".*?>/',$str,$match);
        if($match){
            return '图片';//$match['2']
        }else{
            return $str;
        }
    }
    /**
    * 推送个人或部分人接口
    * @para array  $param     参数
    * @para int    Alias      用户ID
    * @para str    option     替换文字
    * @para int    id         ID
    * @para string type       1为过期提醒，2为H5，3为文章,4为产品，5为问题 0为常规 
    * @return 操作成功，失败返回错误
    */
    // 极光推送。NoticeFunctions::JPushOne(['Alias'=>['94','92','283'],'option'=>'likes','id'=>'测试id','type'=>'1']);
    public  function JPushOne($param = [])
    {
        $production         =   $this->config['environment'] == 'Production' ? true : false;

        $param['Alias']     =   (string)$param['Alias'];
        $param['type']      =   (string)$param['type'];
        $param['id']        =   (string)$param['id'];
        $param['option']    =   (string)$param['option'];
        $param['relation']  =   isset($param['relation']) ? (string)$param['relation'] : '';

        //消息通知
        $content['feedback']       = '回复了你的反馈：xxx';//反馈被回复

        $token  = $this->getUserToken($param['Alias']);
        if(!$token){
            return "推送别名不存在";
        }

        if (empty($param) || !array_key_exists($param['option'], $content)) {
            return "推送内容不存在";
        }
        if (!empty($param['replaceStr'])) {
            $param['replaceStr']=   (string)$param['replaceStr'];
            $content[$param['option']]=str_replace('xxx', $param['replaceStr'], $content[$param['option']]);
        }
        
        try {
            $client = new \JPush\Client($this->config['Jpush']['app_key'], $this->config['Jpush']['master_secret']);
            $return = $client->push()
                ->setPlatform('all')
                //->addAllAudience()//推送所有用户
                ->addAlias($token)//推送别名
                // ->addTag($tag)
                //->setNotificationAlert($content[$param['option']])
                ->iosNotification($content[$param['option']], array(
                    'badge'=> '+1',//表示应用角标，把角标数字改为指定的数字；为 0 表示清除，支持 '+1','-1' 这样的字符串，表示在原有的 badge 基础上进行增减，默认填充为 '+1'
                    'content-available' => true,
                    'extras' => array(
                        'id' => $param['id'],
                        'type' => $param['type'],
                        'relation'  => $param['relation']
                    ),
                ))
                ->androidNotification($content[$param['option']], array(
                    'extras' => array(
                        'id' => $param['id'],
                        'type' => $param['type'],
                        'relation'  => $param['relation']
                    ),
                ))
                ->options(array(
                    //  'sendno' => 100,//推送序号
                    'time_to_live' => 86400,//离线消息保留时长
                    'apns_production' => $production,//是否生产环境
                    //'big_push_duration' => 100//定速推送时长（分钟）
                ))
                ->send();
                
        } catch (\JPush\Exceptions\APIConnectionException $e) {
            // try something here
            return $e;
        } catch (\JPush\Exceptions\APIRequestException $e) {
            //try something here
            return $e;
            
            //die($e);
        }
        return 'OK';
    }
}
// 启动服务器
$server = new Server();
