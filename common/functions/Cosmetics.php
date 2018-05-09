<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\functions;
use Yii;

class Cosmetics {
    /**
     * [cosmeticsList 品牌列表]
     * @return [type] [品牌列表数据]
     */
    static function cosmeticsList(){
        $cache  = Yii::$app->cache;
        $cosmeticsList = $cache->get('tool_cosmetics_list'); 
        if(!$cosmeticsList) {
            $whereStr       = "status = '1' AND rule > '0'";
            $sql            = "SELECT id,name,letter,rule,ename FROM {{%brand}} WHERE $whereStr ORDER BY num DESC";
            $cosmeticsList  = Yii::$app->db->createCommand($sql)->queryAll();
            $cache->set('tool_cosmetics_list', $cosmeticsList, 300);
        }
        return $cosmeticsList;
    }
    /**
     * [品牌规则]
     * @return [type] []
     */
    static function brandRule($id){
        $id = intval($id);
        if(!$id) return false;
        $sql    = "SELECT rule FROM {{%brand}}  WHERE id = '$id'";
        $rule   = Yii::$app->db->createCommand($sql)->queryScalar();

        return $rule ? $rule : false;
    }
    /**
     * [appCosmeticsList 品牌列表]
     * @return [type] [品牌列表数据]
     */
    static function iosCosmeticsList(){
        $cache  = Yii::$app->cache;
        $cosmeticsList = $cache->get('ios_cosmetics_list'); 
        if(!$cosmeticsList) {
            $sql            = "SELECT id,name,letter,rule FROM {{%brand}}  ORDER BY num DESC";
            $list           = Yii::$app->db->createCommand($sql)->queryAll();
            //热门品牌
            $hotCosmetics   = [];
            $otherCosmetics = [];
            $newCosmetics   = [];
            $cosmeticsList  = [];
            $letterArr      = [];

            foreach ($list as $key => $value) {
                if($key > 2) break;
                $hotCosmetics[] = $value;
                unset($list[$key]);
            }
            foreach ($list as $k => $v) {
                if(!in_array($v['letter'],$letterArr)) { 
                    $letterArr[]                    = $v['letter'];
                    $otherCosmetics[$v['letter']]   = [];
                };
                $otherCosmetics[$v['letter']][] = $v;
            }
            //排序
            sort($letterArr);
            ksort($otherCosmetics);
            foreach ($otherCosmetics as $k => $v) {
                $newCosmetics[] = $v;
            }
            $cosmeticsList = ['letterArr' => $letterArr, 'hotCosmetics' => $hotCosmetics,'otherCosmetics' => $newCosmetics];

            $cache->set('ios_cosmetics_list', $cosmeticsList, 300);
        }
        return $cosmeticsList;
    }
    /**
     * [appCosmeticsList 品牌列表]
     * @return [type] [品牌列表数据]
     */
    static function iosCosmeticsList_v3($model = '0'){
        // $cache  = Yii::$app->cache;
        // $cosmeticsList = $cache->get('ios_cosmetics_list_v3'); 
        // if(!$cosmeticsList) {
            $whereStr = "status = '1'";
            $whereStr.= !$model  ? '' : " AND rule > '0' ";
            $sql      = "SELECT id,name,letter,rule,ename FROM {{%brand}} WHERE $whereStr ORDER BY num DESC";
            $list           = Yii::$app->db->createCommand($sql)->queryAll();
            //热门品牌
            $data           = [];
            $cosmeticsList  = [];
            $letterArr      = [];
            $newCosmetics   = [];

            foreach ($list as $k => $v) {
                if(!in_array($v['letter'],$letterArr)) { 
                    $letterArr[]                    = $v['letter'];
                    $cosmeticsList[$v['letter']]    = [];
                };

                $v['name'] =  $v['ename'] == $v['name'] ? $v['name'] : ($v['ename'] ? $v['ename'] .' '. $v['name'] : $v['name']);
                $cosmeticsList[$v['letter']][] = $v;
            }
            //排序
            sort($letterArr);
            ksort($cosmeticsList);
            foreach ($cosmeticsList as $k => $v) {
                $newCosmetics[] = $v;
            }
            $cosmeticsList = ['letterArr' => $letterArr, 'cosmeticsList' => $newCosmetics];
        //     $cache->set('ios_cosmetics_list_v3', $data, 300);
        // }
        return $cosmeticsList;
    }
    /**
     * [appCosmeticsList 品牌列表]
     * @return [type] [品牌列表数据]
     */
    static function androidCosmeticsList(){
        $cache  = Yii::$app->cache;
        $cosmeticsList = $cache->get('android_cosmetics_list'); 
        if(!$cosmeticsList) {
            $sql            = "SELECT id,name,letter,rule FROM {{%brand}}  ORDER BY num DESC";
            $list           = Yii::$app->db->createCommand($sql)->queryAll();
            //热门品牌
            $hotCosmetics   = [];
            $otherCosmetics = [];
            $newCosmetics   = [];
            $cosmeticsList  = [];
            $letterArr      = [];

            foreach ($list as $key => $value) {
                if($key > 2) break;
                $hotCosmetics[] = $value;
                unset($list[$key]);
            }
            foreach ($list as $k => $v) {
                if(!in_array($v['letter'],$letterArr)) { 
                    $letterArr[]                    = $v['letter'];
                    $otherCosmetics[$v['letter']]   = [];
                };
                $otherCosmetics[$v['letter']][] = $v;
            }
            //排序
            sort($letterArr);
            ksort($otherCosmetics);
            foreach ($otherCosmetics as $k => $Cosmetics) {
                foreach ($Cosmetics as $key1 => $val1) {
                   $newCosmetics[] = $val1;
                }
                
            }
            $cosmeticsList = ['letterArr' => $letterArr, 'hotCosmetics' => $hotCosmetics,'otherCosmetics' => $newCosmetics];

            $cache->set('android_cosmetics_list', $cosmeticsList, 300);
        }
        return $cosmeticsList;
    }
    /**
     * [appCosmeticsList 品牌列表]
     * @return [type] [品牌列表数据]
     */
    static function androidCosmeticsList_v3($model = '0'){
        // $cache  = Yii::$app->cache;
        // $cosmeticsList = $cache->get('android_cosmetics_list_v3'); 
        // if(!$cosmeticsList) {
            $whereStr = "status = '1'";
            $whereStr.= !$model  ? '' : " AND rule > '0' ";
            $sql      = "SELECT id,name,letter,rule,ename FROM {{%brand}} WHERE $whereStr ORDER BY num DESC";
            $list     = Yii::$app->db->createCommand($sql)->queryAll();
            //热门品牌
            $cosmeticsList  = [];
            $letterArr      = [];

            foreach ($list as $k => $v) {
                if(!in_array($v['letter'],$letterArr)) { 
                    $letterArr[]   = $v['letter'];
                };

                $v['name'] =  $v['ename'] == $v['name'] ? $v['name'] : ($v['ename'] ? $v['ename'] .' '. $v['name'] : $v['name']);
                $cosmeticsList[]   = $v;
            }
            //排序
            sort($letterArr);
            ksort($cosmeticsList);

            $cosmeticsList = ['letterArr' => $letterArr, 'cosmeticsList' => $cosmeticsList];

        //     $cache->set('android_cosmetics_list_v3', $cosmeticsList, 300);
        // }
        return $cosmeticsList;
    }
    
    //获取年份天数
    static function getYearDate($year){
        $rule = '/^(([0-9]{4})){1}$/';
        preg_match($rule,$year,$result);
        if ($result) {
            $yearDate  = (strtotime(($year+1).'-01-00') - strtotime($year.'-01-00'))/(24 * 3600);
            return $yearDate;
        }
        return false;
    }
    //规则1 
    /**
     * used 2017-12-5 蜜丝佛陀 Max Factor、安娜苏 Anna Sui、古驰 GUCCI、卡尔文克雷恩 Calvin Klein、EVE LOM、杜嘉班纳 Dolce & Gabbana、凡士林 vaseline、
     */
    static function rule1($number){
        $rule = '/^(([0-9]{1})([0-9]{3})){1}$/';//4位码         
        preg_match($rule,$number,$result); 
        if(!$result){
            if (strlen($number) != 4) {
                return $return = ['status' => -1, 'msg' => '批号长度有误'];
            } else {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
        }else{
            $year = $result['2'];
            $date = $result['3'];
            
            //天数不能为0
            if ($date < 1) {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
            
            $year    = $year + 2010;    
            if($year > date('Y')){
                $year = $year - 10;
            }
            $yearDate  = self::getYearDate($year);
            //不符合当年天数
            if ($date > $yearDate) {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }   
            $date_str = date('Ymd',strtotime($year.'-01-00') + 24 * 3600 * $date);
            if($date_str >date('Ymd') && $date_str-10000 <=date('Ymd')){
                $year  = $year - 10;
                
                //年份-10，再验证一遍
                $yearDate  = self::getYearDate($year);
                if ($date > $yearDate) {
                    return $return = ['status' => -1, 'msg' => '批号格式错误'];
                }
            }
            
            //计算生产日期
            $start_time   = strtotime($year.'-01-00') + 24 * 3600 * $date;
            $startDay     = date('Y年m月d日',$start_time);
            $end_time = strtotime('+3 year',$start_time);
            $endDay   = date('Y年m月d日',$end_time);
            
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }
    }
    //规则2
    static function rule2($number){
        $rule = '/^(([0-9]{1})([0-9]{3})[0-9|A-Z]{2}){1}$/';         
        preg_match($rule,$number,$result); 
        if(!$result){
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }else{
            $year = '201'.$result['2'].'-01-00';
            $date = intval($result['3']);

            //计算生产日期
            $start_time = strtotime($year) + 24 * 3600 * $date;
            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }
    }
    //规则3
    /**
     * 雅诗兰黛、海蓝之谜、倩碧、悦木之源、娥佩兰、罗拉玛斯亚、汤姆福特、魅可、祖马龙、芭比波朗、郎仕
     * used 2017-12-15 Prescriptives、艾凡达 AVEDA、迪梵 Darphin、唐可娜儿 DKNY
     */
    static function rule3($number){
        $rule = '/^([A-Z|0-9]{1}([1-9|A-C]{1})([0-9]{1})){1}$/';//3位码
        preg_match($rule,$number,$result); 

        if(!$result){
            if (strlen($number) != 3) {
                return $return = ['status' => -1, 'msg' => '批号长度有误'];
            } else {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
        }else{
            $year   = $result['3'];
            $month  = $result['2'];

            if(is_numeric($month)) {
                $month = $month;
            }else{
                switch ($month) {
                    case 'A':
                        $month = 10;
                        break;
                    case 'B':
                        $month = 11;
                        break;
                    case 'C':
                        $month = 12;
                        break;
                }
            }
            $month      = str_pad($month,2,'0',STR_PAD_LEFT);
            $year = $year + 2010;
            $date_str   = $year.'-'.$month.'-01';
            
            if($date_str >date('Ymd') && $date_str-10000 <=date('Ymd')){
                $year  = $year - 10;
            }

            $start_time = strtotime($year.'-'.$month);
            $end_time   = strtotime('+3 year',$start_time);
            $startDay   = $year.'年'.$month.'月'.'01日';
            $endDay     = date('Y年m月d日',$end_time);
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }
    }
    //规则4
    static function rule4($number){
        $rule = '/^(([A-Z]{1})([A-L]{1})[A-Z]{4}){1}$/';         
        preg_match($rule,$number,$result); 
        if(!$result){
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }else{
            $year   = $result['2'];
            $month  = $result['3'];

            $yearArr= array( 'Q', 'R', 'S', 'T', 'V', 'W', 'Y', 'Z','A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P');
            foreach ($yearArr as $key => $value) {
                if($year == $value) $year = $key + 2004;
            }
            $monArr= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L');
            foreach ($monArr as $k => $v) {
                if($month == $v) $month = $k + 1;
            }
            //计算生产日期
            $year       = str_pad($year,2,'0',STR_PAD_LEFT);
            $month      = str_pad($month,2,'0',STR_PAD_LEFT);

            $startDay   = $year.'-'.$month;
            $start_time = strtotime($startDay);
            $end_time   = strtotime('+3 year',$start_time);

            $startDay   = date('Y年m月',$start_time);
            $endDay     = date('Y年m月',$end_time);
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }
    }
    //规则5
    static function rule5($number){
        $rule = '/^(([0-9]{1})([A-Z]{1})[0-9|A-Z]{3}){1}$/';         
        preg_match($rule,$number,$result); 
        if(!$result){
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }else{
            $year   = $result['2'];
            $month  = $result['3'];

            //计算生产日期
            $mon1 = ['N' => 1 ,'P' => 2 ,'Q' => 3, 'R' => 4, 'S' => 5,'T' => 6, 'U' => 7, 'V' => 8, 'W' => 9, 'X' => 10, 'Y' => 11, 'Z' => 12]; 
            $mon2 = ['A' => 1 ,'B' => 2 ,'C' => 3, 'D' => 4, 'E' => 5,'F' => 6, 'G' => 7 ,'H' => 8, 'J' => 9, 'K' => 10, 'L' => 11, 'M' => 12 ];

            $exist1 = array_key_exists($month, $mon1);
            $exist2 = array_key_exists($month, $mon2);
            if(!$exist1 && !$exist2){
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
            if($exist1) $month = $mon1[$month];
            if($exist2) $month = $mon2[$month];

            $month      = str_pad($month,2,'0',STR_PAD_LEFT);
            $startDay   = '201'.$result['2'].'-'.$month;
            $start_time = strtotime($startDay);
            $end_time   = strtotime('+3 year',$start_time);

            $startDay   = '201'.$year.'年'.$month.'月';
            $endDay     = date('Y年m月',$end_time);
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }
    }
    //规则6
    static function rule6($number){
        $rule = '/^(([0-9]{1})[A-Z]{1}([0-9]{3})[A-Z]{1}){1}$/';         
        preg_match($rule,$number,$result); 
        if(!$result){
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }else{
            $year   = $result['2'];
            $day    = $result['3'];

            $year = '201'.$year.'-01-00';
            $date = intval($day);

            //计算生产日期
            $start_time = strtotime($year) + 24 * 3600 * $date;
            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }
    }
    //规则7
    static function rule7($number){
        $rule = '/^(([0-9]{2})(0?[[1-9]|1[0-2])([0-9]{2})){1}$/';         
        preg_match($rule,$number,$result); 

        if(!$result){
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }else{
            $year   = $result['4'];
            $month  = $result['3'];
            $day    = $result['2'];

            $year   = str_pad($year,2,'0',STR_PAD_LEFT);
            $month  = str_pad($month,2,'0',STR_PAD_LEFT);
            $day    = str_pad($day,2,'0',STR_PAD_LEFT);

            //计算生产日期
            $startDay   = '20'.$year.'-'.$month.'-'.$day;
            $start_time = strtotime($startDay);
            $end_time   = strtotime('+3 year',$start_time);
            $startDay   = '20'.$year.'年'.$month.'月'.$day.'日';
            $endDay     = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }
    }
    //规则8
    /**
     * used 2017-12-05 香蕉船 Banana Boat、必列斯 Bliss
     */
    static function rule8($number){
        $rule = '/^(([0-9]{2})([0-9]{3})[A-Z|0-9]{2,4}){1}$/';//9位码         
        preg_match($rule,$number,$result); 

        if(!$result){
            if (strlen($number) != 9) {
                return $return = ['status' => -1, 'msg' => '批号长度有误'];
            } else {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
        }else{
            $year   = $result['2'];
            $date    = $result['3'];
            
            //天数不能为0
            if ($date < 1) {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
            //年份大于27
            if ($year > 27) {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
            
            $year    = $year + 2000;    
            if($year > date('Y')){
                $year = $year - 10;
            }
            $yearDate  = self::getYearDate($year);
            //不符合当年天数
            if ($date > $yearDate) {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }   
            $date_str = date('Ymd',strtotime($year.'-01-00') + 24 * 3600 * $date);
            if($date_str >date('Ymd') && $date_str-10000 <=date('Ymd')){
                $year  = $year - 10;
                
                //年份-10，再验证一遍
                $yearDate  = self::getYearDate($year);
                if ($date > $yearDate) {
                    return $return = ['status' => -1, 'msg' => '批号格式错误'];
                }
            }
            
            //计算生产日期
            $start_time   = strtotime($year.'-01-00') + 24 * 3600 * $date;
            $startDay     = date('Y年m月d日',$start_time);
            $end_time = strtotime('+3 year',$start_time);
            $endDay   = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }
    }
    //规则9
    static function rule9($number){
        $rule = '/^(([0-9]{3})([0-9]{2})[0-9]{2}){1}$/';         
        preg_match($rule,$number,$result); 

        if(!$result){
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }else{
            $year   = $result['3'];
            $day    = $result['2'];

            $year   = str_pad($year,2,'0',STR_PAD_LEFT);
            $day    = str_pad($day,2,'0',STR_PAD_LEFT);

            //计算生产日期
            $year = '20'.$year.'-01-00';
            $start_time = strtotime($year) + 24 * 3600 * $day;
            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }
    }
    //规则10
    static function rule10($number){
        $rule1 = '/^([A-Z]{1}([A-H|J-N|P-Z]{1})([0-9]{3})){1}$/';
        $rule2 = '/^([A-Z|0-9]{2}([A-H|J-N|P-Z]{1})([O|N|D|1-9]{1})[A-Z|0-9]{2}){1}$/'; 

        preg_match($rule1,$number,$result1); 
        preg_match($rule2,$number,$result2);

        if($result1){
            $year   = $result1['2'];
            $day    = $result1['3'];
            $yearArr= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
            foreach ($yearArr as $key => $value) {
                if($year == $value) $year = $key + 2004;
            }
            //计算生产日期
            $year = $year.'-01-00';
            $start_time = strtotime($year) + 24 * 3600 * $day;
            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }elseif($result2){
            $year   = $result2['2'];
            $day    = $result2['3'];
            $yearArr= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
            foreach ($yearArr as $key => $value) {
                if($year == $value) $year = $key + 2004;
            }
            switch ($day) {
                case '<= 9':
                    $day = $day;
                    break;
                case 'O':
                    $day = 10;
                    break;
                case 'N':
                    $day = 11;
                    break;
                case 'D':
                    $day = 12;
                    break;
            }
            $year       = str_pad($year,2,'0',STR_PAD_LEFT);
            $month      = str_pad($day,2,'0',STR_PAD_LEFT);
            $startDay   = $year.'-'.$month;
            $start_time = strtotime($startDay);
            $end_time   = strtotime('+3 year',$start_time);

            $startDay   = $year.'年'.$month.'月';
            $endDay     = date('Y年m月',$end_time);
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }
    }
    //规则11
    static function rule11($number){
        $rule = '/^(([M-Z]{1})([A-Z]{1})([0-9]{2})){1}$/';         
        preg_match($rule,$number,$result); 

        if(!$result){
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }else{
            $year   = $result['2'];
            $month  = $result['3'];
            $day    = $result['4'];
            $yearArr    = array('M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
            $monthArr   = array('J','F','M','A','Y','E','L','U','S','O','N','D');
            foreach ($yearArr as $key => $value) {
                if($year == $value) $year = $key + 2004;
            }

            foreach ($monthArr as $k => $v) {
                if($month == $v) $month = $k + 1;
            }

            $year   = str_pad($year,2,'0',STR_PAD_LEFT);
            $month  = str_pad($month,2,'0',STR_PAD_LEFT);
            $day    = str_pad($day,2,'0',STR_PAD_LEFT);

            //计算生产日期
            $start_time = strtotime($year.'-'.$month.'-'.$day);
            $end_time   = strtotime('+3 year',$start_time);
            $startDay   = date('Y年m月d日',$start_time);
            $endDay     = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }
    }
    //规则12
    /**
     * used 11-23 香奈儿Chanel
     */
    static function rule12($number){  
        $rule1 = '/^(([0-9]{2})[0-9]{2}){1}$/';
        $rule2 = '/^(([6-7]{1})([0-9]{3})[0-9]{2}){1}$/'; 

        preg_match($rule1,$number,$result1); 
        preg_match($rule2,$number,$result2);

        if($result1){
            $num   = $result1['2'];
            $year  = 0;
            $month = 0;

            if($num < 37){
                $year   = 2016;
                $month  = $num - 1; 
            }else{
                $year   = 2011;
                $month  = $num - 37; 
            }
            $month  = str_pad($month,2,'0',STR_PAD_LEFT);

            //计算生产日期
            $stime      = strtotime($year.'-01');
            $start_time = strtotime('+'.$month.' month',$stime);
            $end_time   = strtotime('+3 year',$start_time);
            $startDay   = date('Y年m月',$start_time);
            $endDay     = date('Y年m月',$end_time);
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }elseif($result2){
            $year   = $result2['2'] + 2010;
            $day    = $result2['3'];
            //计算生产日期
            $year = $year.'-01-00';
            $start_time = strtotime($year) + 24 * 3600 * $day;
            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }
    }
    //规则13
    static function rule13($number){
        $rule = '/^(([0-9]{1})(0?[[1-9]|1[0-2])[0-9]{3}){1}$/';         
        preg_match($rule,$number,$result); 

        if(!$result){
            $rule2 = '/^([0-9|A-Z]{7}){1}$/';         
            preg_match($rule2,$number,$result2);

            if($result2){
                return $return = ['status' => -1, 'msg' => '七位码无任何意义'];
            }
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }else{
            $year    = $result['2'];
            $month   = $result['3'];
            $month   = str_pad($month,2,'0',STR_PAD_LEFT);

            $year    = $year + 2010; 

            //计算生产日期
            $start_time = strtotime($year.'-'.$month);
            $end_time   = strtotime('+3 year',$start_time);
            $startDay   = date('Y年m月',$start_time);
            $endDay     = date('Y年m月',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }
    }
    //规则14
    static function rule14($number){
        $rule = '/^(([0-9]{2})(0?[[1-9]|1[0-2])([0-9]{2})([0-9]{2})[A-Z|0-9]{3}){1}$/';         
        preg_match($rule,$number,$result); 

        if(!$result){
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }else{
            $year   = $result['2'];
            $month  = $result['3'];
            $day    = $result['4'];

            $year   = $year + 2000;

            $month  = str_pad($month,2,'0',STR_PAD_LEFT);
            $day    = str_pad($day,2,'0',STR_PAD_LEFT);

            //计算生产日期
            $start_time = strtotime($year.'-'.$month.'-'.$day);
            $end_time   = strtotime('+3 year',$start_time);
            $startDay   = date('Y年m月d日',$start_time);
            $endDay     = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }
    }
    //规则15
    static function rule15($number){
        $rule = '/^([A-Z]{3}(0?[[1-9]|1[0-2])([0-9]{2})){1}$/';         
        preg_match($rule,$number,$result); 

        if(!$result){
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }else{
            $year   = $result['3'];
            $month  = $result['2'];

            $year   = $year + 2000;

            $month  = str_pad($month,2,'0',STR_PAD_LEFT);

            //计算生产日期
            $start_time = strtotime($year.'-'.$month);
            $end_time   = strtotime('-3 year',$start_time);
            $startDay   = date('Y年m月',$end_time);
            $endDay     = date('Y年m月',$start_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }
    }
    //规则16
    static function rule16($number){
        $rule = '/^(([0-9]{2})[A-Z|0-9]{4}){1}$/';         
        preg_match($rule,$number,$result); 

        if(!$result){
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }else{
            $year   = $result['2'];
            $year   = $year + 2002;

            //计算生产日期
            $start_time = strtotime($year.'-01-01');
            $end_time   = strtotime('+3 year',$start_time);
            $startDay   = date('Y年',$start_time);
            $endDay     = date('Y年',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }
    }
    //规则17
    static function rule17($number){
        $rule = '/^(([0-9]{4})(0?[[1-9]|1[0-2])([0-9]{2})){1}$/';         
        preg_match($rule,$number,$result); 

        if(!$result){
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }else{
            $year   = $result['2'];
            $month  = $result['3'];
            $day    = $result['4'];
            $month  = str_pad($month,2,'0',STR_PAD_LEFT);
            $day    = str_pad($day,2,'0',STR_PAD_LEFT);
            //计算生产日期
            $start_time = strtotime($year.'-'.$month.'-'.$day);
            $end_time   = strtotime('+3 year',$start_time);
            $startDay   = date('Y年m月d日',$start_time);
            $endDay     = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }
    }
    //规则18
    static function rule18($number){
        $rule = '/^(([0-9]{1})([N|P-Z]{1})([0-9]{2})){1}$/';         
        preg_match($rule,$number,$result); 

        if(!$result){
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }else{
            $year   = $result['2'] + 2010;
            $month  = $result['3'];
            $monthArr   = array('N','P','Q','R','S','T','U','V','W','X','Y','Z');
            foreach ($monthArr as $key => $value) {
                if($month == $value) $month = $key + 1;
            }
            $day    = $result['4'];
            $month  = str_pad($month,2,'0',STR_PAD_LEFT);
            $day    = str_pad($day,2,'0',STR_PAD_LEFT);
            //计算生产日期
            $start_time = strtotime($year.'-'.$month.'-'.$day);
            $end_time   = strtotime('+3 year',$start_time);
            $startDay   = date('Y年m月d日',$start_time);
            $endDay     = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }
    }
    //规则19
    static function rule19($number){
        $rule1      = '/^(([0-9]{4})\/(0?[[1-9]|1[0-2])){1}$/';
        $rule2      = '/^(([0-9]{1})([0-9]{3})[A-Z|0-9]{3}){1}$/'; 

        preg_match($rule1,$number,$result1); 
        preg_match($rule2,$number,$result2);

        if($result1){
            $year   = $result1['2'];
            $month  = $result1['3'];

            $month  = str_pad($month,2,'0',STR_PAD_LEFT);
            //计算生产日期
            $year = $year.'-'.$month;
            $start_time = strtotime($year);
            $startDay   = date('Y年m月',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }elseif($result2){
            $year   = $result2['2'];
            $day    = $result2['3'];

            $year = '201'.$year.'-01-00';
            $start_time = strtotime($year) + 24 * 3600 * $day;
            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }
    }
    //规则20
    static function rule20($number){
        $rule    = '/^(([0-9]{1})([A-D|F|H|J-N|P|S]{1})[0-9]{2}){1}$/';

        preg_match($rule,$number,$result); 

        if(!$result){
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }else{
            $year   = $result['2'] + 2010;
            $month  = $result['3'];
            $monthArr   = array('A','B','C','D','F','H','J','K','L','N','P','S');
            foreach ($monthArr as $key => $value) {
                if($month == $value) $month = $key + 1;
            }

            $month  = str_pad($month,2,'0',STR_PAD_LEFT);
            //计算生产日期
            $start_time = strtotime($year.'-'.$month);
            $end_time   = strtotime('+3 year',$start_time);
            $startDay   = date('Y年m月',$start_time);
            $endDay     = date('Y年m月',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }
    }
    //规则21
    /**
     * used 2017-12-07 娇兰 Guerlain
     */
    static function rule21($number){
        $rule = '/^(([0-9]{1})([N|P-Z]{1})[0-9]{2}){1}$/'; //4位码        
        preg_match($rule,$number,$result); 

        if(!$result){
            if (strlen($number) != 4) {
                return $return = ['status' => -1, 'msg' => '批号长度有误'];
            } else {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
        }else{
            $year   = $result['2'] + 2010;
            $month  = $result['3'];
            $monthArr   = array('N','P','Q','R','S','T','U','V','W','X','Y','Z');
            foreach ($monthArr as $key => $value) {
                if($month == $value) $month = $key + 1;
            }

            $month  = str_pad($month,2,'0',STR_PAD_LEFT);
            
            $date_str = $year.$month.'01';
            if($date_str >date('Ymd') && $date_str-10000 <=date('Ymd')){
                $year  = $year - 10;
            }
            
            //计算生产日期
            $start_time = strtotime($year.'-'.$month);
            $end_time   = strtotime('+3 year',$start_time);
            $startDay   = date('Y年m月d日',$start_time);
            $endDay     = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }
    }
    //规则22
    /**
     * used 2017-12-06 水芝澳H2O、范思哲 Versace
     */
    static function rule22($number){
        $rule = '/^(([0-9]{1})([0-9]{3})[0-9|A-Z]{3}){1}$/';//7位码         
        preg_match($rule,$number,$result); 

        if(!$result){
            if (strlen($number) != 7) {
                return $return = ['status' => -1, 'msg' => '批号长度有误'];
            } else {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
        }else{
            $year   = $result['2'];
            $date    = $result['3'];
            
            //天数不能为0
            if ($date < 1) {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }

            $year   = $year + 2010;
            if($year > date('Y')){
                $year = $year - 10;
            }
            $yearDate  = self::getYearDate($year);
            //不符合当年天数
            if ($date > $yearDate) {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
            $date_str = date('Ymd',strtotime($year.'-01-00') + 24 * 3600 * $date);
            if($date_str >date('Ymd') && $date_str-10000 <=date('Ymd')){
                $year  = $year - 10;
            }
            //年份可能-10，再验证一遍
            $yearDate  = self::getYearDate($year);
            if ($date > $yearDate) {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
            
            //计算生产日期
            $start_time   = strtotime($year.'-01-00') + 24 * 3600 * $date;
            $startDay     = date('Y年m月d日',$start_time);
            $end_time = strtotime('+3 year',$start_time);
            $endDay   = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }
    }
    //规则23
    static function rule23($number){
        $rule1      = '/^([A-Z]{1}([A-H|J-N|P-Z]{1})([0-9]{3})){1}$/';
        $rule2      = '/^([A-Z|0-9]{2}([A-H|J-N|P-Z]{1})([1-9|O|N|D]{1})[A-Z|0-9]{2}){1}$/'; 

        preg_match($rule1,$number,$result1); 
        preg_match($rule2,$number,$result2);

        if($result1){
            $year   = $result1['2'];
            $day    = $result1['3'];

            $yearArr= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N',  'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
            foreach ($yearArr as $key => $value) {
                if($year == $value) $year = $key + 2004;
            }

            //计算生产日期
            $year = $year.'-01-00';
            $start_time = strtotime($year) + 24 * 3600 * $day;
            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }elseif($result2){

            $year   = $result2['2'];
            $day    = $result2['3'];

            $yearArr= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
            foreach ($yearArr as $key => $value) {
                if($year == $value) $year = $key + 2004;
            }

            switch ($day) {
                case '<= 9':
                    $day = $day;
                    break;
                case 'O':
                    $day = 10;
                    break;
                case 'N':
                    $day = 11;
                    break;
                case 'D':
                    $day = 12;
                    break;
            }

            $year = $year.'-'.$day;
            $start_time = strtotime($year);
            $startDay   = date('Y年m月',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月',$end_time);
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }
    }
    //规则24
    static function rule24($number){
        $rule = '/^(([0-9]{1})[A-Z]{2}){1}$/';         
        preg_match($rule,$number,$result); 

        if(!$result){
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }else{
            $year   = $result['2'];

            $year   = $year + 2010;

            //计算生产日期
            $year = $year.'-01';
            $start_time = strtotime($year);
            $startDay   = date('Y年',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }
    }
    //规则25
    static function rule25($number){
        $rule = '/^(([0-9]{2})([0-9]{3})){1}$/';         
        preg_match($rule,$number,$result); 

        if(!$result){
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }else{
            $year   = $result['2'];

            $day    = $result['3'];

            //计算生产日期
            $year = '20'.$year.'-01-00';
            $start_time = strtotime($year) + 24 * 3600 * $day;
            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }
    }
    //规则26
    /**
     * used 2017-12-07 科颜氏 Kiehl\'s、 阿玛尼 Giorgio Armani、卡诗 Kerastase、芳珂 FANCL
     */
    static function rule26($number){
        $rule = '/^([0-9|A-Z]{2}([A-H|J-N|P-Z]{1})([0-9|O|N|D]{1})[0-9|A-Z]{2}){1}$/';//6位码       
        preg_match($rule,$number,$result); 

        if(!$result){
            if (strlen($number) != 6) {
                return $return = ['status' => -1, 'msg' => '批号长度有误'];
            } else {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
        }else{
            $year   = $result['2'];
            $month    = $result['3'];

            $yearArr= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
            foreach ($yearArr as $key => $value) {
                if($year == $value) $year = $key + 2004;
            }

            switch ($month) {
                case '>= 1':
                    $month = '0'.$month;
                    break;
                case 'O':
                    $month = 10;
                    break;
                case '0':
                    $month = 10;
                    break;
                case 'N':
                    $month = 11;
                    break;
                case 'D':
                    $month = 12;
                    break;
            }
            
            $date_str = $year.$month.'01';
            if($date_str >date('Ymd') && $date_str-10000 <=date('Ymd')){
                $year  = $year - 10;
            }

            //计算生产日期
            $start_time = strtotime($year.'-'.$month);
            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }
    }
    //规则27
    static function rule27($number){
        $rule1      = '/^(([1-9|O|N|D]{1})([0-9]{1})[0-9|A-Z]{2}){1}$/';
        $rule2      = '/^(([0-9]{4})(0?[[1-9]|1[0-2])){1}$/'; 
        $rule3      = '/^(([0-9]{4})(0?[[1-9]|1[0-2])[A-Z|0-9]{6}){1}$/'; 
        preg_match($rule1,$number,$result1); 
        preg_match($rule2,$number,$result2);
        preg_match($rule3,$number,$result3);

        if($result1){
            $year   = $result1['3'];
            $day    = $result1['2'];

            $year = $year + 2010;

            switch ($day) {
                case '<= 9':
                    $day = $day;
                    break;
                case 'O':
                    $day = 10;
                    break;
                case 'N':
                    $day = 11;
                    break;
                case 'D':
                    $day = 12;
                    break;
            }

            //计算生产日期
            $year = $year.'-'.$day;
            $start_time = strtotime($year);
            $startDay   = date('Y年m月',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }elseif($result2){

            $year   = $result2['2'];
            $day    = $result2['3'];

            $day    = str_pad($day,2,'0',STR_PAD_LEFT);

            $year   = $year.'-'.$day;
            $start_time = strtotime($year);
            $startDay   = date('Y年m月',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月',$end_time);
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }elseif($result3){
            $year   = $result3['2'];
            $day    = $result3['3'];

            $day    = str_pad($day,2,'0',STR_PAD_LEFT);

            $year   = $year.'-'.$day;
            $start_time = strtotime($year);
            $startDay   = date('Y年m月',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月',$end_time);
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay]; 
        }else{
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }
    }
    //规则28
    static function rule28($number){
        $rule1      = '/^([0-9]{2}([A-H|J-N|P-Z]{1})([1-9|O|N|D]{1})[0-9]{2}){1}$/';
        $rule2      = '/^(\+([A-H|J-N|P-Z]{1})([1-9|O|N|D]{1})[0-9]{2}){1}$/'; 
        $rule3      = '/^(\+([A-H|J-N|P-Z]{1})([0-9]{3})[A-Z]{1}){1}$/'; 

        preg_match($rule1,$number,$result1); 
        preg_match($rule2,$number,$result2);
        preg_match($rule3,$number,$result3);

        if($result1){
            $year   = $result1['2'];
            $day    = $result1['3'];

            $yearArr= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
            foreach ($yearArr as $key => $value) {
                if($year == $value) $year = $key + 2004;
            }

            switch ($day) {
                case '<= 9':
                    $day = $day;
                    break;
                case 'O':
                    $day = 10;
                    break;
                case 'N':
                    $day = 11;
                    break;
                case 'D':
                    $day = 12;
                    break;
            }
            $day    = str_pad($day,2,'0',STR_PAD_LEFT);

            $year = $year.'-'.$day;
            $start_time = strtotime($year);


            //计算生产日期
            $year = $year.'-'.$day;
            $start_time = strtotime($year);
            $startDay   = date('Y年m月',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }elseif($result2){

            $year   = $result2['2'];
            $day    = $result2['3'];

            $yearArr= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N',  'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
            foreach ($yearArr as $key => $value) {
                if($year == $value) $year = $key + 2004;
            }

            switch ($day) {
                case '<= 9':
                    $day = $day;
                    break;
                case 'O':
                    $day = 10;
                    break;
                case 'N':
                    $day = 11;
                    break;
                case 'D':
                    $day = 12;
                    break;
            }
            $day    = str_pad($day,2,'0',STR_PAD_LEFT);

            $year   = $year.'-'.$day;
            $start_time = strtotime($year);
            $startDay   = date('Y年m月',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月',$end_time);
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }elseif($result3){
            $year   = $result3['2'];
            $day    = $result3['3'];

            $yearArr= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
            foreach ($yearArr as $key => $value) {
                if($year == $value) $year = $key + 2004;
            }
            $year = $year.'-01-00';
            $start_time = strtotime($year) + 24 * 3600 * $day;
            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }
    }
    //规则29
    /**
     * used 11-30 理肤泉（La Roche-Posay）/欧莱雅L'Oreal/卡尼尔
     */
    static function rule29($number){
        $rule = '/^([0-9|A-Z]{2}([A-H|J-N|P]{1})([0-9|O|N|D]{1})[0-9|A-Z]{2}){1}$/';//6位码
        preg_match($rule,$number,$result);
    
        if(!$result){
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }else{
            $month     = $result['3'];
            $year      = $result['2'];

            $yearArr= array('A'=>'4','B'=>'5', 'C'=>'6', 'D'=>'7', 'E'=>'8', 'F'=>'9',  'G'=>'10', 'H'=>'11','J'=>'12','K'=>'13','L'=>'14','M'=>'15','N'=>'16','P'=>'17');
            $is_year = isset($yearArr[$year]) ? true : false;
            if(!$is_year) return $return = ['status' => -1, 'msg' => '批号格式错误'];

            $year  = $yearArr[$year];    
            $year    = $year + 2000;    
            
            $monthArr= array('0'=>'10','O'=>'10','N'=>'11', 'D'=>'12');
            $is_month = isset($monthArr[$month]) ? true : false;
            if(!$is_month) {
                if ($month >= 1 && $month <= 9) {
                    $month = $month;
                } else {
                    return $return = ['status' => -1, 'msg' => '批号格式错误'];
                }
            } else {
                $month  = $monthArr[$month];
            }

            //计算生产日期    
            $start_time     = strtotime($year.'-'.$month);
            $startDay       = date('Y年m月d日',$start_time);
            $end_time       = strtotime('+3 year',$start_time);
            $endDay         = date('Y年m月d日',$end_time);
    
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }
    }
    //规则30
    /**
     * used 12-06 莱珀妮La prairie
     */
    static function rule30($number){
        $rule = '/^(([0-9]{1})([0-9]{2})[0-9|A-Z]{5}){1}$/';//8位码
        $rule2 = '/^([0-9|A-Z]{1}([1-9|A-C]{1})([0-9]{1})){1}$/';//3位码
        
        preg_match($rule,$number,$result);
        preg_match($rule2,$number,$result2);
    
        if($result){
            $year      = $result['2'];
            $date      = $result['3']*7+1;
            
            //天数不能为0
            if ($date < 1) {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
            
            $year = $year+2010;
            if($year > date('Y')) $year = $year - 10;
            $yearDate  = self::getYearDate($year);
            //不符合当年天数
            if ($date > $yearDate) {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
            $date_str = date('Ymd',strtotime($year.'-01-00') + 24 * 3600 * $date);
            if($date_str >date('Ymd') && $date_str-10000 <=date('Ymd')){
                $year  = $year - 10;
            
                //年份-10，再验证一遍
                $yearDate  = self::getYearDate($year);
                if ($date > $yearDate) {
                    return $return = ['status' => -1, 'msg' => '批号格式错误'];
                }
            }
            
            //计算生产日期
            $start_time   = strtotime($year.'-01-00') + 24 * 3600 * $date;
            $startDay     = date('Y年m月d日',$start_time);
            $end_time = strtotime('+3 year',$start_time);
            $endDay   = date('Y年m月d日',$end_time);
            
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        } elseif ($result2) {
            $year      = $result2['3'];
            $month     = $result2['2'];
            
            if ($year < 3) {
                $year = $year + 2010;
            } else {
                $year = $year + 2000;
            }
            
            $monArr= array('1'=>'01','2'=>'02', '3'=>'03', '4'=>'04', '5'=>'05', '6'=>'06',  '7'=>'07', '8'=>'08','9'=>'09','A'=>'10','B'=>'11','C'=>'12');
            $is_month = isset($monArr[$month]) ? true : false;
            if(!$is_month) return $return = ['status' => -1, 'msg' => '批号格式错误'];
            
            $month  = $monArr[$month];
            
            $date_str = $year.$month.'01';
            if($date_str >date('Ymd') && $date_str-10000 <=date('Ymd')){
                $year  = $year - 10;
            }
            
            //计算生产日期
            $startDay = $year.'年'.$month.'月'.'01日';
            $endDay = ($year+3).'年'.$month.'月'.'01日';
            
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];           
        } else {
            $ruleArr = ['8','3'];
            if (!in_array(strlen($number), $ruleArr)) {
                return $return = ['status' => -1, 'msg' => '批号长度有误'];
            } else {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
        }
    }
    //规则31
    static function rule31($number){
        $rule = '/^(([0-9]{2})(0?[[1-9]|1[0-2])([0-9]{2})[0-9]{1}){1}$/';         
        preg_match($rule,$number,$result); 

        if(!$result){
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }else{
            $year   = $result['2'] + 2000;
            $month  = $result['3'];
            $day    = $result['4'];
            $month  = str_pad($month,2,'0',STR_PAD_LEFT);
            $day    = str_pad($day,2,'0',STR_PAD_LEFT);
            //计算生产日期
            $year = $year.'-'.$month.'-'.$day;
            $start_time = strtotime($year);
            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }
    }
    //规则32
    static function rule32($number){
        $rule = '/^(([0-9]{1})[0-9|A-Z]{3}){1}$/';         
        preg_match($rule,$number,$result); 

        if(!$result){
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }else{
            $year   = $result['2'] + 2010;

            if($year > date('Y')){
                $year = $year - 10;
            }
            //计算生产日期
            $year = $year.'-01';
            $start_time = strtotime($year);
            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }
    }
    //规则33
    static function rule33($number){
        $rule1 = '/^(([A-Z]{1})([A-Z]{1})[0-9|A-Z]{2}){1}$/'; //四位码
        $rule2 = '/^([0-9|A-Z]{1}([A-Z]{1})([A-Z]{1})[0-9|A-Z]{2}){1}$/'; //五位码
        $rule3 = '/^(([0-9]{1})([0-9]{3})[A-Z]{2}){1}$/'; //六位码
        preg_match($rule1,$number,$result1);     
        preg_match($rule2,$number,$result2); 
        preg_match($rule3,$number,$result3);
        if($result1 || $result2){
            if($result1) $res = $result1;
            if($result2) $res = $result2;
            $month  = $res['2'];
            $year   = $res['3'];
            $yearArr= array('J'=>2005,'L'=>2006, 'N'=>2007, 'P'=>2008, 'R'=>2009, 'T'=>2010,  'V'=>2011, 'X'=>2012, 'Z'=>2013,'A'=>2014);
            $monthArr= array('T'=>'01','V'=>'02', 'X'=>'03', 'B'=>'04', 'D'=>'05', 'F'=>'06',  'H'=>'07', 'J'=>'08', 'L'=>'09','N'=>'10','P'=>'11','R'=>'12');
            $is_year = isset($yearArr[$year]) ? true : false;
            $is_month = isset($monthArr[$month]) ? true : false;
            if(!$is_year || !$is_month) return $return = ['status' => -1, 'msg' => '批号格式错误'];

            $month  = $monthArr[$month];
            $year   = $yearArr[$year];
            
            //计算生产日期
            $year = $year.'-'.$month.'-01';
            $start_time = strtotime($year);
            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }elseif($result3){
            $year   = $result3['2'] + 2010;
            $day    = $result3['3'];
            //计算生产日期
            $year = $year.'-01-00';
            $start_time = strtotime($year) + 24 * 3600 * $day;
            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }
    }
    //规则34
    static function rule34($number){
        $rule1      = '/^(([0-9]{1})[0-9]{4}){1}$/';
        $rule2      = '/^(([0-9]{2})([0-9]{3})[0-9]{1}){1}$/'; 

        preg_match($rule1,$number,$result1); 
        preg_match($rule2,$number,$result2);

        if($result1){
            $year   = $result1['2'];

            $year = $year + 2010;

            //计算生产日期
            $year = $year.'-01';
            $start_time = strtotime($year);
            $startDay   = date('Y年',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }elseif($result2){

            $year   = $result2['2'];
            $day    = $result2['3'];

            $year   = $year + 2000 - 4;
            $day    = $day - 400;

            $year   = $year.'-01-00';
            $start_time = strtotime($year) + 24 * 3600 * $day;
            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }
    }
    //规则35
    static function rule35($number){
        $rule = '/^(\+([A-H|J-N|P-Z]{1})([0-9]{3})){1}$/';         
        preg_match($rule,$number,$result); 

        if(!$result){
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }else{
            $year   = $result['2'];
            $day    = $result['3'];

            $yearArr= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H',  'J', 'K', 'L', 'M', 'N', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
            foreach ($yearArr as $key => $value) {
                if($year == $value) $year = $key + 2004;
            }
            //计算生产日期

            $year   = $year.'-01-00';
            $start_time = strtotime($year) + 24 * 3600 * $day;
            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }
    }
    //规则36
    static function rule36($number){
        $rule = '/^(([0-9]{2})(0?[[1-9]|1[0-2])([0-9]{2})){1}$/';         
        preg_match($rule,$number,$result); 

        if(!$result){
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }else{
            $year   = $result['2'] + 2000;
            $month  = $result['3'];
            $day    = $result['4'];

            $month  = str_pad($month,2,'0',STR_PAD_LEFT);
            $day    = str_pad($day,2,'0',STR_PAD_LEFT);
            //计算生产日期

            $year   = $year.'-'.$month.'-'.$day;
            $start_time = strtotime($year);
            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }
    }
    //规则37
    static function rule37($number){
        $rule1      = '/^([A-Z]{1}([0-9]{1})([1-9|O|N|D]{1})[0-9]{2}){1}$/';
        $rule2      = '/^(([A-H|J-N|P-Z]{1})([0-9]{3})[0-9|A-Z]{2}){1}$/'; 
        $rule3      = '/^([A-Z]{1}([A-H|J-N|P-Z]{1})([1-9|O|N|D]{1})([0-9]{2})[0-9|A-Z]{2}){1}$/'; 

        preg_match($rule1,$number,$result1); 
        preg_match($rule2,$number,$result2);
        preg_match($rule3,$number,$result3);

        if($result1){
            $year   = $result1['2'];
            $day    = $result1['3'];

            $year = $year + 2010;

            switch ($day) {
                case '<= 9':
                    $day = $day;
                    break;
                case 'O':
                    $day = 10;
                    break;
                case 'N':
                    $day = 11;
                    break;
                case 'D':
                    $day = 12;
                    break;
            }
            $day    = str_pad($day,2,'0',STR_PAD_LEFT);

            $year = $year.'-'.$day;
            $start_time = strtotime($year);

            //计算生产日期
            $year = $year.'-'.$day;
            $start_time = strtotime($year);
            $startDay   = date('Y年m月',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }elseif($result2){

            $year   = $result2['2'];
            $day    = $result2['3'];

            $yearArr= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
            foreach ($yearArr as $key => $value) {
                if($year == $value) $year = $key + 2004;
            }


            $year = $year.'-01-00';
            $start_time = strtotime($year) + 24 * 3600 * $day;
            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }elseif($result3){
            $year   = $result3['2'];
            $month  = $result3['3'];
            $day    = $result3['4'];
            $yearArr= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
            foreach ($yearArr as $key => $value) {
                if($year == $value) $year = $key + 2004;
            }
            switch ($month) {
                case '<= 9':
                    $month = $month;
                    break;
                case 'O':
                    $month = 10;
                    break;
                case 'N':
                    $month = 11;
                    break;
                case 'D':
                    $month = 12;
                    break;
            }

            $month    = str_pad($month,2,'0',STR_PAD_LEFT);
            $day      = str_pad($day,2,'0',STR_PAD_LEFT);

            $year = $year.'-'.$month.'-'.$day;
            $start_time = strtotime($year);
            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }
    }
    //规则38
    static function rule38($number){
        $rule = '/^([A-Z]{1}([0-9]{2})(0?[[1-9]|1[0-2])([0-9]{2})){1}$/';         
        preg_match($rule,$number,$result); 

        if(!$result){
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }else{
            $year   = $result['4'] + 2000;
            $month  = $result['3'];
            $day    = $result['2'];

            $month  = str_pad($month,2,'0',STR_PAD_LEFT);
            $day    = str_pad($day,2,'0',STR_PAD_LEFT);
            //计算生产日期

            $year   = $year.'-'.$month.'-'.$day;
            $start_time = strtotime($year);
            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }
    }
    //规则39 
    /**
     * used 圣罗兰
     */
    static function rule39($number){
        $rule = '/^([A-Z|0-9]{2}([J-N|P-Z]{1})([1-9|O|N|D]{1})[0-9|A-Z]{2}){1}$/';         
        preg_match($rule,$number,$result); 

        if(!$result){
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }else{
            $year   = $result['2'];
            $month  = $result['3'];

            $yearArr= array('J', 'K', 'L', 'M', 'N', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
            foreach ($yearArr as $key => $value) {
                if($year == $value) $year = $key + 2012;
            }
            switch ($month) {
                case '<= 9':
                    $month = $month;
                    break;
                case 'O':
                    $month = 10;
                    break;
                case 'N':
                    $month = 11;
                    break;
                case 'D':
                    $month = 12;
                    break;
            }

            $month  = str_pad($month,2,'0',STR_PAD_LEFT);
            //计算生产日期

            $year   = $year.'-'.$month;
            $start_time = strtotime($year);
            $startDay   = date('Y年m月',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }
    }
    //规则40
    static function rule40($number){
        $rule    = '/^([A-Z|0-9]{2}([A-H|J-N|P-Z]{1})[0-9|A-Z]{3}){1}$/';

        preg_match($rule,$number,$result); 

        if($result){
            $year   = $result['2'];

            $yearArr= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
            foreach ($yearArr as $key => $value) {
                if($year == $value) $year = $key + 2004;
            }
            //计算生产日期
            $year = $year.'-01';
            $start_time = strtotime($year);
            $startDay   = date('Y年',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }
    }
    //规则41
    static function rule41($number){
        $rule = '/^(([0-9]{1})([0-9]{3})[A-Z|0-9]{6}){1}$/';         
        preg_match($rule,$number,$result); 
        if(!$result){
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }else{
            $year = '201'.$result['2'].'-01-00';
            $date = intval($result['3']);

            //计算生产日期
            $start_time = strtotime($year) + 24 * 3600 * $date;
            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }
    }
    //规则42
    static function rule42($number){
        $rule1  = '/^([A-Z|0-9]{2}([0-9]{3})([0-9]{2})){1}$/';
        $rule2  = '/^(([0-9]{1})([0-9]{3})[0-9|A-Z]{2}){1}$/';  

        preg_match($rule1,$number,$result1); 
        preg_match($rule2,$number,$result2);

        if($result1){
            $year   = $result1['3'];
            $date   = $result1['2'];
            //计算生产日期
            $year = $year + 2000;
            $start_time = strtotime($year.'-01-00') + 24 * 3600 * $date;
            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }elseif($result2){
            $year    = $result2['2'];
            $date    = $result2['3'];

            $year    = $year + 2010; 
            //计算生产日期
            $start_time = strtotime($year.'-01-00') + 24 * 3600 * $date;
            $end_time   = strtotime('+3 year',$start_time);
            $startDay   = date('Y年m月d日',$start_time);
            $endDay     = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }
    }
    //规则43
    static function rule43($number){
        $rule1  = '/^(([0-9]{3})([0-9]{1})){1}$/';
        $rule2  = '/^([0-9|A-Z]{4}([0-9]{3})([0-9]{1})){1}$/';  

        preg_match($rule1,$number,$result1); 
        preg_match($rule2,$number,$result2);

        if($result1){
            $year   = $result1['3'];
            $date   = $result1['2'];
            //计算生产日期
            $year = $year + 2010;
            $start_time = strtotime($year.'-01-00') + 24 * 3600 * $date;
            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }elseif($result2){
            $year    = $result2['3'];
            $date    = $result2['2'];

            $year    = $year + 2010; 

            //计算生产日期
            $start_time = strtotime($year.'-01-00') + 24 * 3600 * $date;
            $end_time   = strtotime('+3 year',$start_time);
            $startDay   = date('Y年m月d日',$start_time);
            $endDay     = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }
    }
    //规则44
    static function rule44($number){       
        $rule1  = '/^(([0-9]{3})([0-9]{1})){1}$/';
        $rule2  = '/^([0-9|A-Z]{4}([0-9]{3})([0-9]{1})){1}$/';  

        preg_match($rule1,$number,$result1); 
        preg_match($rule2,$number,$result2);

        if($result1){
            $year = '201'.$result1['3'].'-01-00';
            $date = intval($result1['2']);

            //计算生产日期
            $start_time = strtotime($year) + 24 * 3600 * $date;
            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }elseif($result2){
            $year    = $result2['3'];
            $date    = $result2['2'];

            $year    = $year + 2010; 

            //计算生产日期
            $start_time = strtotime($year.'-01-00') + 24 * 3600 * $date;
            $end_time   = strtotime('+3 year',$start_time);
            $startDay   = date('Y年m月d日',$start_time);
            $endDay     = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }
    }
    //规则45
    static function rule45($number){
        $rule = '/^(([A-Z]{3})([0-9]{4})([0-9]{2})([0-9]{2})){1}$/';         
        preg_match($rule,$number,$result); 
        if(!$result){
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }else{
            $year   = $result['3'];
            $month  = intval($result['4']);
            $day    = intval($result['5']);

            //计算生产日期
            $start_time = strtotime($year.'-'.$month.'-'.$day);
            if($result['2'] == 'MGF'){
                $startDay   = date('Y年m月d日',$start_time);
                $end_time   = strtotime('+3 year',$start_time);
                $endDay     = date('Y年m月d日',$end_time);
                return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];               
            }else{
                $endDay     = date('Y年m月d日',$start_time);
                $end_time   = strtotime('-3 year',$start_time);
                $startDay   = date('Y年m月d日',$end_time);
                return $return = ['status' => 1,'startDay' => $startDay,'endDay'=>$endDay];   
            }
        }
    }
    //规则46
    static function rule46($number){
        $rule = '/^([A-Z|0-9]{2}([0-9]{2})([A-Z]{1})[0-9|A-Z]{3}){1}$/';         
        preg_match($rule,$number,$result); 
        if(!$result){
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }else{
            $year   = 2000 + $result['2'];
            $month  = $result['3'];

            $monthArr= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
            foreach ($monthArr as $key => $value) {
                if($month == $value) $month = $key + 1;
            }
            //计算生产日期
            $start_time = strtotime($year.'-'.$month);
            $startDay   = date('Y年m月',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月',$end_time);
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];               
        }
    }
    //规则47
    static function rule47($number){
        $rule = '/^([A-Z|0-9]{8}([0-9]{2})\/([0-9]{2})){1}$/';         
        preg_match($rule,$number,$result); 

        if(!$result){
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }else{
            $year   = 2000 + $result['3'];
            $month  = $result['2'];

            //计算生产日期
            $end_time   = strtotime($year.'-'.$month);
            $endDay     = date('Y年m月',$end_time);
            $start_time = strtotime('-3 year',$end_time);
            $startDay   = date('Y年m月',$start_time);
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];               
        }
    }
    //规则48
    static function rule48($number){
        $rule1  = '/^([0-9|A-Z]{5}([0-9]{2})([0-9]{1})){1}$/';
        $rule2  = '/^([0-9|A-Z]{4}([0-9]{2})([0-9]{1})){1}$/'; 

        preg_match($rule1,$number,$result1); 
        preg_match($rule2,$number,$result2);

        if($result1){
            $year   = $result1['3'];
            $date   = $result1['2'];
            //计算生产日期
            $year = $year + 2010;
            $start_time = strtotime('+'.$date.' week',strtotime($year.'-01'));
            $startDay   = date('Y年m月',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }elseif($result2){
            $year   = $result2['3'];
            $date   = $result2['2'];
            //计算生产日期
            $year = $year + 2010;
            $start_time = strtotime('+'.$date.' week',strtotime($year.'-01'));
            $startDay   = date('Y年m月',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }
    }
    //规则49
    static function rule49($number){
        $rule  = '/^([0-9|A-Z]{2}([0-9]{2})([0-9|X-Z]{1})[0-9|A-Z]{1}){1}$/';

        preg_match($rule,$number,$result); 

        if($result){
            $year       = $result['2'];
            $month      = $result['3'];

            if(is_numeric($month)) {
                $month = $month;
            }else{
                switch ($month) {
                    case 'X':
                        $month = 10;
                        break;
                    case 'Y':
                        $month = 11;
                        break;
                    case 'Z':
                        $month = 12;
                        break;
                }
            }
            //计算生产日期
            $year = $year + 2000;
            $start_time = strtotime($year.'-'.$month);
            $startDay   = date('Y年m月',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }
    }
    //规则50
    static function rule50($number){
        $rule  = '/^(EXP([0-9]{2})([0-9]{2})){1}$/';

        preg_match($rule,$number,$result); 

        if($result){
            $year       = $result['3'];
            $month      = $result['2'];

            //计算生产日期
            $year = $year + 2000;
            $end_time   = strtotime($year.'-'.$month);
            $endDay     = date('Y年m月',$end_time);
            $start_time = strtotime('-3 year',$end_time);
            $startDay   = date('Y年m月',$start_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }
    }
    //规则51
    static function rule51($number){
        $rule  = '/^([0-9|A-Z]{2}([0-9]{1})([A-Z]{1})[0-9|A-Z]{1}){1}$/';//五
        $rule1  = '/^(([0-9]{1})([A-Z]{1})[0-9|A-Z]{1}){1}$/';//三位
        $rule2  = '/^([0-9|A-Z]{3}([0-9]{1})([A-Z]{1})[0-9|A-Z]{1}){1}$/';//六位
        $rule3  = '/^([0-9|A-Z]{4}([0-9]{1})([A-Z]{1})[0-9|A-Z]{1}){1}$/';//七位

        preg_match($rule,$number,$result); 
        preg_match($rule1,$number,$result1); 
        preg_match($rule2,$number,$result2); 
        preg_match($rule3,$number,$result3); 
        if($result){
            $year       = $result['2'];
            $month      = $result['3'];
            $monthArr= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M');
            foreach ($monthArr as $key => $value) {
                if($month == $value) $month = $key + 1;
            }
            //计算生产日期
            $year = $year + 2010;
            $start_time   = strtotime($year.'-'.$month);
            $startDay     = date('Y年m月',$start_time);
            $end_time = strtotime('+3 year',$start_time);
            $endDay   = date('Y年m月',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }elseif($result1 || $result2 || $result3){
            if($result1) $res = $result1;
            if($result2) $res = $result2;
            if($result3) $res = $result3;
            
            $year       = $res['2'];
            $month      = $res['3'];
            $monthArr= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I','J', 'K', 'L');
            foreach ($monthArr as $key => $value) {
                if($month == $value) $month = $key + 1;
            }
            if(!is_numeric($month)){
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
            //计算生产日期
            $year = $year + 2010;
            if($year > date('Y')){
                $year = $year - 10;
            }
            $start_time   = strtotime($year.'-'.$month);
            $startDay     = date('Y年m月d日',$start_time);
            $end_time = strtotime('+3 year',$start_time);
            $endDay   = date('Y年m月d日',$end_time);
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }
    }
    //规则52
    /**
     * used 妮维雅
     * used 11-20 维多利亚的秘密 VICTORIA'S SECRET
     */
    static function rule52($number){
        $rule  = '/^(([0-9]{1})([0-9]{3})[0-9|A-Z]{4}){1}$/';

        preg_match($rule,$number,$result); 

        if($result){
            $year       = $result['2'];
            $date       = $result['3'];

            $year = $year + 2010;
            if($year > date('Y')) $year = $year - 10;

            //计算生产日期
            $start_time   = strtotime($year.'-01-00') + 24 * 3600 * $date;
            $startDay     = date('Y年m月d日',$start_time);
            $end_time = strtotime('+3 year',$start_time);
            $endDay   = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }
    }
    //规则53 
    /**
     * used 2017-12-06 思妍丽 DECLEOR
     */
    static function rule53($number){
        $rule  = '/^(([0-9]{1})([A-S]{1})[0-9|A-Z]{2}){1}$/';//4位码

        preg_match($rule,$number,$result); 

        if($result){
            $year       = $result['2'];
            $month       = $result['3'];

            if ($year <= 3) {
                $year = $year + 2010;
            } else {
                $year = $year + 2000;
            }
            
            $monArr= array('A'=>'01','B'=>'02', 'C'=>'03', 'D'=>'04', 'F'=>'05', 'H'=>'06','J'=>'07','K'=>'08','L'=>'09','N'=>'10','P'=>'11','S'=>'12');
            $is_month = isset($monArr[$month]) ? true : false;
            if(!$is_month) return $return = ['status' => -1, 'msg' => '批号格式错误'];
            
            $month  = $monArr[$month];
            
            $date_str = $year.$month.'01';
            if($date_str >date('Ymd') && $date_str-10000 <=date('Ymd')){
                $year  = $year - 10;
            }
            
            $startDay = $year.'年'.$month.'月'.'01日';
            $endDay = ($year+3).'年'.$month.'月'.'01日';

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            if (strlen($number) != 4) {
                return $return = ['status' => -1, 'msg' => '批号长度有误'];
            } else {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
        }
    }
    //规则54
    static function rule54($number){
        $rule1  = '/^(([0-9]{3})([0-9]{1})){1}$/';
        $rule2  = '/^([0-9|A-Z]{4}([0-9]{3})([0-9]{1})){1}$/'; 

        preg_match($rule1,$number,$result1); 
        preg_match($rule2,$number,$result2);

        if($result1){
            $year   = $result1['3'];
            $date   = $result1['2'];
            //计算生产日期
            $year = $year + 2010;
            $start_time = strtotime($year.'-01-00') + 24 * 3600 * $date;
            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }elseif($result2){
            $year   = $result2['3'];
            $date   = $result2['2'];
            //计算生产日期
            $year = $year + 2010;
            $start_time = strtotime($year.'-01-00') + 24 * 3600 * $date;
            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }
    }
    //规则55
    static function rule55($number){
        $rule  = '/^(([0-9]{1})([0-9]{2})(\w{1})){1}$/';
        preg_match($rule,$number,$result); 

        if($result){
            $year       = $result['2'];
            $month      = $result['3'];
            //计算生产日期
            if($year<=6){
                $year = $year + 2010;
            }else{
                $year = $year + 2000;
            }
            
            $start_time   = strtotime($year.'-'.$month);
            $startDay     = date('Y年m月d日',$start_time);
            $end_time = strtotime('+3 year',$start_time);
            $endDay   = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }
    }
    //规则56
    static function rule56($number){
        $rule1  = '/^(([0-9]{3})([0-9]{1})){1}$/';
        $rule2  = '/^([0-9|A-Z]{4}([0-9]{3})([0-9]{1})){1}$/';

        preg_match($rule1,$number,$result1); 
        preg_match($rule2,$number,$result2);
        if($result1){
            $year      = $result1['3'];
            $date      = $result1['2'];

            //计算生产日期
            $year = $year + 2010;
            $start_time     = strtotime($year.'-01-00') + 24 * 3600 * $date;
            $startDay       = date('Y年m月d日',$start_time);
            $end_time       = strtotime('+3 year',$start_time);
            $endDay         = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }elseif($result2){
            $year      = $result2['3'];
            $day     = $result2['2'];
            //计算生产日期
            $year = $year + 2010;
            if($year > date('Y')){
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
            $start_time     = strtotime($year.'-01') + 24 * 3600 * ($day-1);
            $startDay       = date('Y年m月d日',$start_time);
            $end_time       = strtotime('+3 year',$start_time);
            $endDay         = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }
    }
    //规则57
    static function rule57($number){
        $rule1  = '/^(([0-9]{1})([A-M]{1})[0-9|A-Z]{1}){1}$/';
        $rule2  = '/^(([A-M]{1})[0-9|A-Z]{1}([0-9]{1})){1}$/';

        preg_match($rule1,$number,$result1); 
        preg_match($rule2,$number,$result2); 

        if($result1){
            $year      = $result1['2'];
            $month     = $result1['3'];
            $monthArr= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M');
            foreach ($monthArr as $key => $value) {
                if($month == $value) $month = $key + 1;
            }
            //计算生产日期
            $year = $year + 2010;
            $start_time     = strtotime($year.'-'.$month);
            $startDay       = date('Y年m月',$start_time);
            $end_time       = strtotime('+3 year',$start_time);
            $endDay         = date('Y年m月',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }elseif($result2){
            $year      = $result2['3'];
            $month     = $result2['2'];
            $monthArr= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M');
            foreach ($monthArr as $key => $value) {
                if($month == $value) $month = $key + 1;
            }
            //计算生产日期
            $year = $year + 2010;
            $start_time     = strtotime($year.'-'.$month);
            $startDay       = date('Y年m月',$start_time);
            $end_time       = strtotime('+3 year',$start_time);
            $endDay         = date('Y年m月',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }
    }
    //规则58
    static function rule58($number){
        $rule  = '/^([0-9|A-Z]{2}([0-9]{1})([A-M]{1})[0-9|A-Z]{2}){1}$/';

        preg_match($rule,$number,$result); 

        if($result){
            $year      = $result['2'];
            $month     = $result['3'];

            $monthArr= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M');
            foreach ($monthArr as $key => $value) {
                if($month == $value) $month = $key + 1;
            }
            //计算生产日期
            $year = $year + 2010;
            $start_time     = strtotime($year.'-'.$month);
            $startDay       = date('Y年m月',$start_time);
            $end_time       = strtotime('+3 year',$start_time);
            $endDay         = date('Y年m月',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }
    }
    //规则59
    static function rule59($number){
        $rule  = '/^(([0-9]{1})([N-Z]{1})([0-9]{2})){1}$/';

        preg_match($rule,$number,$result); 

        if($result){
            $year      = $result['2'];
            $month     = $result['3'];
            $date      = $result['4'];

            $monthArr= array('N', 'P', 'Q', 'R', 'S', 'T','U', 'V', 'W', 'J', 'X', 'Y', 'Z');
            foreach ($monthArr as $key => $value) {
                if($month == $value) $month = $key + 1;
            }

            //计算生产日期
            $year = $year + 2010;
            $start_time     = strtotime($year.'-'.$month.'-'.$date);
            $startDay       = date('Y年m月d日',$start_time);
            $end_time       = strtotime('+3 year',$start_time);
            $endDay         = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }
    }
    //规则60
    static function rule60($number){
        $rule  = '/^(([A-Z]{1})([1-9|X-Z]{1})([0-9|A-Z]{1})[0-9|A-Z]{2}){1}$/';

        preg_match($rule,$number,$result); 

        if($result){
            $year      = $result['2'];
            $month     = $result['3'];
            $date      = $result['4'];

            $yearArr    = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H','I','J', 'K', 'L', 'M', 'N','O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
            $dateArr    = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P',  'R', 'S', 'T', 'U', 'V', 'W');

            foreach ($yearArr as $key => $value) {
                if($year == $value) $year = $key + 2011;
            }

            switch ($month) {
                case 'X':
                    $month = 10;
                    break;
                case 'Y':
                    $month = 11;
                    break;
                case 'Z':
                    $month = 12;
                    break;
            }

            switch ($date) {
                case '>= 1':
                    $date = $date;
                    break;
                case '0':
                    $date = 10;
                    break;
                default:
                    foreach ($dateArr as $key => $value) {
                        if($date == $value) $date = $key + 11;
                    }
                    break;
            }

            //计算生产日期
            $start_time     = strtotime($year.'-'.$month.'-'.$date);
            $startDay       = date('Y年m月d日',$start_time);
            $end_time       = strtotime('+3 year',$start_time);
            $endDay         = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }
    }
    //规则61
    static function rule61($number){
        $rule  = '/^(([A-Z]{1})([0-9|X-Z]{1})[0-9|A-Z]{2}){1}$/';

        preg_match($rule,$number,$result); 

        if($result){
            $year      = $result['2'];
            $month     = $result['3'];

            $yearArr1 = array('T', 'U', 'A', 'B', 'C', 'D','E', 'F', 'G', 'H');
            $yearArr2 = array('I', 'J', 'K', 'L', 'M', 'N','P', 'Q', 'R', 'S');

            if(in_array($year,$yearArr1)){
                $yearArr = $yearArr1;
            }elseif(in_array($year,$yearArr2)){
                $yearArr = $yearArr2;
            }else{
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }

            foreach ($yearArr as $key => $value) {
                if($year === $value){
                    $year = $key;
                } 

            }

            switch ($month) {
                case 'X':
                    $month = 10;
                    break;
                case 'Y':
                    $month = 11;
                    break;
                case 'Z':
                    $month = 12;
                    break;
            }

            //计算生产日期
            $year = $year + 2008;
            $start_time     = strtotime($year.'-'.$month);
            $startDay       = date('Y年m月',$start_time);
            $end_time       = strtotime('+3 year',$start_time);
            $endDay         = date('Y年m月',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }
    }
    //规则62
    static function rule62($number){
        $rule  = '/^([0-9|A-Z]{3}){1}$/';

        preg_match($rule,$number,$result); 

        if($result){
            $position = 0 ;
            $firstYear = date('Y') - 3;

            for ($i=0; $i < strlen($result[1]) ; $i++) { 
                if(preg_match ('/^[A-Z]/', $result[1][$i])){
                    $position   = $i + 1;
                    $month      = $result[1][$i]; 
                    break; 
                } 
            }
            if(!$position) return $return = ['status' => -1, 'msg' => '批号格式错误'];

            $year = $firstYear + $position;

            $month1 = array('A', 'B', 'C', 'D', 'E', 'F','G', 'H', 'I', 'J','K','L');
            $month2 = array('N', 'P', 'Q', 'R', 'S', 'T','U', 'V', 'W', 'X','Y','Z');

            if(in_array($month,$month1)){
                $monthArr = $month1;
            }elseif(in_array($month,$month2)){
                $monthArr = $month2;
            }else{
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }

            foreach ($monthArr as $key => $value) {
                if($month === $value){
                    $month = $key + 1;
                } 
            }

            //计算生产日期
            $start_time     = strtotime($year.'-'.$month);
            $startDay       = date('Y年m月',$start_time);
            $end_time       = strtotime('+3 year',$start_time);
            $endDay         = date('Y年m月',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }
    }
    //规则63
    static function rule63($number){
        $rule1  = '/^([0-9|A-Z]{1}([A-D]{1})([1-9]{1})([1-9]{1})([1-9|X-Z]{1})[1-9|A-Z]{1}){1}$/';
        $rule2  = '/^([0-9|A-Z]{2}([A-D]{1})([1-9]{1})([1-9]{1})([1-9|X-Z]{1})[1-9|A-Z]{1}){1}$/';

        preg_match($rule1,$number,$result1); 
        preg_match($rule2,$number,$result2); 

        $result  = $result1 ? $result1 : $result2;
        if($result){
            $year      = $result['4'];
            $month     = $result['5'];
            $day1      = $result['2'];
            $day2      = $result['3'];
            //计算生产日期
            $year = $year + 2010;
            switch ($month) {
                case '<= 9':
                    $month = $month;
                    break;
                case 'X':
                    $month = 10;
                    break;
                case 'Y':
                    $month = 11;
                    break;
                case 'Z':
                    $month = 12;
                    break;
            }

            switch ($day1) {
                case 'A':
                    $day1 = 0;
                    break;
                case 'B':
                    $day1 = 1;
                    break;
                case 'C':
                    $day1 = 2;
                    break;
                case 'D':
                    $day1 = 3;
                    break;
            }
            $day = $day1.$day2;
            $start_time     = strtotime($year.'-'.$month.'-'.$day);
            $startDay       = date('Y年m月d日',$start_time);
            $end_time       = strtotime('+3 year',$start_time);
            $endDay         = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }
    }
    //规则64
    static function rule64($number){
        $rule = '/^([0-9|A-Z]{2}([A-Z]{1})([L-N|P|R-T|V-Z]{1})[0-9|A-Z]{2}){1}$/';         
        preg_match($rule,$number,$result); 

        if(!$result){
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }else{
            $year   = $result['2'];
            $month  = $result['3'];

            $yearArr= ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
            foreach ($yearArr as $key => $value) {
                if($year == $value) $year = $key + 2010;
            }
            $monthArr = ['L','M','N','P','R', 'S', 'T', 'V', 'W', 'X', 'Y', 'Z'];

            foreach ($monthArr as $k => $v) {
                if($month == $v) $month = $k + 1;
            }

            $timeStr    = $year.'-'.$month;
            $start_time = strtotime($timeStr);
            //计算生产日期
            $startDay   = date('Y年m月',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }
    }
    //规则65 茵芙莎
    static function rule65($number){
        $rule1  = '/^(([0-9]{1})([0-9]{3})[0-9|A-Z]{1}){1}$/';         
        $rule2  = '/^(([0-9]{1})([0-9]{3})([0-9|A-Z]{2})){1}$/';

        preg_match($rule1,$number,$result1); 
        preg_match($rule2,$number,$result2);

        if($result1){
            $year   = $result1['2'];
            $day    = $result1['3'];

            $year = $year + 2010;
            if($year > date('Y')){
                $year = $year - 10;
            }
            //计算生产日期
            $year = $year.'-01-00';
            $start_time = strtotime($year) + 24 * 3600 * $day;
            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }elseif($result2){
            $year      = $result2['2'];
            $day       = $result2['3'];

            //计算生产日期
            $year = $year + 2010;
            if($year > date('Y')){
                $year = $year - 10;
            }
            //计算生产日期
            $year = $year.'-01-00';
            $start_time = strtotime($year) + 24 * 3600 * $day;
            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }
    }
    //规则66
    static function rule66($number){
        $rule = '/^(([A-Z]){1}([A-Z]{1})[0-9|A-Z]{2}){1}$/';         
        preg_match($rule,$number,$result); 

        if(!$result){
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }else{
            $year   = $result['2'];
            $month  = $result['3'];

            $yearArr= ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
            foreach ($yearArr as $key => $value) {
                if($year == $value) $year = $key + 2009;
            }
            $monthArr = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];

            foreach ($monthArr as $k => $v) {
                if($month == $v) $month = $k + 1;
            }

            //计算生产日期
            $timeStr    = $year.'-'.$month;
            $start_time = strtotime($timeStr);
            $startDay   = date('Y年m月',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }
    }
    //规则67
    static function rule67($number){
        $rule1  = '/^(([0-9]{1})([A-Z]{1})([0-9]{2})){1}$/';
        $rule2  = '/^(([0-9]{4})([0-9]{2})([0-9]{2})){1}$/';

        preg_match($rule1,$number,$result1); 
        preg_match($rule2,$number,$result2);

        if($result1){
            $year      = $result1['2'];
            $month     = $result1['3'];
            $day       = $result1['4'];

            $monthArr=  ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
            foreach ($monthArr as $key => $value) {
                if($month == $value) $month = $key <=11 ? $key + 1 : $key - 11;
            }
            //计算生产日期
            $year           = $year + 2010;
            $start_time     = strtotime($year.'-'.$month.'-00') + 24 * 3600 * $day;
            $startDay       = date('Y年m月d日',$start_time);
            $end_time       = strtotime('+3 year',$start_time);
            $endDay         = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }elseif($result2){
            $year      = $result2['2'];
            $month     = $result2['3'];
            $day       = $result2['4'];

            //计算生产日期
            $start_time     = strtotime($year.'-'.$month.'-'.$day);
            $startDay       = date('Y年m月d日',$start_time);
            $end_time       = strtotime('+3 year',$start_time);
            $endDay         = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }
    }
    //规则68
    static function rule68($number){
        $rule = '/^([0-9|A-Z]{3}([0-9]{3})([0-9]{1})){1}$/';
        preg_match($rule,$number,$result); 

        if(!$result){
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }else{
            $year   = $result['3'];
            $day    = $result['2'];

            $year   = $year + 2010;

            //计算生产日期
            $year = $year.'-01-00';
            $start_time = strtotime($year) + 24 * 3600 * $day;
            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }
    }
    //规则69
    static function rule69($number){
        $rule = '/^(([0-9]{2})([H-Z]{1})([A-Z]{1})[0-9|A-Z]{2}){1}$/';
        preg_match($rule,$number,$result); 

        if(!$result){
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }else{
            $year      = $result['3'];
            $month     = $result['4'];
            $day       = $result['2'];

            $yearArr   =  [ 'H', 'I','J', 'K', 'L', 'M', 'N','O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
            foreach ($yearArr as $key => $value) {
                if($year == $value) $year = $key +2000;
            }
            $monthArr   =  ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
            foreach ($monthArr as $k => $v) {
                if($month == $v) $month = $k <=11 ? $k + 1 : $k - 11;
            }
            //计算生产日期
            $start_time     = strtotime($year.'-'.$month.'-'.$day);
            $startDay       = date('Y年m月d日',$start_time);
            $end_time       = strtotime('+3 year',$start_time);
            $endDay         = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }
    }
    //规则70
    static function rule70($number){
        $rule = '/^(([A-H|J|K]{1})([X-Z|0-9]{1})([0-9|A-H|J-N|P|R-X]{1})([A-Z|0-9]{2})){1}$/';

        preg_match($rule,$number,$result); 

        if($result){
            $year      = $result['2'];
            $month     = $result['3'];
            $date      = $result['4'];

            $yearArr    = $yearArr= array('H', 'J', 'K','A', 'B', 'C', 'D', 'E', 'F', 'G');
            $dateArr    = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'R', 'S', 'T', 'U', 'V', 'W','X');

            foreach ($yearArr as $key => $value) {
                if($year == $value) $year = $key + 2008;
            }

            switch ($month) {
                case 'X':
                    $month = 10;
                    if($year == '2017') $year = 2007; 
                    break;
                case 'Y':
                    $month = 11;
                    if($year == '2017') $year = 2007; 
                    break;
                case 'Z':
                    $month = 12;
                    if($year == '2017') $year = 2007; 
                    break;
                case '>= 1':
                    $date = $date;
                    break;
                default:
                   
            }

            switch ($date) {
                case '>= 1':
                    $date = $date;
                    break;
                case '0':
                    $date = 10;
                    break;
                default:
                    foreach ($dateArr as $key => $value) {
                        if($date == $value) $date = $key + 11;
                    }
                    break;
            }
            //计算生产日期
            $start_time     = strtotime($year.'-'.$month.'-'.$date);
            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }
    }
    //规则71 
    //9-20 雪花秀 sulwhasoo  11-17 谜尚 MISSHA 
    static function rule71($number){
        $rule = '/^(([0-9]{4})([0-9]{2})([0-9]{2})){1}$/';//8位码
        preg_match($rule,$number,$result); 

        if(!$result){
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }else{
            $year      = $result['2'];
            $month     = $result['3'];
            $day       = $result['4'];
            //计算生产日期
            $isDate = checkdate($month, $day, $year);
            $date_str = $year.$month.$day; 
            if(!$isDate || $date_str >date('Ymd') || $year < 1000){
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
            $start_time     = strtotime($year.'-'.$month.'-'.$day);
            $startDay       = date('Y年m月d日',$start_time);
            $end_time       = strtotime('+3 year',$start_time);
            $endDay         = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }
    }

    //规则72
    //2017-9-26 蝶翠诗Dhc(before rule17)  
    static function rule72($number){
        $rule = '/^(([A-Z]{1})([A-Z]{1})[0-9|A-Z]{3}){1}$/'; //五位码
        preg_match($rule,$number,$res); 
        if($res){
            $month  = $res['3'];
            $year   = $res['2'];
            $yearArr= array('C'=>2011, 'D'=>2012, 'E'=>2013,'F'=>2014,'G'=>2015,'H'=>2016);
            $monthArr= array('A'=>'03','B'=>'04', 'C'=>'04', 'D'=>'05', 'E'=>'06', 'F'=>'07',  'G'=>'08', 'H'=>'09', 'J'=>'10','K'=>'11','L'=>'12','M'=>'1','N'=>'2');
            $is_year = isset($yearArr[$year]) ? true : false;
            $is_month = isset($monthArr[$month]) ? true : false;
            if(!$is_year || !$is_month) return $return = ['status' => -1, 'msg' => '批号格式错误'];

            $month  = $monthArr[$month];
            $year   = $yearArr[$year];
            
            //计算生产日期
            $year = $year.'-'.$month.'-01';
            $start_time = strtotime($year);
            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }
    }

    //规则73
    /**
     * used 2017-10-23 无印良品
     */
    static function rule73($number){
        $rule = '/^(([A-Z]{1})([0-9]{1})[0-9|A-Z]{2}){1}$/'; //四位码
        preg_match($rule,$number,$res); 
        if($res){
            $month  = $res['2'];
            $year   = $res['3'];

            $year = $year + 2010;
            if($year > date('Y')){
                $year = $year - 10;
            }
            $monthArr= array('A'=>'01','B'=>'02', 'C'=>'03', 'D'=>'04', 'E'=>'05', 'F'=>'06',  'G'=>'07', 'H'=>'08','I'=>'09','J'=>'10','K'=>'11','L'=>'12');
            $is_month = isset($monthArr[$month]) ? true : false;
            if(!$is_month) return $return = ['status' => -1, 'msg' => '批号格式错误'];

            $month  = $monthArr[$month];
            
            //计算生产日期
            $year = $year.'-'.$month.'-01';
            $start_time = strtotime($year);
            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }
    }

    //规则74
    //2017-10-23 曼丹 
    static function rule74($number){
        $rule = '/^(([0-9]{2})([0-9]{2})([0-9]{2})){1}$/'; //6位码
        preg_match($rule,$number,$res); 
        if($res){
            $day    = $res['2'];
            $month  = $res['3'];
            $year   = $res['4'];

            $year = $year + 2000;
            if($year > date('Y')){
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }

            //计算生产日期
            $year = $year.'-'.$month.'-'.$day;
            $start_time = strtotime($year);
            if(!$start_time){
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }
    }

    //规则75
    //2017-11-16 菲诗小铺 The Face Shop 
    static function rule75($number){
        $rule = '/^(([0-9]{4})([0-9]{2})([0-9]{2})){1}$/'; //8位码
        preg_match($rule,$number,$res); 
        if($res){
            $day    = $res['4'];
            $month  = $res['3'];
            $year   = $res['2'];

            if($year > date('Y')){
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }

            //计算生产日期
            $year = $year.'-'.$month.'-'.$day;
            $start_time = strtotime($year);
            if(!$start_time){
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }
    }

    //规则76
    //2017-11-16 艾杜纱 Ettusais 复制rule42
    static function rule76($number){
        $rule  = '/^(([0-9]{1})([0-9]{3})[0-9|A-Z]{2}){1}$/';  
        preg_match($rule,$number,$result);
        if($result){
            $year    = $result['2'];
            $date    = $result['3'];

            $year    = $year + 2010; 
            if($year > date('Y')){
                $year = $year - 10;
            }
            //计算生产日期
            $start_time = strtotime($year.'-01-00') + 24 * 3600 * $date;
            $end_time   = strtotime('+3 year',$start_time);
            $startDay   = date('Y年m月d日',$start_time);
            $endDay     = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }
    }

    //规则77
    //2017-11-16 杜克 SKINCEUTICALS 
    static function rule77($number){
        $rule = '/^([0-9|A-Z]{2}([A-Z]{1})([0-9]{1})[0-9|A-Z]{2}){1}$/'; //6位码
        preg_match($rule,$number,$res); 
        if($res){
            $year    = $res['2'];
            $month  = $res['3'];

            $yearArr= array('A'=>'04','B'=>'05','C'=>'06', 'D'=>'07', 'E'=>'08','F'=>'09','G'=>'10','H'=>'11','J'=>'12','K'=>'13','L'=>'14','M'=>'15','N'=>'16','P'=>'17');
            $is_year = isset($yearArr[$year]) ? $year='20'.$yearArr[$year] : false;
            $month = $month == 0 ? 10 : $month;

            if(!$is_year) return $return = ['status' => -1, 'msg' => '批号格式错误'];

            //计算生产日期
            $year = $year.'-'.$month.'-01';
            $start_time = strtotime($year);
            if(!$start_time){
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }
    }

    //规则78
    //2017-11-16 美宝莲
    static function rule78($number){
        $rule = '/^([0-9|A-Z]{1}([A-Z]{1})([0-9]{3})){1}$/'; //5位码
        preg_match($rule,$number,$res); 
        if($res){
            $year    = $res['2'];
            $day  = $res['3'];

            $yearArr= array('A'=>'04','B'=>'05','C'=>'06', 'D'=>'07', 'E'=>'08','F'=>'09','G'=>'10','H'=>'11','J'=>'12','K'=>'13');

            $is_year = isset($yearArr[$year]) ? $year='20'.$yearArr[$year] : false;

            if(!$is_year) return $return = ['status' => -1, 'msg' => '批号格式错误'];

            //计算生产日期
            $start_time = strtotime($year.'-01-00') + 24 * 3600 * $day;
            if(!$start_time){
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }
    }

    //规则79
    //2017-11-17 美即 MG
    static function rule79($number){
        $rule = '/^(([0-9]{4})([0-9]{2})){1}$/'; //6位码
        preg_match($rule,$number,$res); 
        if($res){
            $year       = $res['2'];
            $month      = $res['3'];

            $yearArr    = [2007,2020];

            if($year<$yearArr[0] || $year>$yearArr[1] || $month > 12) return $return = ['status' => -1, 'msg' => '批号格式错误'];

            //计算生产日期
            $year = ($year-3).'-'.$month.'-01';
            $start_time = strtotime($year);

            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }
    }

    //规则80
    //2017-11-17 曼秀雷敦
    static function rule80($number){
        $rule = '/^(([0-9]{4})[\w\W]{1}([0-9]{2})){1}$/'; //7位码
        preg_match($rule,$number,$res); 
        if($res){
            $year       = $res['2'];
            $month      = $res['3'];

            $yearArr    = [2007,2020];

            if($year<$yearArr[0] || $year>$yearArr[1] || $month > 12) return $return = ['status' => -1, 'msg' => '批号格式错误'];

            //计算生产日期
            $year = ($year-3).'-'.$month.'-01';
            $start_time = strtotime($year);

            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }
    }

    //规则81
    /**
     * used 2017-11-17 丝芙兰 SEPHORA
     * used 2017-12-05 阿瓦隆 Avalon
     */
    static function rule81($number){
        $rule = '/^(([0-9]{1})([0-9]{3})[0-9|A-Z]{1}){1}$/'; //5位码
        preg_match($rule,$number,$res); 
        if($res){
            $year       = $res['2'];
            $date        = $res['3'];
            
            //天数不能为0
            if ($date < 1) {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }

            $year    = $year + 2010; 
            if($year > date('Y')){
                $year = $year - 10;
            }
            $yearDate  = self::getYearDate($year);
            //不符合当年天数
            if ($date > $yearDate) {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
            $date_str = date('Ymd',strtotime($year.'-01-00') + 24 * 3600 * $date);
            if($date_str >date('Ymd') && $date_str-10000 <=date('Ymd')){
                $year  = $year - 10;
                
                //年份-10，再验证一遍
                $yearDate  = self::getYearDate($year);
                if ($date > $yearDate) {
                    return $return = ['status' => -1, 'msg' => '批号格式错误'];
                }
            }

            //计算生产日期
            $start_time   = strtotime($year.'-01-00') + 24 * 3600 * $date;
            $startDay     = date('Y年m月d日',$start_time);
            $end_time = strtotime('+3 year',$start_time);
            $endDay   = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            if (strlen($number) != 5) {
                return $return = ['status' => -1, 'msg' => '批号长度有误'];
            } else {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
        }
    }

    //规则82
    //2017-11-17 雅芳 Avon
    static function rule82($number){
        $rule = '/^([0-9|A-Z]{1}([0-9]{3})([0-9]{1})){1}$/'; //5位码
        preg_match($rule,$number,$res); 
        if($res){
            $year       = $res['3'];
            $day        = $res['2'];
            
            $year    = $year + 2010; 
            if($year > date('Y')){
                $year = $year - 10;
            }
            //计算生产日期
            $start_time = strtotime($year.'-01-00') + 24 * 3600 * $day;
            if(!$start_time){
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }

            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }
    }

    //规则83
    //2017-11-17 伊索 aesop
    static function rule83($number){
        $rule = '/^(([0-9]{2})[0-9|A-Z]{1}([0-9]{2})([0-9]{2})){1}$/'; //7位码
        preg_match($rule,$number,$res); 
        if($res){
            $year       = $res['4'];
            $month      = $res['3'];
            $day        = $res['2'];
            
            $year    = $year + 2000;
            $date_str = $year.$month.$day; 
            if($date_str >date('Ymd') && $date_str-10000 <=date('Ymd')){
                $year = $year - 10;
                $date_str = $year.$month.$day;
            }
            //计算生产日期
            $isDate = checkdate($month, $day, $year);
            if(!$isDate || $date_str >date('Ymd')){
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
            $start_time     = strtotime($year.'-'.$month.'-'.$day);
            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }
    }

    //规则84
    //2017-11-16 悦诗风吟 Innisfree  
    static function rule84($number){
        $rule = '/^(([0-9]{4})([0-9]{2})([0-9]{2})){1}$/'; //8位码
        preg_match($rule,$number,$res); 
        if($res){
            $day    = $res['4'];
            $month  = $res['3'];
            $year   = $res['2'];

            $date_str = $year.$month.$day; 
            if($date_str >date('Ymd') && $date_str-10000 <=date('Ymd')){
                $year = $year - 10;
                $date_str = $year.$month.$day;
            }
            //计算生产日期
            $isDate = checkdate($month, $day, $year);
            if(!$isDate || $date_str >date('Ymd')){
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
            $start_time     = strtotime($year.'-'.$month.'-'.$day);

            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }
    }

    //规则85
    /**
     * 2017-11-16 自然乐园 Nature Republic
     * 2017-12-04 奥莎迪 Oshadhi
     * 2017-12-04 嘉丝肤缇 Just BB
     */   
    static function rule85($number){
        $rule = '/^(([0-9]{4})([0-9]{2})([0-9]{2})){1}$/'; //8位码
        preg_match($rule,$number,$res); 
        if($res){
            $day    = $res['4'];
            $month  = $res['3'];
            $year   = $res['2'];

            if($year < 4 || $year > 2020){
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
            $year  = str_pad($year-3,4,'0',STR_PAD_LEFT);
            $date_str = $year.$month.$day; 
            if($date_str >date('Ymd') && $date_str-10000 <=date('Ymd')){
                $year = $year - 10;
                $date_str = $year.$month.$day;
            }
            //计算生产日期
            $isDate = checkdate($month, $day, $year);
            if(!$isDate || $date_str >date('Ymd')){
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
            $start_time     = strtotime($year.'-'.$month.'-'.$day);

            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            if (strlen($number) != 8) {
                return $return = ['status' => -1, 'msg' => '批号长度有误'];
            } else {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
        }
    }

    //规则86
    //2017-11-16 艾维诺 Aveeno
    static function rule86($number){
        $rule = '/^([0-9|A-Z]{3}([0-9]{3})([0-9]{1})[0-9|A-Z]{1}){1}$/'; //8位码
        preg_match($rule,$number,$res); 
        if($res){
            $day    = $res['2'];
            $year   = $res['3'];

            $year    = $year + 2010; 
            if($year > date('Y')){
                $year = $year - 10;
            }
            //计算生产日期
            $start_time = strtotime($year.'-01-00') + 24 * 3600 * $day;

            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }
    }

    //规则87
    /**
     * used 11-17 茱莉蔻 jurlique
     */
    static function rule87($number){
        $rule = '/^([A-Z|0-9]{2}([A-Z]{1})([1-9|O|N|D]{1})[0-9|A-Z]{2}){1}$/';         
        preg_match($rule,$number,$result); 

        if(!$result){
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }else{
            $year   = $result['2'];
            $month  = $result['3'];

            $yearArr= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'Q');
            if(!in_array($year,$yearArr)) return $return = ['status' => -1, 'msg' => '批号格式错误'];

            foreach ($yearArr as $key => $value) {
                if($year == $value) $year = $key + 2004;
            }

            if($year > date('Y')) $year = $year - 10;
            
            switch ($month) {
                case '<= 9':
                    $month = $month;
                    break;
                case 'O':
                    $month = 10;
                    break;
                case 'N':
                    $month = 11;
                    break;
                case 'D':
                    $month = 12;
                    break;
            }

            $month  = str_pad($month,2,'0',STR_PAD_LEFT);
            //计算生产日期

            $year   = $year.'-'.$month;
            $start_time = strtotime($year);
            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }
    }

    //规则88
    /**
     * used 11-20 帕玛氏 Palmer's
     */
    static function rule88($number){
        $rule = '/^([0-9|A-Z]{1}([0-9]{1})([0-9]{3})[0-9|A-Z]{1}){1}$/'; //6位码
        preg_match($rule,$number,$res); 
        if($res){
            $day    = $res['3'];
            $year   = $res['2'];

            $year    = $year + 2010; 
            if($year > date('Y')){
                $year = $year - 10;
            }
            //计算生产日期
            $start_time = strtotime($year.'-01-00') + 24 * 3600 * $day;

            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }
    }

    //规则89
    /**
     * used 11-20 THREE
     */
    static function rule89($number){
        $rule = '/^(([A-Z]{1})([1-9]{1})([0-9|A-X]{1})[0-9|A-Z]{2,3}){1}$/';  //5、6位码       
        preg_match($rule,$number,$result); 

        if(!$result){
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }else{
            $year   = $result['2'];
            $month  = $result['3'];
            $date  = $result['4']; 

            $yearArr= array('H', 'J', 'K','A', 'B', 'C', 'D', 'E', 'F', 'G');
            if(!in_array($year,$yearArr)) return $return = ['status' => -1, 'msg' => '批号格式错误'];

            foreach ($yearArr as $key => $value) {
                if($year == $value) $year = $key + 2008;
            }

            $notDateArr= array('I','O','Q');
            $dateArr= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K','L','M','N','P','R','S','T','U','V','W','X');
            if(in_array($date,$notDateArr)){
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }elseif(!is_numeric($date)){
                foreach ($dateArr as $k => $v) {
                    if($date == $v){
                        $date = $k + 11;break;
                    } 
                }
            }
            //计算生产日期

            $year   = $year.'-'.$month.'-'.$date;
            $start_time = strtotime($year);
            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }
    }

    //规则90
    /**
     * used 11-20 Alpha Hydrox
     */
    static function rule90($number){
        $rule = '/^(([0-9]{2})([A-Z]{1})([0-9]{1})[0-9|A-Z]{4}){1}$/';//8位码         
        preg_match($rule,$number,$result); 

        if(!$result){
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }else{
            $date  = $result['2']; 
            $month  = $result['3'];
            $year   = $result['4'];

            $year = $year + 2010;
            if($year > date('Y')) $year = $year - 10;
            
            $monthArr= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H','I', 'J', 'K','L');
            if(in_array($month,$monthArr)){
                foreach ($monthArr as $k => $v) {
                    if($month == $v){
                        $month = $k + 1;break;
                    } 
                }
            }else{
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
            //计算生产日期

            $year   = $year.'-'.$month.'-'.$date;
            $start_time = strtotime($year);
            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }
    }

    //规则91
    /**
     * used 11-20 宝格丽 Bulgari
     */
    static function rule91($number){
        $rule  = '/^(([0-9]{1})([A-Z]{1})[0-9|A-Z]{3}){1}$/';  //5位码     
        $rule1 = '/^(([0-3]{1})[0]{1}([A-H]{1})([1-9]{1})([1-9]{1})[0-9|A-Z]{3}){1}$/';  //8位码    
        preg_match($rule,$number,$result); 
        preg_match($rule1,$number,$result1);
        if($result){
            $year   = $result['2'];
            $month  = $result['3'];

            if($year < 3) {
                $year = $year + 2010;
            }else{
                $year = $year + 2000;
            }

            $monthArr1= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K','L','M');
            $monthArr2= array('N', 'P', 'Q', 'R', 'S', 'T', 'U', 'V','W', 'X', 'Y','Z');
            if(in_array($month,$monthArr1)){
                foreach ($monthArr1 as $k => $v) {
                    if($month == $v){
                        $month = $k + 1;break;
                    } 
                }
            }elseif(in_array($month,$monthArr2)){
                foreach ($monthArr2 as $k => $v) {
                    if($month == $v){
                        $month = $k + 1;break;
                    } 
                }
            }else{
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
            //计算生产日期

            $year   = $year.'-'.$month;
            $start_time = strtotime($year);
            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }elseif($result1){
            $date1  = $result1['2'];
            $year   = $result1['3'];
            $month  = $result1['4'];
            $date2  = $result1['5'];

            $yearArr = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H');
            foreach ($yearArr as $key => $value) {
                if($year == $value) $year = $key + 2010;
            }
            //计算生产日期
            $year   = $year.'-'.$month.'-'.$date1.$date2;
            $start_time = strtotime($year);
            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }
    }

    //规则92
    /**
     * used 11-20 ELF
     */
    static function rule92($number){
        $rule = '/^(([0-9]{1})([0-9]{2})([0-9]{2})){1}$/';//5位码
        preg_match($rule,$number,$result); 

        if(!$result){
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }else{
            $year      = $result['2'];
            $month     = $result['3'];
            $day       = $result['4'];
            $year = $year+2010;
            if($year > date('Y')) $year = $year - 10;

            //计算生产日期
            $isDate = checkdate($month, $day, $year);
            $date_str = $year.$month.$day; 
            if(!$isDate || $date_str >date('Ymd') || $year < 1000){
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
            $start_time     = strtotime($year.'-'.$month.'-'.$day);
            $startDay       = date('Y年m月d日',$start_time);
            $end_time       = strtotime('+3 year',$start_time);
            $endDay         = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }
    }

    //规则93
    /**
     * used 11-21 爱马仕 Hermes
     */
    static function rule93($number){
        $rule = '/^(([0-9]{1})([A-H|J-M]{1})[0-9|A-Z]{3}){1}$/';//5位码
        preg_match($rule,$number,$result);
    
        if(!$result){
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }else{
            $month     = $result['3'];
            $year      = $result['2'];
    
            $monArr= array('A'=>'01','B'=>'02', 'C'=>'03', 'D'=>'04', 'E'=>'05', 'F'=>'06',  'G'=>'07', 'H'=>'08','J'=>'09','K'=>'10','L'=>'11','M'=>'12');
            $is_month = isset($monArr[$month]) ? true : false;
            if(!$is_month) return $return = ['status' => -1, 'msg' => '批号格式错误'];

            $month  = $monArr[$month];
    
            $year    = $year + 2010;
            if($year > date('Y')) $year = $year - 10;

            $date_str = $year.$month.'01';
            if($date_str > date('Ymd') && $date_str-10000 <=date('Ymd')){
                $year = $year - 10;
            }
    
            //计算生产日期    
            $start_time     = strtotime($year.'-'.$month);
            $startDay       = date('Y年m月d日',$start_time);
            $end_time       = strtotime('+3 year',$start_time);
            $endDay         = date('Y年m月d日',$end_time);
    
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }
    }
    
    //规则94
    /**
     * used 11-22 奥伦纳素 Erno Laszlo
     */
    static function rule94($number){
        $rule = '/^(([A-L]{1})([0-9]{1})[0-9|A-Z]{3}){1}$/';//5位码
        preg_match($rule,$number,$result);
    
        if(!$result){
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }else{
            $month     = $result['2'];
            $year      = $result['3'];
    
            $monArr= array('A'=>'01','B'=>'02', 'C'=>'03', 'D'=>'04', 'E'=>'05', 'F'=>'06',  'G'=>'07', 'H'=>'08','I'=>'09','J'=>'10','K'=>'11','L'=>'12');
            $is_month = isset($monArr[$month]) ? true : false;
            if(!$is_month) return $return = ['status' => -1, 'msg' => '批号格式错误'];

            $month  = $monArr[$month];
    
            $year = $year+2010;
            if($year > date('Y')) $year = $year - 10;
            
            $date_str = $year.$month.'01';
            if($date_str >date('Ymd') && $date_str-10000 <=date('Ymd')){
                $year = $year - 10;
            }
    
            //计算生产日期    
            $start_time     = strtotime($year.'-'.$month);
            $startDay       = date('Y年m月d日',$start_time);
            $end_time       = strtotime('+3 year',$start_time);
            $endDay         = date('Y年m月d日',$end_time);
    
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }
    }
    
    //规则95
    /**
     * used 11-22 奇士美 Kiss Me
     */
    static function rule95($number){
        $rule = '/^([0-9|A-Z]{1}([0-9]{1})([1-9]{1})){1}$/';//3位码
        preg_match($rule,$number,$result);
    
        if(!$result){
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }else{
            $year      = $result['2'];
            $month     = $result['3'];
    
            $year = $year+2010;
            if($year > date('Y')) $year = $year - 10;
            
            $date_str = $year.$month.'01';
            if($date_str >date('Ymd') && $date_str-10000 <=date('Ymd')){
                $year = $year - 10;
            }
    
            //计算生产日期
            $start_time     = strtotime($year.'-'.$month);
            $startDay       = date('Y年m月d日',$start_time);
            $end_time       = strtotime('+3 year',$start_time);
            $endDay         = date('Y年m月d日',$end_time);
    
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }
    }
    
    //规则96
    /**
     * used 11-22 优色林 Eucerin
     */
    static function rule96($number){
        $rule = '/^(([0-9]{1})([0-9]{2})[0-9|A-Z]{5}){1}$/';//8位码
        preg_match($rule,$number,$result);
    
        if(!$result){
            if (strlen($number) != 8) {
                return $return = ['status' => -1, 'msg' => '批号长度有误'];
            } else {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
        }else{
            $year      = $result['2'];
            $date      = $result['3']*7+1;
            
            //天数不能为0
            if ($date < 1) {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
    
            $year = $year+2010;
            if($year > date('Y')) $year = $year - 10;
            $yearDate  = self::getYearDate($year);
            //不符合当年天数
            if ($date > $yearDate) {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
            $date_str = date('Ymd',strtotime($year.'-01-00') + 24 * 3600 * $date);
            if($date_str >date('Ymd') && $date_str-10000 <=date('Ymd')){
                $year  = $year - 10;
                
                //年份-10，再验证一遍
                $yearDate  = self::getYearDate($year);
                if ($date > $yearDate) {
                    return $return = ['status' => -1, 'msg' => '批号格式错误'];
                }
            }
    
            //计算生产日期
            $start_time   = strtotime($year.'-01-00') + 24 * 3600 * $date;
            $startDay     = date('Y年m月d日',$start_time);
            $end_time = strtotime('+3 year',$start_time);
            $endDay   = date('Y年m月d日',$end_time);
    
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }
    }
    
    //规则97
    /**
     * used 11-22 歌薇 GOLDWELL、花王 Kao、肤蕊 Freshel
     */
    static function rule97($number){
        $rule = '/^([0-9|A-Z]{4}([0-9]{3})([0-9]{1})){1}$/';//8位码
        preg_match($rule,$number,$result);
    
        if(!$result){
            if (strlen($number) != 8) {
                return $return = ['status' => -1, 'msg' => '批号长度有误'];
            } else {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
        }else{
            $year      = $result['3'];
            $date      = $result['2'];
            
            //天数不能为0
            if ($date < 1) {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
    
            $year = $year+2010;
            if($year > date('Y')) $year = $year - 10;
            $yearDate  = self::getYearDate($year);
            //不符合当年天数
            if ($date > $yearDate) {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
            $date_str = date('Ymd',strtotime($year.'-01-00') + 24 * 3600 * $date);
            if($date_str >date('Ymd') && $date_str-10000 <=date('Ymd')){
                $year  = $year - 10;
                
                //年份-10，再验证一遍
                $yearDate  = self::getYearDate($year);
                if ($date > $yearDate) {
                    return $return = ['status' => -1, 'msg' => '批号格式错误'];
                }
            }

            //计算生产日期
            $start_time   = strtotime($year.'-01-00') + 24 * 3600 * $date;
            $startDay     = date('Y年m月d日',$start_time);
            $end_time = strtotime('+3 year',$start_time);
            $endDay   = date('Y年m月d日',$end_time);

            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }
    }

    //规则98
    /**
     * used 11-22 海飞丝 Head & Shoulders
     * used 12-04 威娜 WELLA、封面女郎 Covergirl
     */
    static function rule98($number){
        $rule = '/^(([0-9]{1})([0-9]{3})[0-9|A-Z]{6}){1}$/';//10位码
        preg_match($rule,$number,$result);
    
        if(!$result){
            if (strlen($number) != 10) {
                return $return = ['status' => -1, 'msg' => '批号长度有误'];
            } else {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
        }else{
            $year      = $result['2'];
            $date      = $result['3'];
            
            //天数不能为0
            if ($date < 1) {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }

            $year    = $year + 2010; 
            if($year > date('Y')){
                $year = $year - 10;
            }
            $yearDate  = self::getYearDate($year);
            //不符合当年天数
            if ($date > $yearDate) {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }   
            $date_str = date('Ymd',strtotime($year.'-01-00') + 24 * 3600 * $date);
            if($date_str >date('Ymd') && $date_str-10000 <=date('Ymd')){
                $year  = $year - 10;
                
                //年份-10，再验证一遍
                $yearDate  = self::getYearDate($year);
                if ($date > $yearDate) {
                    return $return = ['status' => -1, 'msg' => '批号格式错误'];
                }
            }

            //计算生产日期
            $start_time   = strtotime($year.'-01-00') + 24 * 3600 * $date;
            $startDay     = date('Y年m月d日',$start_time);
            $end_time = strtotime('+3 year',$start_time);
            $endDay   = date('Y年m月d日',$end_time);
    
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }
    }

    //规则99
    /**
     * used 11-22 三宅一生 Issey Miyake
     */
    static function rule99($number){
        $rule = '/^(([S-Z]{1})([A|C|E|G|J|L|N|Q|S|U|W|Y]{1})[0-9|A-Z]{4}){1}$/';//6位码
        preg_match($rule,$number,$result);
    
        if(!$result){
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }else{
            $year      = $result['2'];
            $month       = $result['3'];

            $yearArr= array( 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
            foreach ($yearArr as $key => $value) {
                if($year == $value) $year = $key + 2010;
            }
            $monArr= array('A', 'C', 'E', 'G', 'J', 'L', 'N', 'Q', 'S', 'U', 'W','Y');
            foreach ($monArr as $k => $v) {
                if($month == $v) $month = $k + 1;
            }

            //计算生产日期
            $start_time     = strtotime($year.'-'.$month);
            $startDay       = date('Y年m月d日',$start_time);
            $end_time       = strtotime('+3 year',$start_time);
            $endDay         = date('Y年m月d日',$end_time);
    
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }
    }

    //规则100
    /**
     * used 11-22 法尔曼 VALMONT
     */
    static function rule100($number){
        $rule = '/^([0-9|A-Z]{2}([0-9]{1})([A-L]{1})[0-9|A-Z]{3}){1}$/';//7位码
        preg_match($rule,$number,$result);
    
        if(!$result){
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }else{
            $year      = $result['2'];
            $month       = $result['3'];

            $year = $year + 2010;
            if($year > date('Y')){
                $year = $year - 10;
            }

            $monthArr= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K','L');
            foreach ($monthArr as $k => $v) {
                if($month == $v) $month = $k + 1;
            }

            //计算生产日期
            $start_time     = strtotime($year.'-'.$month);
            $startDay       = date('Y年m月d日',$start_time);
            $end_time       = strtotime('+3 year',$start_time);
            $endDay         = date('Y年m月d日',$end_time);
    
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }
    }

    //规则101
    /**
     * used 11-22 旁氏 unileverusa
     */
    static function rule101($number){
        $rule = '/^([0]{1}([1-9]{1})([0-31]{2})([0-9]{1})[0-9|A-Z]{4}){1}$/';//9位码
        preg_match($rule,$number,$result);
    
        if(!$result){
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }else{
            $year      = $result['4'];
            $month       = $result['2'];
            $date       = $result['3'];

            $year = $year + 2010;
            if($year > date('Y')){
                $year = $year - 10;
            }

            $isDate = checkdate($month, $date, $year);
            $date_str = $year.$month.$date;
            if(!$isDate || $date_str >date('Ymd')){
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }

            //计算生产日期
            $start_time     = strtotime($year.'-'.$month.'-'.$date);
            $startDay       = date('Y年m月d日',$start_time);
            $end_time       = strtotime('+3 year',$start_time);
            $endDay         = date('Y年m月d日',$end_time);
    
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }
    }

    //规则102
    /**
     * used 11-22 A.H.C
     */
    static function rule102($number){
        $rule = '/^([0-9|A-Z]{1}([0-9]{1})([0-9]{2})([0-9]{2})){1}$/';//6位码
        $rule1 = '/^(([0-9]{4})([0-9]{2})([0-9]{2})){1}$/';//8位码

        preg_match($rule,$number,$result);
        preg_match($rule1,$number,$result1);
        if($result){
            $year      = $result['2'];
            $month     = $result['3'];
            $date      = $result['4'];

            $year = $year + 2007;

            $isDate = checkdate($month, $date, $year);
            $date_str = $year.$month.$date; 
            if(!$isDate || $date_str >date('Ymd')){
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }

            //计算生产日期
            $start_time     = strtotime($year.'-'.$month.'-'.$date);
            $startDay       = date('Y年m月d日',$start_time);
            $end_time       = strtotime('+3 year',$start_time);
            $endDay         = date('Y年m月d日',$end_time);
    
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }elseif($result1){
            $year      = $result1['2'];
            $month     = $result1['3'];
            $date      = $result1['4'];

            $isDate = checkdate($month, $date, $year);
            $date_str = $year.$month.$date; 
            if(!$isDate || $date_str >date('Ymd') || $year < 1000){
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }

            //计算生产日期
            $start_time     = strtotime($year.'-'.$month.'-'.$date);
            $startDay       = date('Y年m月d日',$start_time);
            $end_time       = strtotime('+3 year',$start_time);
            $endDay         = date('Y年m月d日',$end_time);
    
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            return $return = ['status' => -1, 'msg' => '批号格式错误'];

        }
    }

    //规则103
    /**
     * used 11-23 豆腐盛田屋  Tofu-moritaya
     */
    static function rule103($number){
        $rule = '/^(([0-9]{3})([0-9]{1})[0-9|A-Z]{2}){1}$/';//6位码
        preg_match($rule,$number,$result);
    
        if(!$result){
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }else{
            $year      = $result['3'];
            $day       = $result['2'];

            $year    = $year + 2010; 
            if($year > date('Y')){
                $year = $year - 10;
            }
            //计算生产日期
            $start_time = strtotime($year.'-01-00') + 24 * 3600 * $day;
            $startDay       = date('Y年m月d日',$start_time);
            $end_time       = strtotime('+3 year',$start_time);
            $endDay         = date('Y年m月d日',$end_time);
    
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }
    }
    
    //规则104
    /**
     * used 11-24 妙巴黎Bourjois
     */
    static function rule104($number){
        $rule = '/^((([0-9]{1})[0-9]{1})([A-Z|0-9]{2})){1}$/';//4位码
        preg_match($rule,$number,$result);
    
        if(!$result){
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }else{
            $yearArr = [
                '00' => '2015-12-01',
                '24' => '2009-12-01',
                '97' => '2016-01-01',
            ];
    
            if (array_key_exists($result['2'],$yearArr)) {
                $start_time     = strtotime($yearArr[$result['2']]);
                $startDay       = date('Y年m月d日',$start_time);
                $end_time       = strtotime('+3 year',$start_time);
                $endDay         = date('Y年m月d日',$end_time);
    
                return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
            }
    
            $groupNumber = $result['4'];
    
            $year      = $result['3'];
            $year      = $year + 2010;
            if($year > date('Y')) $year = $year - 10;
    
            if (is_numeric($groupNumber)){
                $date      = substr($result['1'],strlen($result['1'])-3,3);
                $yearDate  = (strtotime(($year+1).'-01-00') - strtotime($year.'-01-00'))/(24 * 3600);
            }
    
            if (is_numeric($groupNumber) && ($result['3'] == '6' || $result['3'] == '7') && $date <= $yearDate) {
                if ($result['1'] == '6000' || $result['1'] == '6001') {
                    $startDay       = '2012年12月01日';
                    $endDay         = '2015年12月01日';
    
                    return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
                } else {
                    $rule2 = '/^(([0-9]{1})([0-9]{3})){1}$/';
                    preg_match($rule2,$number,$result2);
    
                    $date       = $result2['3'];
    
                    //计算生产日期
                    $year           = $year.'-01-00';
                    $start_time     = strtotime($year) + 24 * 3600 * $date;
                    $date_str = date('Ymd',$start_time);
                    if($date_str >date('Ymd') && $date_str-10000 <=date('Ymd')){
                        $start_time  = strtotime('-10 year',$start_time);
                    }
                    $startDay       = date('Y年m月d日',$start_time);
                    $end_time       = strtotime('+3 year',$start_time);
                    $endDay         = date('Y年m月d日',$end_time);
    
                    return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
                }
    
                return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
            } else {
                switch($result['2'])
                {
                    case $result['2'] >= '01' && $result['2']<= '12':
                        $year = '2016';
                        $min = 1;
                        break;
                    case $result['2'] >= '13' && $result['2']<= '23':
                        $year = '2017';
                        $min = 13;
                        break;
                    case $result['2'] >= '25' && $result['2']<= '36':
                        $year = '2010';
                        $min = 25;
                        break;
                    case $result['2'] >= '37' && $result['2']<= '48':
                        $year = '2011';
                        $min = 37;
                        break;
                    case $result['2'] >= '49' && $result['2']<= '60':
                        $year = '2012';
                        $min = 49;
                        break;
                    case $result['2'] >= '61' && $result['2']<= '72':
                        $year = '2013';
                        $min = 61;
                        break;
                    case $result['2'] >= '73' && $result['2']<= '84':
                        $year = '2014';
                        $min = 73;
                        break;
                    case $result['2'] >= '85' && $result['2']<= '96':
                        $year = '2015';
                        $min = 85;
                        break;
                }
    
                $month = $result['2'];
                $month =  intval($result['2']) - $min + 1;
                $date_str = $year.$month.'1';
                if($date_str >date('Ymd')){
                    return $return = ['status' => -1, 'msg' => '批号格式错误'];
                }
    
                $start_time     = strtotime($year.'-'.$month);
                $startDay       = date('Y年m月d日',$start_time);
                $end_time       = strtotime('+3 year',$start_time);
                $endDay         = date('Y年m月d日',$end_time);
    
                return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
            }
        }
    }
    
    //规则105
    /**
     * used 11-30 香缇卡 Chantecaille
     */
    static function rule105($number){
        $rule = '/^(([A-Z]{1})([0-9]{1})[0-9|A-Z]{1,2}){1}$/'; //3、4位码
        preg_match($rule,$number,$res);
        if($res){
            $month  = $res['2'];
            $year   = $res['3'];
    
            $year = $year + 2010;
            if($year > date('Y')){
                $year = $year - 10;
            }
            $monthArr= array('A'=>'01','B'=>'02', 'C'=>'03', 'D'=>'04', 'E'=>'05', 'F'=>'06',  'G'=>'07', 'H'=>'08','I'=>'09','J'=>'10','K'=>'11','L'=>'12');
            $is_month = isset($monthArr[$month]) ? true : false;
            if(!$is_month) return $return = ['status' => -1, 'msg' => '批号格式错误'];
    
            $month  = $monthArr[$month];
    
            //计算生产日期
            $start_time = strtotime($year.'-'.$month.'-01');
            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);
    
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            return $return = ['status' => -1, 'msg' => '批号格式错误'];
        }
    }

    //规则106
    /**
     * used 2017-12-4 黑龙堂 Hipitch
     */
    static function rule106($number){
        $rule = '/^(([0-9]{1})([A-H|J-M]{1})([0-9]{2})[0-9|A-Z]{1}){1}$/';//5位码
        preg_match($rule,$number,$result);
    
        if($result){
            $month     = $result['3'];
            $year      = $result['2'];
            $date       = $result['4'];
            
            $monArr= array('A'=>'01','B'=>'02', 'C'=>'03', 'D'=>'04', 'E'=>'05', 'F'=>'06',  'G'=>'07', 'H'=>'08','J'=>'09','K'=>'10','L'=>'11','M'=>'12');
            $is_month = isset($monArr[$month]) ? true : false;
            if(!$is_month) return $return = ['status' => -1, 'msg' => '批号格式错误'];
            
            $month  = $monArr[$month];
            
            $year    = $year + 2010;
            if($year > date('Y')) $year = $year - 10;
            
            $date_str = $year.$month.$date;
            if($date_str > date('Ymd') && $date_str-10000 <=date('Ymd')){
                $year = $year - 10;
            }
            $isDate = checkdate($month, $date, $year);
            if(!$isDate){
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
            
            //计算生产日期
            $start_time     = strtotime($year.'-'.$month.'-'.$date);
            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);
            
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            if (strlen($number) != 5) {
                return $return = ['status' => -1, 'msg' => '批号长度有误'];
            } else {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
        }
    }
    
    //规则107
    /**
     * used 2017-12-4 丽丽贝尔 Lily Bell、所望 Somang
     */
    static function rule107($number){
        $rule = '/^(([0-9]{2})([0-9]{2})([0-9]{2})){1}$/';//6位码
        preg_match($rule,$number,$result);
    
        if($result){
            $month     = $result['3'];
            $year      = $result['2'];
            $date       = $result['4'];
            
            if ($year > 27) {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }

            $year = $year + 2000;
            if($year > date('Y')) $year = $year - 10;
    
            $date_str = $year.$month.$date;
            if($date_str > date('Ymd') && $date_str-10000 <=date('Ymd')){
                $year = $year - 10;
            }
            $isDate = checkdate($month, $date, $year);
            if(!$isDate){
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
    
            //计算生产日期
            $start_time     = strtotime($year.'-'.$month.'-'.$date);
            $startDay   = date('Y年m月d日',$start_time);
            $end_time   = strtotime('+3 year',$start_time);
            $endDay     = date('Y年m月d日',$end_time);
    
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            if (strlen($number) != 6) {
                return $return = ['status' => -1, 'msg' => '批号长度有误'];
            } else {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
        }
    }
    
    //规则108
    /**
     * used 2017-12-4 露韩饰 Lohashill
     */
    static function rule108($number){
        $rule = '/^(([0-9]{4})([0-9]{2})([0-9]{2})){1}$/';//6位码
        preg_match($rule,$number,$result);
    
        if($result){
            $month     = $result['3'];
            $year      = $result['2'];
            $day       = $result['4'];
    
            if($year > date('Y')) $year = $year - 10;
    
            $date_str = $year.$month.$day;
            if($date_str > date('Ymd') && $date_str-10000 <=date('Ymd')){
                $year = $year - 10;
            }
            $isDate = checkdate($month, $day, $year);
            if(!$isDate || $date_str >date('Ymd')){
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }

            //计算生产日期
            $startDay = $year.'年'.$month.'月'.$day.'日';
            $endDay = ($year+3).'年'.$month.'月'.$day.'日';
    
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            if (strlen($number) != 6) {
                return $return = ['status' => -1, 'msg' => '批号长度有误'];
            } else {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
        }
    }
    
    //规则109
    /**
     * used 2017-12-4 RMK、苏菲娜 sofina
     */
    static function rule109($number){
        $rule  = '/^(([0-9]{3})([0-9]{1})){1}$/';//4位码
        $rule1  = '/^(([0-9|A-Z]{4})([0-9]{3})([0-9]{1})){1}$/';//8位码

        preg_match($rule,$number,$result);
        preg_match($rule1,$number,$result1);

        if ($result) {
            $year      = $result['3'];
            $date      = $result['2'];
            
            //天数不能为0
            if ($date < 1) {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
    
            $year = $year+2010;
            if($year > date('Y')) $year = $year - 10;
            $yearDate  = self::getYearDate($year);
            //不符合当年天数
            if ($date > $yearDate) {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
            $date_str = date('Ymd',strtotime($year.'-01-00') + 24 * 3600 * $date);
            if($date_str >date('Ymd') && $date_str-10000 <=date('Ymd')){
                $year  = $year - 10;
                
                //年份-10，再验证一遍
                $yearDate  = self::getYearDate($year);
                if ($date > $yearDate) {
                    return $return = ['status' => -1, 'msg' => '批号格式错误'];
                }
            }

            //计算生产日期
            $start_time   = strtotime($year.'-01-00') + 24 * 3600 * $date;
            $startDay     = date('Y年m月d日',$start_time);
            $end_time = strtotime('+3 year',$start_time);
            $endDay   = date('Y年m月d日',$end_time);
    
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        } elseif ($result1) {
            $year      = $result1['4'];
            $date      = $result1['3'];
            
            //天数不能为0
            if ($date < 1) {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
    
            $year = $year+2010;
            if($year > date('Y')) $year = $year - 10;
            $yearDate  = self::getYearDate($year);
            //不符合当年天数
            if ($date > $yearDate) {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
            $date_str = date('Ymd',strtotime($year.'-01-00') + 24 * 3600 * $date);
            if($date_str >date('Ymd') && $date_str-10000 <=date('Ymd')){
                $year  = $year - 10;
                
                //年份-10，再验证一遍
                $yearDate  = self::getYearDate($year);
                if ($date > $yearDate) {
                    return $return = ['status' => -1, 'msg' => '批号格式错误'];
                }
            }

            //计算生产日期
            $start_time   = strtotime($year.'-01-00') + 24 * 3600 * $date;
            $startDay     = date('Y年m月d日',$start_time);
            $end_time = strtotime('+3 year',$start_time);
            $endDay   = date('Y年m月d日',$end_time);
            
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        } else {
            $ruleArr = ['4','8'];
            if (!in_array(strlen($number), $ruleArr)) {
                return $return = ['status' => -1, 'msg' => '批号长度有误'];
            } else {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
        }
    }
    
    //规则110
    /**
     * used 2017-12-4 媛色 EST
     */
    static function rule110($number){
        $rule  = '/^(([0-9]{3})([0-9]{1})){1}$/';//4位码
    
        preg_match($rule,$number,$result);
    
        if ($result) {
            $year      = $result['3'];
            $date      = $result['2'];
            
            //天数不能为0
            if ($date < 1) {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }

            $year = $year+2010;
            if($year > date('Y')) $year = $year - 10;
            $yearDate  = self::getYearDate($year);
            //不符合当年天数
            if ($date > $yearDate) {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
            $date_str = date('Ymd',strtotime($year.'-01-00') + 24 * 3600 * $date);
            if($date_str >date('Ymd') && $date_str-10000 <=date('Ymd')){
                $year  = $year - 10;
                
                //年份-10，再验证一遍
                $yearDate  = self::getYearDate($year);
                if ($date > $yearDate) {
                    return $return = ['status' => -1, 'msg' => '批号格式错误'];
                }
            }
    
            //计算生产日期
            $start_time   = strtotime($year.'-01-00') + 24 * 3600 * $date;
            $startDay     = date('Y年m月d日',$start_time);
            $end_time = strtotime('+3 year',$start_time);
            $endDay   = date('Y年m月d日',$end_time);
    
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        } else {
            if (strlen($number) != 4) {
                return $return = ['status' => -1, 'msg' => '批号长度有误'];
            } else {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
        }
    }
    
    //规则111
    /**
     * used 2017-12-05 加州宝宝 California Baby
     */
    static function rule111($number){
        $rule = '/^([0-9|A-Z]{2}([0-9]{1})([0-9]{3})[0-9|A-Z]{2}){1}$/';//8位码
        preg_match($rule,$number,$result);
    
        if(!$result){
            if (strlen($number) != 8) {
                return $return = ['status' => -1, 'msg' => '批号长度有误'];
            } else {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
        }else{
            $year      = $result['2'];
            $date      = $result['3'];
            
            //天数不能为0
            if ($date < 1) {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
    
            $year = $year+2010;
            if($year > date('Y')) $year = $year - 10;
            $yearDate  = self::getYearDate($year);
            //不符合当年天数
            if ($date > $yearDate) {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
            $date_str = date('Ymd',strtotime($year.'-01-00') + 24 * 3600 * $date);
            if($date_str >date('Ymd') && $date_str-10000 <=date('Ymd')){
                $year  = $year - 10;
                
                //年份-10，再验证一遍
                $yearDate  = self::getYearDate($year);
                if ($date > $yearDate) {
                    return $return = ['status' => -1, 'msg' => '批号格式错误'];
                }
            }
    
            //计算生产日期
            $start_time   = strtotime($year.'-01-00') + 24 * 3600 * $date;
            $startDay     = date('Y年m月d日',$start_time);
            $end_time = strtotime('+3 year',$start_time);
            $endDay   = date('Y年m月d日',$end_time);
    
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }
    }
    
    //规则112
    /**
     * used 2017-12-4 百蕾适 Blistex
     */
    static function rule112($number){
        $rule = '/^(([0-9]{2})([0-9]{2})[0-9|A-Z]{2}){1}$/';//6位码
        preg_match($rule,$number,$result);
    
        if($result){
            $month     = $result['2'];
            $year      = $result['3'];
            $day       = '01';
            
            if ($year > 27) {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
    
            $year = $year + 2000;
            if($year > date('Y')) $year = $year - 10;
            
            $date_str = $year.$month.$day;
            if($date_str >date('Ymd') && $date_str-10000 <=date('Ymd')){
                $year  = $year - 10;
            }
    
            $isDate = checkdate($month, $day, $year);
            if(!$isDate){
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
    
            //计算生产日期
            $startDay = $year.'年'.$month.'月'.$day.'日';
            $endDay = ($year+3).'年'.$month.'月'.$day.'日';
    
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            if (strlen($number) != 6) {
                return $return = ['status' => -1, 'msg' => '批号长度有误'];
            } else {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
        }
    }
    
    //规则113
    /**
     * used 2017-12-4 瑰珀翠 Crabtree & Evelyn
     */
    static function rule113($number){
        $rule = '/^(([0-9]{1})([0-9]{3})[0-9|A-Z]{0,1}){1}$/'; //4、5位码
        preg_match($rule,$number,$res);
        if($res){
            $year       = $res['2'];
            $date        = $res['3'];
            
            //天数不能为0
            if ($date < 1) {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
    
            $year    = $year + 2010;
            if($year > date('Y')){
                $year = $year - 10;
            }
            $yearDate  = self::getYearDate($year);
            //不符合当年天数
            if ($date > $yearDate) {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
            $date_str = date('Ymd',strtotime($year.'-01-00') + 24 * 3600 * $date);
            if($date_str >date('Ymd') && $date_str-10000 <=date('Ymd')){
                $year  = $year - 10;
                
                //年份-10，再验证一遍
                $yearDate  = self::getYearDate($year);
                if ($date > $yearDate) {
                    return $return = ['status' => -1, 'msg' => '批号格式错误'];
                }
            }

            //计算生产日期
            $start_time   = strtotime($year.'-01-00') + 24 * 3600 * $date;
            $startDay     = date('Y年m月d日',$start_time);
            $end_time = strtotime('+3 year',$start_time);
            $endDay   = date('Y年m月d日',$end_time);
    
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            $ruleArr = ['4','5'];
            if (!in_array(strlen($number), $ruleArr)) {
                return $return = ['status' => -1, 'msg' => '批号长度有误'];
            } else {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
        }
    }
    
    //规则114
    /**
     * used 2017-12-05 倩丽 CEZANNE
     */
    static function rule114($number){
        $rule = '/^(([0-9]{1})([0-9]{2})[0-9|A-Z]{1}){1}$/'; //4位码
        preg_match($rule,$number,$res);
        if($res){
            $year       = $res['2'];
            $month        = $res['3'];
            $day       = '01';
    
            $year = $year + 2010;
            if($year > date('Y')) $year = $year - 10;
    
            $isDate = checkdate($month, $day, $year);
            if(!$isDate){
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
    
            //计算生产日期
            $startDay = $year.'年'.$month.'月'.$day.'日';
            $endDay = ($year+3).'年'.$month.'月'.$day.'日';
    
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            if (strlen($number) != 4) {
                return $return = ['status' => -1, 'msg' => '批号长度有误'];
            } else {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
        }
    }
    
    //规则115
    /**
     * used 2017-12-06 可伶可俐 Clean & Clear
     */
    static function rule115($number){
        $rule = '/^(([0-9]{3})([0-9]{1})[0-9|A-Z]{2}){1}$/'; //6位码
        preg_match($rule,$number,$res);
        if($res){
            $year       = $res['3'];
            $date        = $res['2'];
            
            //天数不能为0
            if ($date < 1) {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
    
            $year = $year + 2010;
            if($year > date('Y')) $year = $year - 10;
            $yearDate  = self::getYearDate($year);
            //不符合当年天数
            if ($date > $yearDate) {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
            $date_str = date('Ymd',strtotime($year.'-01-00') + 24 * 3600 * $date);
            if($date_str >date('Ymd') && $date_str-10000 <=date('Ymd')){
                $year  = $year - 10;
                
                //年份-10，再验证一遍
                $yearDate  = self::getYearDate($year);
                if ($date > $yearDate) {
                    return $return = ['status' => -1, 'msg' => '批号格式错误'];
                }
            }
    
            //计算生产日期
            $start_time   = strtotime($year.'-01-00') + 24 * 3600 * $date;
            $startDay     = date('Y年m月d日',$start_time);
            $end_time = strtotime('+3 year',$start_time);
            $endDay   = date('Y年m月d日',$end_time);
    
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            if (strlen($number) != 6) {
                return $return = ['status' => -1, 'msg' => '批号长度有误'];
            } else {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
        }
    }
    
    //规则116
    /**
     * used 2017-12-06 芬迪 Fendi
     */
    static function rule116($number){
        $rule = '/^(([0-9]{1})([A-M]{1})[0-9|A-Z]{2}){1}$/'; //4位码
        preg_match($rule,$number,$res);
        if($res){
            $year       = $res['2'];
            $month        = $res['3'];
    
            $monArr= array('A'=>'01','B'=>'02', 'C'=>'03', 'D'=>'04', 'E'=>'05', 'F'=>'06',  'G'=>'07', 'H'=>'08','J'=>'09','K'=>'10','L'=>'11','M'=>'12');
            $is_month = isset($monArr[$month]) ? true : false;
            if(!$is_month) return $return = ['status' => -1, 'msg' => '批号格式错误'];
            
            $month  = $monArr[$month];
    
            $year = $year + 2010;
            if($year > date('Y')) $year = $year - 10;
            
            $date_str = $year.$month.'01';
            if($date_str >date('Ymd') && $date_str-10000 <=date('Ymd')){
                $year  = $year - 10;
            }
    
            //计算生产日期
            $start_time     = strtotime($year.'-'.$month.'-01');
            $startDay     = date('Y年m月d日',$start_time);
            $end_time = strtotime('+3 year',$start_time);
            $endDay   = date('Y年m月d日',$end_time);
    
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            if (strlen($number) != 4) {
                return $return = ['status' => -1, 'msg' => '批号长度有误'];
            } else {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
        }
    }
    
    //规则117
    /**
     * used 2017-12-06 菲拉格慕 Ferragamo
     */
    static function rule117($number){
        $rule = '/^([0-9|A-Z]{1}([0-9]{1})([0-9]{1})([0-9]{1})([0-9]{1})([0-9]{1})[0-9|A-Z]{1}){1}$/'; //7位码
        preg_match($rule,$number,$res);
        if($res){
            $year       = $res['4'];
            $month      = $res['3'].$res['5'];
            $day        = $res['2'].$res['6'];
    
            $year = $year + 2010;
            if($year > date('Y')) $year = $year - 10;
            
            $isDate = checkdate($month, $day, $year);
            if(!$isDate){
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
            
            $date_str = $year.$month.$day; 
            if($date_str >date('Ymd') && $date_str-10000 <=date('Ymd')){
                $year  = $year - 10;
            }  
            
            //计算生产日期
            $start_time     = strtotime($year.'-'.$month.'-'.$day);
            $startDay     = date('Y年m月d日',$start_time);
            $end_time = strtotime('+3 year',$start_time);
            $endDay   = date('Y年m月d日',$end_time);
    
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            if (strlen($number) != 7) {
                return $return = ['status' => -1, 'msg' => '批号长度有误'];
            } else {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
        }
    }
    
    //规则118
    /**
     * used 2017-12-06 艾丽美 Elemis
     */
    static function rule118($number){
        $rule = '/^([0-9|A-Z]{3}([0-9]{3})([0-9]{1})){1}$/'; //7位码
        preg_match($rule,$number,$res);
        if($res){
            $year       = $res['3'];
            $date       = $res['2'];
            
            //天数不能为0
            if ($date < 1) {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
    
            $year = $year + 2010;
            if($year > date('Y')) $year = $year - 10;
    
            $yearDate  = self::getYearDate($year);
            //不符合当年天数
            if ($date > $yearDate) {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }   
            $date_str = date('Ymd',strtotime($year.'-01-00') + 24 * 3600 * $date);
            if($date_str >date('Ymd') && $date_str-10000 <=date('Ymd')){
                $year  = $year - 10;
                
                //年份-10，再验证一遍
                $yearDate  = self::getYearDate($year);
                if ($date > $yearDate) {
                    return $return = ['status' => -1, 'msg' => '批号格式错误'];
                }
            }
            
            //计算生产日期
            $start_time   = strtotime($year.'-01-00') + 24 * 3600 * $date;
            $startDay     = date('Y年m月d日',$start_time);
            $end_time = strtotime('+3 year',$start_time);
            $endDay   = date('Y年m月d日',$end_time);
    
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            if (strlen($number) != 7) {
                return $return = ['status' => -1, 'msg' => '批号长度有误'];
            } else {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
        }
    }

    //规则119
    /**
     * used 2017-12-07 宠爱之名 FOR BELOVED ONE
     */
    static function rule119($number){
        $rule = '/^(([0-9]{1})([A-Z]{1})[0-9|A-Z]{2}){1}$/'; //4位码
        preg_match($rule,$number,$res);
        
        if($res){
            $year      = $res['2'];
            $month     = $res['3'];
            
            if ($year < 3) {
                $year = $year + 2010;
            } else {
                $year = $year + 2000;
            }
            
            $monArr1= array('A'=>'01','B'=>'02', 'C'=>'03', 'D'=>'04', 'E'=>'05', 'F'=>'06', 'G'=>'07', 'H'=>'08','J'=>'09','K'=>'10','L'=>'11','M'=>'12');
            $monArr2= array('N'=>'01','P'=>'02', 'Q'=>'03', 'R'=>'04', 'S'=>'05', 'T'=>'06', 'U'=>'07', 'V'=>'08','W'=>'09','X'=>'10','Y'=>'11','Z'=>'12');
            if (isset($monArr1[$month])) {
                $month  = $monArr1[$month];
            } elseif (isset($monArr2[$month])) {
                $month  = $monArr2[$month];
            } else {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
            
            $date_str = $year.$month.'01';
            if($date_str >date('Ymd') && $date_str-10000 <=date('Ymd')){
                $year  = $year - 10;
            }
            
            //计算生产日期
            $startDay = $year.'年'.$month.'月'.'01日';
            $endDay = ($year+3).'年'.$month.'月'.'01日';
            
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        } else {
            if (strlen($number) != 4) {
                return $return = ['status' => -1, 'msg' => '批号长度有误'];
            } else {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
            
        }
    }
    
    //规则120
    /**
     * used 2017-12-07 塔莉卡 TALIKA
     */
    static function rule120($number){
        $rule = '/^(([A-N]{1})([0-9]{3})[0-9|A-Z]{1}){1}$/'; //5位码
        preg_match($rule,$number,$res);

        if($res){
            $year      = $res['2'];
            $date      = $res['3'];
            
            if ($date < 1) {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }

            $yearArr= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H','I', 'J', 'K', 'L', 'M', 'N');
            if (in_array($year, $yearArr)) {
                foreach ($yearArr as $key=>$val) {
                    if ($year == $val) {
                        $year = $key + 2004;
                    }
                }
            } else {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
            
//             if($year > date('Y')){
//                 $year = $year - 10;
//             }
            $yearDate  = self::getYearDate($year);
            //不符合当年天数
            if ($date > $yearDate) {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
            $date_str = date('Ymd',strtotime($year.'-01-00') + 24 * 3600 * $date);
            if($date_str >date('Ymd') && $date_str-10000 <=date('Ymd')){
                $year  = $year - 10;
            }
            //年份可能-10，再验证一遍
            $yearDate  = self::getYearDate($year);
            if ($date > $yearDate) {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
            
            //计算生产日期
            $start_time   = strtotime($year.'-01-00') + 24 * 3600 * $date;
            $startDay     = date('Y年m月d日',$start_time);
            $end_time = strtotime('+3 year',$start_time);
            $endDay   = date('Y年m月d日',$end_time);
    
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        } else {
            if (strlen($number) != 5) {
                return $return = ['status' => -1, 'msg' => '批号长度有误'];
            } else {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
    
        }
    }
    
    //规则121
    /**
     * used 2017-12-07 卡鲁 kaloo
     */
    static function rule121($number){
        $rule = '/^(([0-9]{3})[0-9|A-Z]{1}([0-9]{2})){1}$/'; //6位码
        preg_match($rule,$number,$res);
        if($res){
            $year       = $res['3'];
            $date        = $res['2'];
    
            //天数不能为0
            if ($date < 1) {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
            
            if ($year > 26) {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
    
            $year = $year + 2000;
            if($year > date('Y')) $year = $year - 10;
            $yearDate  = self::getYearDate($year);
            //不符合当年天数
            if ($date > $yearDate) {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
            $date_str = date('Ymd',strtotime($year.'-01-00') + 24 * 3600 * $date);
            if($date_str >date('Ymd') && $date_str-10000 <=date('Ymd')){
                $year  = $year - 10;
    
                //年份-10，再验证一遍
                $yearDate  = self::getYearDate($year);
                if ($date > $yearDate) {
                    return $return = ['status' => -1, 'msg' => '批号格式错误'];
                }
            }
    
            //计算生产日期
            $start_time   = strtotime($year.'-01-00') + 24 * 3600 * $date;
            $startDay     = date('Y年m月d日',$start_time);
            $end_time = strtotime('+3 year',$start_time);
            $endDay   = date('Y年m月d日',$end_time);
    
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        }else{
            if (strlen($number) != 6) {
                return $return = ['status' => -1, 'msg' => '批号长度有误'];
            } else {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
        }
    }
    
    //规则122
    /**
     * used 2017-12-07 玫珂菲 Makeup Forever
     */
    static function rule122($number){
        $rule = '/^(([0-9]{1})([N|P-Z]{1})[0-9]{2}){1}$/'; //4位码        
        $rule2 = '/^(([L-X]{1})([A-L]{1})[0-9|A-Z]{3}){1}$/'; //5位码
        $rule3 = '/^(([0-9|Q|R|S]{1})([A-M]{1})[0-9|A-Z]{2}){1}$/'; //4位码
        
        preg_match($rule,$number,$result); 
        preg_match($rule2,$number,$result2);
        preg_match($rule3,$number,$result3);

        if($result){
            $year   = $result['2'] + 2010;
            $month  = $result['3'];
            
            $monthArr   = array('N','P','Q','R','S','T','U','V','W','X','Y','Z');
            foreach ($monthArr as $key => $value) {
                if($month == $value) $month = $key + 1;
            }
            
            $month  = str_pad($month,2,'0',STR_PAD_LEFT);
            
            $date_str = $year.$month.'01';
            if($date_str >date('Ymd') && $date_str-10000 <=date('Ymd')){
                $year  = $year - 10;
            }
            
            //计算生产日期
            $start_time = strtotime($year.'-'.$month);
            $end_time   = strtotime('+3 year',$start_time);
            $startDay   = date('Y年m月d日',$start_time);
            $endDay     = date('Y年m月d日',$end_time);
            
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        } elseif ($result2) {
            $year  = $result2['2'];
            $month = $result2['3'];
            
            $yearArr= array('L', 'M', 'N', 'P', 'Q', 'R', 'S', 'T','U', 'V', 'W', 'X');
            if (in_array($year, $yearArr)) {
                foreach ($yearArr as $key=>$val) {
                    if ($year == $val) {
                        $year = $key + 2006;
                    }
                }
            } else {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
            
            $monArr= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H','I', 'J', 'K', 'L');
            if (in_array($month, $monArr)) {
                foreach ($monArr as $key=>$val) {
                    if ($month == $val) {
                        $month = $key + 1;
                    }
                }
                $month  = str_pad($month,2,'0',STR_PAD_LEFT);
            } else {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
            
            $date_str = $year.$month.'01';
            if($date_str >date('Ymd') && $date_str-10000 <=date('Ymd')){
                $start_time  = strtotime('-10 year',$start_time);
            }
            
            //计算生产日期
            $startDay = $year.'年'.$month.'月'.'01日';
            $endDay = ($year+3).'年'.$month.'月'.'01日';
            
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        } elseif ($result3) {
            $year  = $result3['2'];
            $month = $result3['3'];
            
            $monArr= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M');
            if (in_array($month, $monArr)) {
                foreach ($monArr as $key=>$val) {
                    if ($month == $val) {
                        $month = $key + 1;
                    }
                }
                $month  = str_pad($month,2,'0',STR_PAD_LEFT);
            } else {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }

            if (is_numeric($year)) {
                if ($year>=0 && $year<=2) $year = $year + 2010;
                if ($year>=3 && $year<=9) $year = $year + 2000;
            } else {
                switch ($year) {
                    case 'Q':
                        $year = 2010;
                        break;
                    case 'R':
                        $year = 2011;
                        break;
                    case 'S':
                        $year = 2012;
                        break;
                    default:
                        return $return = ['status' => -1, 'msg' => '批号格式错误'];
                }
            }
            
//             $date_str = $year.$month.'01';
//             if($date_str >date('Ymd') && $date_str-10000 <=date('Ymd')){
//                 $start_time  = strtotime('-10 year',$start_time);
//             }
            
            //计算生产日期
            $startDay = $year.'年'.$month.'月'.'01日';
            $endDay = ($year+3).'年'.$month.'月'.'01日';
            
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];            
        } else {
            $ruleArr = ['4','5'];
            if (!in_array(strlen($number), $ruleArr)) {
                return $return = ['status' => -1, 'msg' => '批号长度有误'];
            } else {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
            
        }
    }
    
    //规则123
    /**
     * used 2017-12-07 拉尔夫劳伦 Ralph Lauren
     */
    static function rule123($number){
        $rule = '/^([0-9|A-Z]{1}([A-Y]{1})([0-9]{3})){1}$/'; //5位码
        $rule2 = '/^([0-9|A-Z]{2}([A-Z]{1})([1-9|O|N|D]{1})[0-9|A-Z]{2}){1}$/'; //6位码
        
        preg_match($rule,$number,$res);
        preg_match($rule2,$number,$res2);
        
        if($res){
            $year       = $res['2'];
            $date       = $res['3'];
    
            //天数不能为0
            if ($date < 1) {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
    
            $yearArr= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', /*'Q', 'R', 'S', 'T','U', 'V', 'W', 'X', 'Y' */);
            if (in_array($year, $yearArr)) {
                foreach ($yearArr as $key=>$val) {
                    if ($year == $val) {
                        $year = $key + 2004;
                    }
                }
            } else {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
            
            $yearDate  = self::getYearDate($year);
            //不符合当年天数
            if ($date > $yearDate) {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
            $date_str = date('Ymd',strtotime($year.'-01-00') + 24 * 3600 * $date);
            if($date_str >date('Ymd') && $date_str-10000 <=date('Ymd')){
                $year  = $year - 10;
    
                //年份-10，再验证一遍
                $yearDate  = self::getYearDate($year);
                if ($date > $yearDate) {
                    return $return = ['status' => -1, 'msg' => '批号格式错误'];
                }
            }
    
            //计算生产日期
            $start_time   = strtotime($year.'-01-00') + 24 * 3600 * $date;
            $startDay     = date('Y年m月d日',$start_time);
            $end_time = strtotime('+3 year',$start_time);
            $endDay   = date('Y年m月d日',$end_time);
    
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];
        } elseif ($res2) {
            $year       = $res2['2'];
            $month      = $res2['3'];
            
            $yearArr= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', /*'Q', 'R', 'S', 'T','U', 'V', 'W', 'X', 'Y' */);
            if (in_array($year, $yearArr)) {
                foreach ($yearArr as $key=>$val) {
                    if ($year == $val) {
                        $year = $key + 2004;
                    }
                }
            } else {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
            
            if (is_numeric($month)) {
                $month = '0'.$month;
            } else {
                switch ($month) {
                    case 'O':
                        $month = 10;
                        break;
                    case 'N':
                        $month = 11;
                        break;
                    case 'D':
                        $month = 12;
                        break;
                    default:
                        return $return = ['status' => -1, 'msg' => '批号格式错误'];
                }
            }
            
            $date_str = $year.$month.'01';
            if($date_str >date('Ymd') && $date_str-10000 <=date('Ymd')){
                $year  = $year - 10;
            }      
            
            //计算生产日期
            $startDay = $year.'年'.$month.'月'.'01日';
            $endDay = ($year+3).'年'.$month.'月'.'01日';
            
            return $return = ['status' => 1, 'startDay' => $startDay, 'endDay'=>$endDay];           
        } else{
            $ruleArr = ['5','6'];
            if (!in_array(strlen($number), $ruleArr)) {
                return $return = ['status' => -1, 'msg' => '批号长度有误'];
            } else {
                return $return = ['status' => -1, 'msg' => '批号格式错误'];
            }
        }
    }
    
    
    
}