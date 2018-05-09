<?php
namespace frontend\models;
use Yii;
//抽奖活动公共
class DrawCom {
    private $huoDongdId     = '';       //活动ID
    private $startTime      = false;    //活动开始时间
    private $endTime        = false;    //活动结束时间
    private $maxTotal       = -1;       //总共最大抽奖次数，-1表示无限制
    private $maxOne         = -1;       //单日最大抽奖次数，-1表示无限制
    private $oneUse         = 0;        //单日已使用次数
    private $totalUse       = 0;        //总共已使用次数
    private $userId         = false;    //用户uid
    private $userName       = false;    //用户名username
    private $turnOn         = true;     //活动是否关闭
    private $timeFromConfig = true; 
    /**
     * 抽奖
     *
     * @access public
     * @param  array $config
     *  $config = array(
         'huodong'   =>  'chunjie',  //必需
         'drawCfg'   =>  array(0 => array('prize' => 10.1, 'rate' => 0.01, 'name' => '奖品名称')), //必需
         'startTime' =>  '2014-01-28', //可选
         'endTime'   =>  '2014-02-08', //可选
         'onemax'    =>  2,          //可选
         'totalmax'  =>  -1,         //可选
         'otherFunc' =>  null,       //可选
     );
     *  @param $giftNo   中奖编号
     *  @return 
            false   :   插入失败，当成没中奖
            true    :   插入成功, 通过$giftNo获取中奖编号
            -1      :   不在活动期间
            -2      :   用户没登陆
            -3      :   抽奖次数已用玩，通过getUse()获取已使用次数
            -99     :   游戏未指定
            -100    :   活动名称未指定
            -101    :   抽奖概率未设置
     */          
    public function panDraw($config, &$giftNo) {
        $huoDongdId =   empty($config['huoDongdId']) ? false : $config['huoDongdId'];
        $uid        =   empty($config['uid']) ? false : $config['uid'];
        $username   =   empty($config['username']) ? false : $config['username'];
        $drawCfg    =   empty($config['drawCfg']) ? false : $config['drawCfg'];
        $drawCfg    =   is_array($drawCfg) ? $drawCfg : false;

        $oneMax     =   isset($config['onemax']) ? intval($config['onemax']) : -1;
        $totalMax   =   isset($config['totalmax']) ? intval($config['totalmax']) : -1;
        $otherReason=   empty($config['otherReason']) ? true : $config['otherReason'];


        if($huoDongdId === false)  return -100;             //活动未指定
        if($drawCfg === false)  return -101;                //抽奖概率未设置
        if(empty($uid) || empty($username)) return -2;      //用户未登录
                    
        //配置
        $hasChance = $this->getSetting($config)->setHuoDong($huoDongdId)
                    ->setMax($totalMax, "total")->setMax($oneMax, "oneday")
                    ->setUserInfo($uid, $username)
                    ->hasChance($otherReason);
        if($hasChance !== true) return $hasChance; //没有抽奖机会,返回负整数: -1不在活动期间，-2没指定uid, -3抽奖次数没有了

        $rateCfg = false;
        if(is_array($drawCfg)) {
            foreach($drawCfg as $eK => $eV) {
                $rateCfg[$eK] = floatval($eV['rate']);
            }
        }
        if($rateCfg === false)  return -101; //抽奖概率未设置

        //抽奖
        $giftNo = $this->doDraw($rateCfg);
        // 奖品上限处理
        if (!empty($config['limPrize'])) {
            $limPrize = $config['limPrize'];
            // 如果抽奖结果是有上限的奖品，则进行上限检查处理
            if (array_key_exists($giftNo, $limPrize)) {
                $giftNo = $this->getNoAfterCheckLimPrize($limPrize, $giftNo);
            }
        }
        // 同一用户获取奖品上限处理
        if (!empty($config['limDraw'])) {
            $limDraw = $config['limDraw'];
            // 如果抽奖结果是有上限的同一奖品，则进行上限检查处理
            if (array_key_exists($giftNo, $limDraw)) {
                $giftNo = $this->getNoAfterCheckLimDraw($limDraw, $giftNo);
            }
        }
        $items = array(
            'prize'     =>  $drawCfg[$giftNo]['prize'],
            'giftid'    =>  $giftNo,
            'giftname'  =>  $drawCfg[$giftNo]['name']
        );
        
        if($this->insertDraw($items))   return true;
        else return false;
    }

    /**
     * 获取有限奖品完成校验后的奖品id
     * @param  [array] $limPrize
     * array(
     *      prizeNo => array(
     *           limitNum  => num,
     *           replaceNo => no
     *      )
     *  ); prizeNo为奖品Id，limitNum为奖品的上限数量，replaceNo替换后的奖品Id
     * @param  [int]   $prizeNo   [抽奖结果中的奖品Id]
     * @return [int]   $resultNo  [最终奖品Id]
     */
    function getNoAfterCheckLimPrize($limPrize, $prizeNo){
        $startTime  = $this->startTime;
        $endTime    = $this->endTime;
        $actType    = $this->huoDongdId;
        $limitNum   = $limPrize[$prizeNo]['limitNum'];
        $replaceNo  = $limPrize[$prizeNo]['replaceNo'];

        $sql        = "SELECT count(id) AS num FROM {{%huodong_special_draw}}  WHERE  hdid='$actType'
                    AND giftid='$prizeNo' AND addtime >= '$startTime' AND addtime <= '$endTime'";
        $useNum     = Yii::$app->db->createCommand($sql)->queryScalar();
        if ($useNum >= $limitNum) {
            // 达到上限后，强制更改奖品id
            $prizeNo = $replaceNo;
        }
        return $prizeNo;
    }
    /**
     * 获取有限奖品完成校验后的奖品id
     * @param  [array] $limDraw
     *       array(
     *           prizeNo => array(
     *           limitNum  => num,
     *           replaceNo => no
     *       )
     * ); prizeNo为奖品Id，limitNum为奖品的上限数量，replaceNo替换后的奖品Id
     * @param  [int]   $prizeNo   [抽奖结果中的奖品Id]
     * @return [int]   $resultNo  [最终奖品Id]
     */
    function getNoAfterCheckLimDraw($limPrize, $prizeNo){
        $uid       = $this->userId;
        $startTime = $this->startTime;
        $endTime   = $this->endTime;
        $actType   = $this->huoDongdId;
        $limitNum  = $limPrize[$prizeNo]['limitNum'];
        $replaceNo = $limPrize[$prizeNo]['replaceNo'];

        $sql = "SELECT count(id) AS num FROM {{%huodong_special_draw}} WHERE  hdid='$actType'
                AND giftid='$prizeNo' AND uid='$uid' AND addtime >= '$startTime' AND addtime <= '$endTime'";
        $useNum     = Yii::$app->db->createCommand($sql)->queryScalar();
        if ($useNum >= $limitNum) {
            // 达到上限后，强制更改奖品id
            $prizeNo = $replaceNo;
        }
        return $prizeNo;
    }
    //获取活动配置
    public function getSetting($config) {
        $startTime  =   empty($config['startTime']) ? false : $config['startTime'];
        $endTime    =   empty($config['endTime']) ? false : $config['endTime'];
        $turnOn     =   isset($config['turnOn']) ? $config['turnOn'] : true;
        $this->setTurnOn((bool)$turnOn);
        $this->setStartTime($startTime);
        $this->setEndTime($endTime);
        return $this;
    }

    //活动是否关闭
    public function setTurnOn($t = true) {
        $this->turnOn = $t;
        return $this;
    }

    //获取用户的中奖记录
    public function getDrawRecord() {
        $result = array('msg' => '提示', 'text' => null);
        $drawRecord = array();
        if(!empty($this->userId)) {
            //用户已登录
            $query      = "SELECT addtime, giftname FROM {{%huodong_special_draw}}
                        WHERE uid='" . $this->userId ."' AND hdi ='" . $this->huoDongdId . "' ORDER BY addtime DESC";
            $drawRecord = Yii::$app->db->createCommand($sql)->queryAll();
            if ($drawRecord) {
                $result['msg'] = '抽奖记录';
                foreach ($drawRecord as $item) {
                    $result['text'] .= $item['addtime'] . ' 您抽奖的结果是 “' . $item['giftname'] . '”';
                    $result['text'] .= "<br>";
                }
            }
            else {
                $result['text'] = '您暂时还没有抽奖记录 :)';
            }
        } else {
            $result['text'] = '您需要登录后才能查看记录哦 :(';
        }
        echo json_encode($result);
        return false;
    }

    //插入进行抽奖表
    public function insertDraw($otherItem = false) {
        if(!is_array($otherItem))   return false;
        $data = array_merge($otherItem, array(
                'uid'       =>  $this->userId,
                'username'  =>  $this->userName,
                'addtime'   =>  time(),
                'hdid'      =>  $this->huoDongdId,
                'ip'        =>  $_SERVER["REMOTE_ADDR"]
        ));
        return Yii::$app->db->createCommand()->insert('tms_huodong_special_draw', $data)->execute();
    }

    /**
    * 根据后台配置好的概率随机抽奖
    *
    * @param array $input array('a'=>0.5,'b'=>0.2,'c'=>0.4)
    * @param int $pow 小数点位数
    * @return array key
    */
    public function doDraw($input, $pow = 8) {
        $much = pow(10, $pow);
        $max  = array_sum($input) * $much;
        $rand = mt_rand(1, $max);
        $base = 0;
        foreach ($input as $k => $v) {
            $min = $base * $much + 1;
            $max = ($base + $v) * $much;
            if ($min <= $rand && $rand <= $max) {
                return $k;
            }
            else {
                $base += $v;
            }
        }
        return false;
    }

    //获取已使用的次数，0单日，1总共，2单日和总共
    public function getUse($type = 1) {
        $type = intval($type);
        if($type == 0)
            return $this->oneUse;
        else if($type == 2) 
            return array($this->oneUse, $this->totalUse);
        else
            return $this->totalUse;
    }
    
    //判断是否有抽奖机会, -1不在活动期间，-2没指定uid, -3抽奖机会没有了, true允许抽奖
    public function hasChance($otherCode = true) {
        if($this->turnOn == false)  return -5; //活动已关闭
        //if(!$this->isInTime())    return -1; //不在活动期间
        if($this->userId === false || $this->userName === false)    return -2; //没有指定uid或username 
        if($this->isOverMax())  return -3; //抽奖机会已用完
        return $otherCode;
    }

    //判断用户的抽奖次数是否已经超过了
    public function isOverMax() {
        $sql        =   "SELECT  DATE(addtime)  AS dateno FROM {{%huodong_special_draw}}
                        WHERE   hdid='" . $this->huoDongdId . "' AND uid='" . $this->userId . "'";
        $tmpData    =   Yii::$app->db->createCommand($sql)->queryAll();
        $oneNum     =   0;
        $maxNum     =   0;
        $today      =   date('Y-m-d');
        if(is_array($tmpData)) {
            foreach($tmpData as $eachD) {
                ++$maxNum;
                if($eachD['dateno'] == $today)  ++$oneNum;
            }
        }
    
        $this->oneUse = $oneNum;
        $this->totalUse = $maxNum;      

        if($this->maxOne != '-1' && $this->maxOne <= $oneNum)   return true;
        if($this->maxTotal != '-1' && $this->maxTotal <= $maxNum)   return true;
        return false;
    }

    //设置用户信息
    public function setUserInfo($uid, $username) {
        $this->userId   = $uid;
        $this->userName = $username;
        return $this;
    }

    //获取用户信息
    public function getUserInfo() {
        return array(
            'uid'       =>  $this->userId,
            'username'  =>  $this->userName
        );
    }

    //设置最大抽奖次数. $type值: oneday(单日), total(总共)
    public function setMax($c = 1, $type = "oneday") {
        $c = intval($c);
        $type = strtolower($type);
        $type = in_array($type, array("oneday", "total")) ? $type : "oneday";
        if($type == "total")    $this->maxTotal = $c;
        else    $this->maxOne = $c;
        return $this;
    }

    //判断当前时间是否在活动时间内
    public function isInTime() {
        //当前时间
        $now = time();

        //若设置了开始时间，且当前时间小于开始时间则返回false
        if($this->startTime != false && $this->startTime > $now) return false;
        //若设置了结束时间，且当前时间大于结束时间则返回false
        if($this->endTime != false && $this->endTime < $now) return false;
        return true;
    }

    public function setStartTime($t = false) {
        $this->startTime = $t;
        return $this;
    }

    public function setEndTime($t = false) {
        $this->endTime = $t;
        return $this;
    }

    public function setHuoDong($h) {
        $this->huoDongdId = $h;
        return $this;
    }

    public function setTimeFromConfig($t = true) {
        $this->timeFromConfig = $t;
        return $this;
    }
}
?>