<?php

namespace common\functions;
use Yii;
use common\functions\Functions;
use QL\QueryList;

class Tools{
  /**
   * [HtmlDate 时间工具]
   * @param [type] $date [解析后的时间]
   */
	public static function HtmlDate($date,$is_show=false){     
      $now = time();
      $time = '';
      if (is_numeric($date)) {
          $sub = $now - $date;   
          if ($sub < 60 && !$is_show) { //不足一分钟
              $time = intval($sub) . '秒前';
          } else if ($sub < 60*60 && !$is_show) { //不足一小时
              $time = intval($sub/60) . '分钟前';
          } else if ($sub < 86400) { //大于1小时小于24小时
              $time = intval($sub/3600) . '小时前';
              if($is_show) {
                $time =  date('Ymd', $date) == date('Ymd') ? "今天" : "昨天";
              }
          } else if ($sub < 86400 * 2) {  //大于24小时小于48小时
              $yesterday = strtotime(date('Y-m-d', time())) - 86400 + $sub;
              $time = '昨天';
          } else if ($sub < 86400 * 31) { //31天内
              $time = intval($sub/86400) . '天前';
          } else if ($sub < 86400 * 365) {  //12月内
              $time = abs(intval(date('m', time()) - date('m', $date))) . '个月前';
          } else if ($sub >= 86400 * 365) { //大于12月
              $time = abs(intval(date('y', time()) - date('y', $date))) . '年前';
          }
          if ($time < 0) { 
              $time = '';
          }
      }
      return $time;
	}
  /**
   * [HtmlDates 时间显示函数]
   * @param [type] $date [description]
   * @param string $type [description]
   */
  public static function HtmlDates($date,$type= '1'){
      $time = "";
      if (is_numeric($date)) {
          if($type == 1){
              $now = strtotime(date("Y-m-d"));
              $time = strtotime(date("Y-m-d",$date));
              $sub = $now - $time;

              if($sub == 0){
                  $time = '今天';
              }else if($sub <= 86400){
                  $time = '昨天';
              }else if($sub < 86400*2){
                  $time = '前天';
              }else if($sub >= 86400*2) {
                  $time = intval($sub / 86400) . '天前';
              }

          }elseif($type== 11){//后面添加，防止跟之前冲突所以11
              $now = strtotime(date("Y-m-d"));
              $time = strtotime(date("Y-m-d",$date));
              $sub = $now - $time;

              if($sub == 0){
                  $time = date("H:i",$date);
              }else{
                  $time = date("m-d",$date);
              }
          }else{
              //评论时间<60min，展示为xx分钟前；60min<评论时间<24h，展示为xx小时前；24h<评论时间<31day，展示为xx天前；评论时间>31day，展示为年-月-日）；

              $now = time();
              $sub = $now - $date;
              if ($sub < 2) {
                  $time = '刚刚';
              }else if ($sub < 60) { //不足一小时
                  $time = $sub . '秒前';
              }else if ($sub < 60*60) { //不足一小时
                  $time = intval($sub/60) . '分钟前';
              } else if ($sub < 86400) { //大于1小时小于24小时
                  $time = intval($sub/3600) . '小时前';
              } else if ($sub < 86400 * 31) { //31天内
                  $time = intval($sub/86400) . '天前';
              } else if ($sub > 86400 * 31) {  //12月内
                  $time = date('Y-m-d', $date);
              }

          }
      }
      return $time;
    }
  /**
   * [getFileNames 获取文件目录下子文件]
   * @param  [type] $dir [文件路径]
   * @return [type]      [返回文件数组]
   */
  static function getFileNames($dir){ 
      $fileFace = [];
     //检查是否为目录
      if(is_dir($dir)){
           
          //打开一个目录句柄
          if ($dh = opendir($dir)){
               
              //判断打开的目录句柄中的条目
              while (($file = readdir($dh)) !== false){
                   
                  //判断是否为目录，进入子目录读取
                  if((is_dir($dir."/".$file)) && $file!="." && $file!=".."){
                      //echo "<b><font color='red'>文件夹名：</font></b>",$file,"<hr>";
                      getFileNames($dir."/".$file."/");
                  }else{
                      if($file!="." && $file!=".."){
                            $fileFace[] = $file;
                      }
                  }
              }
              //关闭由 opendir() 函数打开的目录句柄。
              closedir($dh);
          }
      }
      return $fileFace;
   }
  /**
   * [getFaces 获取表情]
   * @return [type] [返回所有表情]
   */
    static function getFaces(){
        //获取表情数组
        $cache = Yii::$app->cache;
        $faces = $cache->get('faces');
        if(!$faces){
            $sql     = "SELECT phrase,url from {{%faces}}";
            $faceArr = Yii::$app->db->createCommand($sql)->queryAll(); 
            $faces   = [];
            foreach ($faceArr as $key => $value) {
                $faces[$value['phrase']] = $value['url'];
            }
            $cache->set('faces',$faces,3600);
        }
        return $faces;        
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
     * [userTextDecode 反解码转义]
     * @param  [type] $str [转义后的字符]
     * @return [type]      [解析后的字符]
     */
    static function statistics($uid,$type,$relation_id){
        $uid          = intval($uid);
        $type         = intval($type);
        $relationId   = intval($relation_id);

        $sql  = "SELECT * FROM {{%huodong_statistics_config}} WHERE type = '$type' AND relation_id = '$relationId'";

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
     * [getip 获取正式IP地址]
     * @return [type] [description]
     */
    static  function getip(){
        if(!empty($_SERVER["HTTP_CLIENT_IP"])) {
            $cip = $_SERVER["HTTP_CLIENT_IP"]; 
        }else if(
            !empty($_SERVER["HTTP_X_FORWARDED_FOR"])) { $cip = $_SERVER["HTTP_X_FORWARDED_FOR"]; 
        }else if(!empty($_SERVER["REMOTE_ADDR"])) {
          $cip = $_SERVER["REMOTE_ADDR"]; 
        }else $cip = "";
        preg_match("/[\d\.]{7,15}/", $cip, $cips);
        $cip = $cips[0] ? $cips[0] : 'unknown';
        unset($cips);
        return $cip;
    }
    /**
     * [keylink 替换关键字]
     * @param  [type]  $str     [内容]
     * @param  [type]  $linkMap [替换数据]
     * @param  integer $count   [替换次数]
     * @param  integer $total   [总替换次数]
     * @return [type]           [description]
     */
    static public function keylink($str,$count = '1' ,$total = '6'){
        $tmpKwds = [];
        $num     = 1;
        $count   = intval($count);
        $total   = intval($total);
        //先匹配替换掉产品
        $regex ="/<div class=\"border\".*?>.*?<\/a><\/div><\/div>/ism"; 
        if(preg_match_all($regex, $str, $matches)){ 
            foreach ($matches[0] as $key => $value) {
                $tmpKwd = '{'.md5($value).'}';
                $str    = str_replace($value, $tmpKwd, $str);
                $tmpKwds[$tmpKwd] = $value;
                unset($tmpKwd);
            } 
        }
        //再匹配关键字
        $sql     = "SELECT keyword,link FROM {{%article_keywords}}";
        $linkMap = Yii::$app->db->createCommand($sql)->queryAll(); 

        if(!$str || !$linkMap) return $str;
        shuffle($linkMap);
        foreach($linkMap as $row) {
            $str = preg_replace('/(<a.*?>\s*)('.$row['keyword'].')(\s*<\/a>)/sui', '${2}', $str);
        }

        foreach($linkMap as $i => $row) {
            $kwd = $row['keyword'];
            $url = $row['link'];

            for($j=$i+1; $j<count($linkMap); $j++) {
                $subKwd = $linkMap[$j]['keyword'];
                
                //如果包含其他关键字，暂时替换成其他字符串
                if(strpos($kwd, $subKwd) !== false) {
                    $tmpKwd = '{'.md5($subKwd).'}';
                    $kwd = str_replace($subKwd, $tmpKwd, $kwd);
                    $tmpKwds[$tmpKwd] = $subKwd;
                }
                unset($subKwd);
            }

            if(strpos($str, $kwd) !== false && $num <= $total) {
                //把文字替换成链接
                $str = preg_replace('/('.$kwd.')/sui', '<a href="'.$row['link'].'" target="_blank">'.$kwd.'</a>', $str, $count);
                $num ++;
            }
            unset($kwd,$url);
        }
        //把代替子关键字的字符串替换回来
        foreach($tmpKwds as $tmp=>$kwd) {
            $str = str_replace($tmp, $kwd, $str);
        }
        return $str;
    }
    /**
     * [getCalendar 当日详情]
     * @param  [type] $date [description]
     * @return [type]       [description]
     */
    static public function getCalendar($date = ''){
        $isWork       =   0;
        $headers      =   [];
        //判断是否是节假日
        $cache        = Yii::$app->cache;
        $calendarInfo = $cache->get('calendarInfo');

        if(!$calendarInfo || !isset($calendarInfo[$date])){
            $host         =   "http://jisuwnl.market.alicloudapi.com";
            $path         =   "/calendar/query";
            $method       =   "GET";
            $appcode      =   "53674c5a445846aea50ab8c6e81bbc3e";
            array_push($headers, "Authorization:APPCODE " . $appcode);
            $querys       =   ['date' => $date];
            $url          =   $host . $path;
            $json         =   Functions::http_judu($url,$querys,'GET',$headers);
            $arr          =   json_decode($json,1);
            //今天是否要上班,默认0上班
            if($arr['status'] == '0'){
                if(isset($arr['result']['workholiday'])){
                    $isWork = $arr['result']['workholiday'];
                }else{
                    $isWork =  in_array($arr['result']['week'],['六','日']) ? '1' : '0';
                }
            }
            $cache->set('calendarInfo',[$date => $isWork],86400);
        }else{
            $isWork = $calendarInfo[$date];
        }
        return $isWork;
    }
    /**
     * [getEssenceSet 精华设置]
     * @return [type] [description]
     */
    static public  function getEssenceSet(){
        $date         =   date('Y-m-d');
        $todayTime    =   strtotime($date);
        $nowTime      =   time();
        $difference   =   $nowTime - $todayTime;
        $isWork       =   0;
        $updateNum    =   0;
        //判断是否是节假日
        $isWork = self::getCalendar();
        //根据时间段判断
        $stageOneEnd    = 32400;
        $stageOneStart  = 82800;
        if($difference < $stageOneEnd || $difference > $stageOneStart){
            $updateNum = 3 ;
        }
        //节假日时间
        if($isWork){
          $stageTwoEnd    = 82800;
          $stageTwoStart  = 32400;
          if($difference < $stageTwoEnd && $difference > $stageTwoStart){
              $updateNum = 15 ;
          }
        }else{
          $stageTwoEnd    = 82800;
          $stageTwoStart  = 68400;
          if($difference < $stageTwoEnd && $difference > $stageTwoStart){
              $updateNum = 15 ;
          }
        }
        return $updateNum;
    }
    /**
     * [getEssenceSet 精华设置]
     * @return [type] [description]
     */
    static public  function getRandComment(){
        $idArr   = [];
        $huor    = date('H');
        $randNum = 15;//self::getEssenceSet();
        if(!$randNum) return false;

        $whereStr= " C.status = '1' AND C.is_digest = '1' AND C.type = '1' ";
        //需要就读取
        if($randNum){
            $cache        = Yii::$app->cache;
            $cacheName    = 'api_essence_'.$huor;
            $idArr        = $cache->get($cacheName);
            if(!$idArr){
                $sql    = "SELECT C.id FROM {{%comment}} C WHERE $whereStr  GROUP BY C.post_id ORDER BY RAND() LIMIT $randNum";
                $idArr  = Yii::$app->db->createCommand($sql)->queryColumn(); 
                $cache->set($cacheName,$idArr,3600);
            } 
        }
        return $idArr;
    }
    /**
     * [getProductComponent 获取产品名成份]
     * @param  [type] $name [description]
     * @return [type]       [description]
     */
    static public function getProductComponent($name){
        if(!$name)     return ['status' => '0','msg' => '产品名不能为空'];
        //先验证是否重复录入
        $selectSql      = "SELECT id FROM {{%product_details}} WHERE product_name = '" . addslashes($name) ."'";
        $productInfo    = Yii::$app->db->createCommand($selectSql)->queryOne();

        if($productInfo) return ['status' => '0','msg' => '产品存在'];
      
        $componentArr = [];
        $arr          = [];
        $return       = [];
        $idArr        = [];
        $errorArr     = [];
        $time         = time();
        //抓取cosdna的成份
        $newUrl       = 'http://www.cosdna.com/chs/product.php?q='.mb_substr(urlencode($name),0,100).'&s=3';
        $componentUrl = 'https://api.bevol.cn/static/multiple_search';
        $componentInfoUrl = 'https://api.bevol.cn/entity/info2/composition';
        $searchUrl    = 'https://api.bevol.cn/search/composition/index';

        //采集产品成分列表
        $newData = QueryList::run('Request',[
            'target'        =>  $newUrl,
            'referrer'      =>  'http://www.cosdna.com',
            'method'        =>  'GET',
            'timeout'       =>  '5'
        ])->setQuery(array(
            'url'     => array('.ProdTbl > tbody > tr > td > a','href','-p'),
            'name'    => array('.ProdTbl > tbody > tr > td > a','text','-p')
        ))->getData();

        if(empty($newData)) return ['status' => '0','msg' => '未找到【'.$name.'】'];
        //开始匹配
        $href       = '';
        $pregName = str_replace(' ','',$name);
        foreach ($newData as $key => $value) {
            $value['name'] = str_replace(' ','',$value['name']);
            if($pregName == $value['name'] || htmlspecialchars($pregName) == $value['name']){
              $href = $value['url'];
              break;
            }
        }
        if(empty($href)) return ['status' => '0','msg' => '未匹配到【'.$name.'】'];
        //开始采集成份列表
        $url    = 'http://www.cosdna.com/chs/'.$href;
        $componentList = QueryList::run('Request',[
            'target'        =>  $url,
            'referrer'      =>  'http://www.cosdna.com',
            'method'        =>  'GET',
            'timeout'       =>  '20'
        ])->setQuery(array(
            'component'   => array(".iStuffTable tr[valign='top'] > .iStuffETitle > a",'text','-p')
        ))->getData();

        //开始查询
        if($componentList){
            foreach ($componentList as $key => $value) {
                $info   = [];
                $componentInfoJson = Functions::http_judu($searchUrl,['keywords' => $value['component'],'p' => '1'],'get');
                $componentInfo     = json_decode($componentInfoJson,true);
                if(isset($componentInfo['data']['items']['0'])){
                    $info        = $componentInfo['data']['items']['0']; 
                }else{
                    $errorArr[]  = $value['component'];
                }
                // $info = isset($componentInfo['data']['items']['0']) ? $componentInfo['data']['items']['0'] : [] ; 
                $componentArr[] = $info;

                usleep(100);
            }
            //库中是否存在
            foreach ($componentArr as $k => $v) {
                 $title = isset($v['name']) ? addslashes($v['name']) :'';
                 if($title){
                    $sql      = "SELECT id,name FROM {{%product_component}} WHERE name = '$title' ORDER BY id";
                    $isExsit  = Yii::$app->db->createCommand($sql)->queryOne();

                    if(!$isExsit){
                        $componentData  = [
                            'name' => addslashes($v['name']),
                            'ename'=> $v['english'],
                            'cas'  => $v['cas'],
                            'alias'=> $v['other_title'],
                            'risk_grade'=> $v['safety'],
                            'is_active' => $v['active']   ? 1 : 0,
                            'is_pox'    => $v['acne_risk'] ? 1 : 0,
                            'component_action' => $v['usedtitle'],
                            'description' => $v['remark'],
                            'created_at'  => $time,
                        ];
                        $isTure = Yii::$app->db->createCommand()->insert('{{%product_component}}',$componentData)->execute();
                        $id     = $isTure ? Yii::$app->db->getLastInsertID() : '';
                        $compInfo = ['id' => $id, 'name' => $title];
                    }else{
                        $compInfo = ['id' => $isExsit['id'], 'name' => $title];
                    }
                    $return [] = $compInfo;
                    unset($id,$compInfo);
                 }
                 unset($title);
            }
        }
        return ['status' => 1 ,'msg' => $return,'error' => $errorArr];
      }
  }