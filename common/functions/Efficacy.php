<?php

/**
 * 产品功效
 */

namespace common\functions;
use Yii;


class Efficacy {

    /**
     * 获取功效列表
     *
     * @param int $userId 用户ID
     * @return string 
     */
    PUBLIC STATIC function getEfficacyList($component_list,$cate_name)
    {
        $relate = self::getFunctionalComponent();

        $function_list =  self::getShowRule($cate_name);
        $safe_list = ['香精' => [], '防腐剂' => [], '风险' => [], '孕妇慎用' => [],];

        foreach($component_list as $value){

            $component_name = $value['name'];
            $component_id = $value['id'];

            //洁面、洗护分类 特殊处理（成分名匹配）
            if($cate_name == "洁面" || $cate_name == "洗护"){
                $componentArr = ['椰油酰基谷氨酸 TEA 盐','氨酸钠','氨酸钾','牛磺酸钠'];
                foreach ($componentArr as $k1 => $v1) {
                    if(preg_match("/$v1/",$component_name)){
                        $function_list['氨基酸表活'][$component_id] = $component_name;
                    }
                }
                if(preg_match('/硫酸酯钠/',$component_name) ){
                    $function_list['sls/sles'][$component_id] = $component_name;
                }
            }
            //孕妇慎用
            $componentArr = ['视黄醇','水杨酸','羟苯异丁酯','甲氧基肉桂酸乙基己酯'];
            foreach ($componentArr as $k2 => $v2) {
                if(preg_match("/$v2/",$component_name)){
                    $safe_list['孕妇慎用'][$component_id] = $component_name;
                }
            }
            
            if($value['risk_grade'] > 6){
                $safe_list['风险'][$component_id] = $component_name;
            }

            //使用目的匹配
            $row = explode("，",$value['component_action']);
            foreach($row as $val){
                //功效列表
                if(isset($relate['effect'][$val])){
                    if(isset($function_list[ $relate['effect'][$val] ])){
                        $function_list[$relate['effect'][$val]][$component_id] = $component_name;
                    }
                }

                //安全性列表
                if(isset($relate['safe'][$val])){
                    $safe_list[$relate['safe'][$val]][$component_id] = $component_name;
                }

                if(preg_match('/控油/',$val) && isset($function_list['控油']) ){
                    $function_list['控油'][$component_id] = $component_name;
                }

                if(preg_match('/舒缓抗敏/',$val) && isset($function_list['舒缓']) ){
                    $function_list['舒缓'][$component_id] = $component_name;
                }

                //香精
                if(preg_match("/香精/",$val)){
                    $safe_list['香精'][$component_id] = $component_name;
                }

//                if(preg_match('/保湿/',$val,$match) || preg_match('/抗氧化/',$val,$match) || preg_match('/控油/',$val,$match) || preg_match('/清洁/',$val,$match) || preg_match('/物理防晒/',$val,$match)
//                    || preg_match('/化学防晒/',$val,$match) || preg_match('/舒缓/',$val,$match) || preg_match('/美白/',$val,$match)){
//
//                    if(isset($function_list[ $match[0] ])) {
//                        $function_list[ $match[0] ]++;
//                    }
//                }
//
//                if(preg_match('/香精/',$val,$match) || preg_match('/防腐剂/',$val,$match)){
//                    $safe_list[ $match[0] ]++;
//                }

            }
        }

        $data = array(
            'function_list' => $function_list,
            'safe_list' => $safe_list,
        );

        return $data;
    }

    //产品分类功效展示规则表
    static function getShowRule($cate_name){

        $list = array(
            '洁面' => '清洁，氨基酸表活，sls/sles',
            '洗护' => '清洁，氨基酸表活，sls/sles',
            '化妆水' => '保湿，抗氧化，美白，舒缓，控油',
            '精华' => '保湿，抗氧化，美白，舒缓，控油',
            '乳霜' => '保湿，抗氧化，美白，舒缓，控油',
            '眼霜' => '保湿，抗氧化，美白，舒缓，控油',
            '面膜' => '保湿，抗氧化，美白，舒缓，控油',
            '防晒' => '物理防晒，化学防晒',
        );

        $functional = "";
        foreach($list as $val){
            if(isset($list[$cate_name])){
                $functional = $list[$cate_name];
            }
        }

        $result = array();
        if($functional){
            $functional = explode("，",$functional);
            foreach($functional as $val){
                $result[$val] = [];
            }
        }

        return $result;
    }


    //产品成分、功效对应关系
    static function getFunctionalComponent(){

        $list = array(
            'effect'=> array(
                '保湿剂' => '保湿',
                '抗氧化剂' => '抗氧化',
                //'控油' => '控油',
                '清洁剂' => '清洁',
                '物理防晒剂' => '物理防晒',
                '化学防晒剂' => '化学防晒',
                //'舒缓抗敏' => '舒缓',
//                '美白剂' => '美白',
                '美白祛斑' => '美白',
//                '硫酸酯钠' => 'sls/sles',
//                '氨酸钠' => '氨基酸表活',
//                '氨酸钾' => '氨基酸表活',
//                '牛磺酸钠' => '氨基酸表活',
            ),
            'safe'=> array(
                //'香精' => '香精',
                '防腐剂' => '防腐剂',
//                '视黄醇' => '孕妇慎用',
//                '水杨酸' => '孕妇慎用',
//                '羟苯异丁酯' => '孕妇慎用',
//                '甲氧基肉桂酸乙基己酯' => '孕妇慎用',
            )

        );

        return $list;
    }





}