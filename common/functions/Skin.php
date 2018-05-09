<?php

/**
 * 肤质方法
 */
namespace common\functions;

use Yii;
use yii\sphinx\Query;


class Skin {
        //肤质测试配置
        PUBLIC  STATIC  $skinConfig = [
                'dry'=> [
                            ['name'=>'重油','letter' => 'O','min' => '31' ,'max' => '40','desc'=>'属于非常油的皮肤'],
                            ['name'=>'轻油','letter' => 'O','min' => '25' ,'max' => '30','desc'=>'属于轻微的油性皮肤'],
                            ['name'=>'轻干','letter' => 'D','min' => '16' ,'max' => '24','desc'=>'属于轻微的干性皮肤'],
                            ['name'=>'重干','letter' => 'D','min' => '10' ,'max' => '15','desc'=>'属于非常干的皮肤'],
                        ],
                'tolerance'=> [
                            ['name'=>'重敏','letter' => 'S','min' => '23' ,'max' => '48','desc'=>'属于非常敏感的皮肤'],
                            ['name'=>'轻敏','letter' => 'S','min' => '20' ,'max' => '22','desc'=>'属于略为敏感皮肤'],
                            ['name'=>'轻耐','letter' => 'R','min' => '17' ,'max' => '19','desc'=>'属于比较有耐受性的皮肤'],
                            ['name'=>'重耐','letter' => 'R','min' => '12' ,'max' => '16','desc'=>'属于耐受性很强的皮肤'],
                        ],
                'pigment'=> [
                            ['name'=>'色素','letter' => 'P','min' => '18' ,'max' => '29','desc'=>'属于色素沉着性皮肤'],
                            ['name'=>'非色素','letter' => 'N','min' => '6' ,'max' => '17','desc'=>'属于非色素沉着性皮肤'],
                        ],
                'compact'=> [
                            ['name'=>'皱纹','letter' => 'W','min' => '22' ,'max' => '51','desc'=>'属于皱纹性皮肤'],
                            ['name'=>'紧致','letter' => 'T','min' => '13' ,'max' => '21','desc'=>'属于紧致性皮肤'],
                        ],
        ];
        PUBLIC  STATIC  $skinList = [
                'D' => ['10' ,'24'],
                'O' => ['25' ,'40'],
                'R' => ['12' ,'19'],
                'S' => ['20' ,'48'],
                'N' => ['6' ,'17'],
                'P' => ['18' ,'29'],
                'T' => ['13' ,'21'],
                'W' => ['22' ,'51'],
        ];
        //分类展示功效
        PUBLIC STATIC $showList = [
                '洁面'    => ['清洁','氨基酸表活','sls/sles'],
                '化妆水'  => ['保湿','抗氧化','美白','舒缓','控油'],
                '精华'    => ['保湿','抗氧化','美白','舒缓','控油'],
                '乳霜'    => ['保湿','抗氧化','美白','舒缓','控油'],
                '眼霜'    => ['保湿','抗氧化','美白','舒缓','控油'],
                '面膜'    => ['保湿','抗氧化','美白','舒缓','控油'],
                '防晒'    => ['物理防晒','化学防晒'],
        ];
        //成份对应功效
        PUBLIC STATIC $effect = [
                '保湿剂'      => '保湿',
                '抗氧化剂'    => '抗氧化',
                '控油'        => '控油',
                '清洁剂'      => '清洁',
                '物理防晒剂'  => '物理防晒',
                '化学防晒剂'  => '化学防晒',
                '舒缓抗敏'    => '舒缓',
                '美白剂'      => '美白',
                '硫酸酯钠'    => 'sls/sles',
                '氨酸钠'      => '氨基酸表活',
                '氨酸钾'      => '氨基酸表活',
                '牛磺酸钠'    => '氨基酸表活' 
        ];
        //安全信息
        PUBLIC STATIC $security =[
                '香精'        => '香精',
                '防腐剂'      => '防腐剂',
                '视黄醇'      => '孕妇慎用',
                '水杨酸'      => '孕妇慎用',
                '羟苯异丁酯'  => '孕妇慎用',
                '甲氧基肉桂酸乙基己酯' => '孕妇慎用',
        ];
    /**
     * [getSkin 获取肤质列表]
     * @return [type] [description]
     */
    PUBLIC STATIC function getSkin(){
        $return     =   [];
        $cache      =   Yii::$app->cache;
        //分类列表和功效列表
        $return     =   $cache->get('skinList');
        if(!$return){
            $sql        = 'SELECT `id`,`skin`,`explain`,`features`,`elements`,`star` FROM {{%skin}}';
            $skinList   = Yii::$app->db->createCommand($sql)->queryAll();
            foreach ($skinList as $key => $value) {
                $return[$value['skin']] = [
                    'explain' => $value['explain'],
                    'skin_id' => $value['id'],
                    'features'=>$value['features'],
                    'elements'=>$value['elements'],
                    'star'    =>$value['star'],
                ];
            }
            $cache->set('skinList',$return,300);      
        }
        return $return;
    }
    /**
     * 查询用户肤质
     *
     * @param int $userId 用户ID
     * @return string 
     */
    PUBLIC STATIC function getUserSkin($userId)
    {
        if(!$userId) return false;
        $userSql    = "SELECT uid,dry,tolerance,pigment,compact,skin_name,add_time FROM {{%user_skin}} WHERE uid = '$userId'";
        $skinInfo   = Yii::$app->db->createCommand($userSql)->queryOne();

        if(!$skinInfo) return false;
        if(!$skinInfo['skin_name']) return false;
        //$return = self::evaluateSkin($skinInfo);
        return $skinInfo;
    }
    /**
     * 用户肤质推荐方案
     *
     * @param int $userId 用户ID
     * @return string 
     */
    PUBLIC STATIC function getUserSkinCopy($userId)
    {
        $categoryList = [];
        if(!$userId) return false;
        $userSql    = "SELECT S.category_id,S.copy,S.reskin FROM {{%skin_recommend}} S LEFT JOIN {{%user_skin}} U ON U.skin_name = S.skin_name WHERE U.uid = '$userId'";
        $skinInfo   = Yii::$app->db->createCommand($userSql)->queryAll();

        foreach ($skinInfo as $k => $v) {
            $skinList[$v['category_id']]['copy']    = $v['copy'];
            $skinList[$v['category_id']]['reskin']  = $v['reskin'];
        }

        $cateList   =    Functions::categoryList(); 

        foreach ($cateList as $key => $value) {
            
            if(!empty($skinList[$value['id']]['reskin'])){
                $condition  = [];
                $copy       = isset($skinList[$value['id']]['copy']) ? $skinList[$value['id']]['copy'] : '';
                if($value['budget']){
                   $priceArr = explode('，',$value['budget']);
                   foreach ($priceArr as $k => $v) {
                       $section = explode('-',$v);
                       $condition[] = ['min' => $section[0],'max'=> $section[1]];
                       unset($section);
                   }
                   unset($priceArr);
                }
                $categoryList[] = ['name' => $value['cate_name'],'id' => $value['id'],'copy' => $copy,'condition' => $condition];
                unset($copy);
                unset($condition);
            }
            
        }
        return $categoryList;
    }
    /**
     * 查询用户测试结果
     *
     * @param int $userId 用户ID
     * @return string 
     */
    PUBLIC STATIC function getUserTestSkin($userId)
    {
        if(!$userId) return false;
        $userSql    = "SELECT uid,dry,tolerance,pigment,compact,skin_name FROM {{%user_skin}} WHERE uid = '$userId'";
        $skinInfo   = Yii::$app->db->createCommand($userSql)->queryOne();
        $skinAll    = self::getSkinType();
        //进行识别
        $skin       =   ['dry' => '','tolerance' => '','pigment' => '','compact' => ''];

        $returnInfo  = [];
        foreach ($skin as $key => $value) {
            foreach ($skinAll[$key] as $k => $v) {
                if($skinInfo[$key] >= $v['min'] && $skinInfo[$key] <= $v['max'] ){
                    unset($v['min'],$v['max']);
                    $v['score']     = $skinInfo[$key];
                    $v['type']      = $key;
                    $returnInfo[]   = $v;
                }
                
            }
        }
        return $returnInfo;
    }
    /**
     * 获取肤质配置
     *
     * @return string 
     */
    PUBLIC STATIC function getSkinType()
    {
        //获取信息
        $cache     =   Yii::$app->cache;
        $return    =   '';//$cache->get('api_get_skin_all');
        if(!$return){
            $skinConfig     = self::$skinConfig;
            $return         = ['dry' => [],'tolerance' => [],'pigment' => [],'compact' => []];
            $maximum        = ['dry' => 0,'tolerance' => 0,'pigment' => 0,'compact' => 0];
            //计算最大值--无奈循环一次
            foreach ($skinConfig as $key => $value) {
                foreach ($value as $k => $v) {
                    $maximum[$key] = $v['max'] > $maximum[$key] ? $v['max'] : $maximum[$key];
                }
            }
            $sql       = "SELECT * FROM {{%skin_type}} ORDER BY `order`";
            $skinAll   = Yii::$app->db->createCommand($sql)->queryAll();

            foreach ($skinAll as $k => $v) {
                $type = $v['type'];
                foreach ($skinConfig[$type] as $k1 => $v1) {
                    if($v1['name'] == $v['name']){
                        $return[$type][] = [
                            'name'      => $v1['name'],
                            'letter'    => $v1['letter'],
                            'min'       => $v1['min'],
                            'max'       => $v1['max'],
                            'maximum'   => $maximum[$v['type']],
                            'unscramble'=> $v['unscramble'],
                        ];
                    }
                }
            }
        }
        return $return;
    }
    /**
     * 查询用户推荐成份及不推荐成份
     *
     * @param int $userId 用户ID
     * @return string 
     */
    PUBLIC STATIC function getUserRecommendSkin($categoryId,$userId)
    {
        $data = ['recommend'=> [] , 'notRecommend' =>[]];

        if(!$userId || !$categoryId) return $data;
        $userSql    = " SELECT R.reskin,R.noreskin FROM {{%user_skin}} U
                        LEFT JOIN {{%skin_recommend}} R ON R.skin_name = U.skin_name 
                        WHERE uid = '$userId' AND category_id = '$categoryId'";
        $skinInfo   = Yii::$app->db->createCommand($userSql)->queryOne();

        $data['recommend']      = $skinInfo['reskin'] ? explode(',',$skinInfo['reskin']) : [];
        $data['notRecommend']   = $skinInfo['noreskin'] ? explode(',',$skinInfo['noreskin']) : [];

        return $data;
    }
    /**
     * 根据评分定肤质
     *
     * @param int $skinInfo 评分信息
     * @return string 
     */
    PUBLIC STATIC function evaluateSkin($skinInfo)
    {
        $skin_name  = '';
        $return     = ['dry' => '','tolerance' => '','pigment' => '','compact' => ''];

        $dry        =   $skinInfo['dry'] ;
        $tolerance  =   $skinInfo['tolerance'];
        $pigment    =   $skinInfo['pigment'];
        $compact    =   $skinInfo['compact'];

        if(!$dry || !$tolerance || !$pigment  || !$compact) return false;

        foreach ($return as $key => $value) {
            foreach (self::$skinConfig[$key] as $k => $v) {
                if($skinInfo[$key]){
                    if($skinInfo[$key] >= $v['min'] && $skinInfo[$key] <= $v['max'] ){
                        $return[$key]  = $v['name'];
                        $skin_name    .= $v['letter'];
                    }
                }
            }
        }
        //如果有问题，则为空
        foreach ($return as $skinKey => $skinValue) {
            if(!$skinValue){
                return false;
                break;
            }
        }
        $return['skin_name'] = $skin_name;
        return $return;
    }
    /**
     * 保存用户肤质
     * @param int $uid 用户ID
     * @param int $skinInfo 评分信息
     * @return string 
     */
    PUBLIC STATIC function saveSkin($uid,$type,$value)
    {
        $uid       = intval($uid);
        $value     = intval($value);
        $time      = time();
        $skinArr   = ['dry','tolerance','pigment','compact'];

        $isType    = in_array($type,$skinArr);

        if(!$isType) return ['status' => -1, 'msg' =>'类型不正确'];
        if(!$uid) return ['status' => -1, 'msg' => '用户参数为空'];

        $isTrue = 0;
        foreach (self::$skinConfig[$type] as $k => $v) {
            if($value >= $v['min'] && $value <= $v['max'] ){
                $isTrue = 1;
            }
        }
        if(!$isTrue) return ['status' => -1, 'msg' => '肤质参数不在范围内'];

        $userSql    = "SELECT uid,dry,tolerance,pigment,compact,skin_name FROM {{%user_skin}} WHERE uid = '$uid'";
        $skinInfo   = Yii::$app->db->createCommand($userSql)->queryOne();

        $updateStr       = "`$type` = '$value',add_time = '$time'";
        //存在更新，不存在添加 
        if(!$skinInfo){
            $sql    = "INSERT INTO {{%user_skin}} (uid) VALUES('$uid')";
            Yii::$app->db->createCommand($sql)->execute();  
        }else{
            $skinInfo[$type]        = $value;
            $return                 = self::evaluateSkin($skinInfo);

            if($return['skin_name'] && strlen($return['skin_name']) == 4) {
                $n        = 0;
                $skinName = $return['skin_name'];
                $reSkin   = ['skin_name' => $skinName];
                unset($return['skin_name']);
                foreach ($return as $key => $value) {
                    $reSkin[] = ['key' => $skinName[$n],'name' => $value];
                    $n++;
                }
                $updateStr .= " , skin_name = '$skinName'";
            }
        }

        $updateSql   = " UPDATE {{%user_skin}} SET $updateStr WHERE uid = '$uid'";

        Yii::$app->db->createCommand($updateSql)->execute();

        return ['status' => 1, 'msg' => '保存成功'];
    }
    /**
     * [skinProduct 肤质推荐]
     * @param  [type] $skin [肤质]
     * @return [type]       [推荐内容]
     */
    PUBLIC STATIC function skinProduct($skin)
    {
        //验证
        $skinList   =  self::getSkin();
        if(!$skin  || !array_key_exists($skin,$skinList)) return ['status' => -1, 'msg' => '肤质不符合'];

        //获取信息
        $cache      =   Yii::$app->cache;
        $skinInfo   =   $skinList[$skin];
        $skinId     =   $skinInfo['skin_id'];
        $return     =   $cache->get('skin_recommend_product_'.$skin);

        $cateList   =    Functions::cateList();
        $orderBy    =  'P.comment_num DESC,P.is_recommend DESC,P.is_complete DESC,P.has_img DESC,P.has_price DESC,P.has_brand DESC,P.created_at DESC,P.id DESC';

        if(!$return){
            
            $sql        = 'SELECT category_id,reskin FROM {{%skin_recommend}} WHERE skin_id = \''.$skinInfo['skin_id'].'\' ORDER BY category_id ASC';
            $reskinList = Yii::$app->db->createCommand($sql)->queryAll();
            $skinReturn = [];
            foreach ($reskinList as $key => $value) {
                $categroyId = $value['category_id'];
                //条件
                $whereStr   = " P.status = '1' AND R.status = '1' AND P.cate_id = '$categroyId' AND R.skin_id = '$skinId'";

                $sql    = " SELECT P.id,P.cate_id,P.product_name,P.product_img,P.product_explain,P.price,P.form,B.name as brand_name FROM {{%product_details}} P  
                            LEFT JOIN {{%skin_recommend_product}} R ON P.id = R.product_id 
                            LEFT JOIN {{%brand}} B ON B.id = P.brand_id 
                            WHERE $whereStr
                            ORDER BY $orderBy
                            LIMIT 5"; 

                $list           = Yii::$app->db->createCommand($sql)->queryAll();

                foreach ($list as $key => $value) {
                   $list[$key]['product_img'] = Functions::get_image_path($value['product_img']);
                }

                $skinReturn[]   = ['category_id' => $categroyId ,'category_name' => $cateList[$categroyId],'data' => $list];

                unset($categroyId);
                unset($whereStr);
                unset($reskinStr);
                unset($list);
            }
            $cache->set('skin_recommend_product_'.$skin,$skinReturn,300);
        }else{
            $skinReturn = $return;
        }
        return $skinReturn;
    }
    /**
     * [skinProductList 肤质推荐列表]
     * @param  [type]  $skin        [肤质]
     * @param  string  $category_id [栏目]
     * @param  integer $page        [页数]
     * @param  string  $pageSize    [每页数]
     * @return [type]               [数据]
     */
    PUBLIC STATIC function skinProductList($skin,$category_id = '',$page = 1,$pageSize = '20',$min = '',$max = '')
    {
        //验证
        $skinList   =  self::getSkin();
        if(!$skin  || !array_key_exists($skin,$skinList)) return ['status' => -1, 'msg' => '肤质不符合'];

        //获取信息
        $cache      =   Yii::$app->cache;
        $skinInfo   =   $skinList[$skin];
        $skinId     =   $skinInfo['skin_id'];
        //条件
        $whereStr   = " P.status = '1' AND R.status = '1' AND P.cate_id = '$category_id' AND R.skin_id = '$skinId'";
        $whereStr  .= $min ? ' AND P.price >= \''.$min.'\'' : '';
        $whereStr  .= $max ? ' AND P.price <= \''.$max.'\'' : '';

        $orderBy    =  'P.comment_num DESC,P.is_recommend DESC,P.is_complete DESC,P.has_img DESC,P.has_price DESC,P.has_brand DESC,P.created_at DESC,P.id DESC';
        
        $sql        = "SELECT COUNT(*) FROM (SELECT P.id FROM {{%product_details}} P  
                    LEFT JOIN {{%skin_recommend_product}} R ON P.id = R.product_id
                    WHERE $whereStr) AS M";
        /* 分页 */
        $num    = Yii::$app->db->createCommand($sql)->queryScalar();
        //分页判断
        $pageMin   = ($page - 1) * $pageSize;

        $skinReturn = [];
        $sql        = " SELECT P.id,P.product_name,P.product_img,P.star,P.price,P.form,P.product_explain FROM {{%product_details}} P  
                        LEFT JOIN {{%skin_recommend_product}} R ON P.id = R.product_id 
                        WHERE $whereStr
                        ORDER BY $orderBy  
                        LIMIT $pageMin,$pageSize"; 

        $list       = Yii::$app->db->createCommand($sql)->queryAll();

        foreach ($list as $key => $value) {
           $list[$key]['product_img'] = Functions::get_image_path($value['product_img']);
        }

        $skinReturn = ['status' => 1, 'msg' =>$list,'pageTotal' => $num, 'pageSize'=> $pageSize];

        return $skinReturn;
    }  
    /**
     * [skinProduct 肤质推荐]
     * @param  [type] $skin [肤质]
     * @return [type]       [推荐内容]
     */
    PUBLIC STATIC function skinArticle($skin)
    {
        //验证
        $skinList   =  self::getSkin();
        if(!$skin  || !array_key_exists($skin,$skinList)) return ['status' => -1, 'msg' => '肤质不符合'];

        //获取信息
        $cache          =   Yii::$app->cache;
        $skinInfo       =   $skinList[$skin];
        $skinArticle    =   $cache->get('skin_recommend_article_'.$skin);

        if(!$skinArticle){
            $sql        = 'SELECT id,title,article_img,comment_num,created_at FROM {{%article}} WHERE status = \'1\' AND  skin_id = \''.$skinInfo['skin_id'].'\' ORDER BY created_at DESC';
            $skinArticle= Yii::$app->db->createCommand($sql)->queryAll();

            foreach ($skinArticle as $key => $value) {
                $skinArticle[$key]['article_img']  = Functions::get_image_path($value['article_img']);
                $skinArticle[$key]['created_at']   = Tools::HtmlDate($value['created_at']);
            }
            $cache->set('skin_recommend_article_'.$skin,$skinArticle,300);
        }
        return $skinArticle;
    }
    /**
     * [skinTest 肤质测试题]
     * @param  [type] $type [类型]
     * @return [type]       [description]
     */
    PUBLIC STATIC function skinTest($type){
        //获取信息
        $type           =   intval($type) ? intval($type) : 1 ;
        $cache          =   Yii::$app->cache;
        $question       =   $cache->get('skin_test_'.$type);
        if(!$question){
            $sql        = "SELECT id,question,`order` FROM {{%question}} WHERE type = '$type'  ORDER BY `order` ASC";

            $question   = Yii::$app->db->createCommand($sql)->queryAll();

            foreach ($question as $key => $value) {
                $qid        = $value['id'];
                $answerSql  = "SELECT `option`,`content`,`score` FROM {{%question_answer}} 
                WHERE qid = '$qid' ORDER BY `option` ASC";

                $answerList = Yii::$app->db->createCommand($answerSql)->queryAll();
                $question[$key]['answer'] = $answerList;
            }        
        }
        return $question;
    }
    /**
     * [componentStatistics 产品成份统计]
     * @param  [type] $id [产品ID]
     * @return [type]     [description]
     */
    PUBLIC STATIC function componentStatistics($id,$userId = ''){
        if(!$id) return false;
        $sql    = "SELECT P.id,P.cate_id,C.cate_name,P.product_name,P.price,P.is_top,P.form,P.alias,P.star,P.standard_number,P.product_country,P.product_date,P.remark,P.product_img,P.product_company,B.id AS brand_id,B.name AS brand,B.rule,B.img AS brand_img,P.en_product_company,P.product_explain,B.link_tb,B.link_jd
                FROM {{%product_details}} P 
                LEFT JOIN {{%product_category}} C ON P.cate_id = C.id
                LEFT JOIN {{%brand}} B ON B.id = P.brand_id
                WHERE P.id =  '$id' AND P.status = '1'";
        $rows   = Yii::$app->db->createCommand($sql)->queryOne();

        if(!$rows){ return false;}
        //评论数
        $rows['Praise'] = 0;
        $rows['middle'] = 0;
        $rows['bad']    = 0;
        $rows['total']  = 0;

        //价格格式修改
        $rows['price'] = (float)$rows['price'];

        //评论统计
        $totalSql   = "SELECT star,COUNT(*) num FROM {{%comment}}  WHERE type = '1' AND  post_id=  '$id' AND parent_id = '0' AND status = '1' GROUP BY star";
        $starAll    = Yii::$app->db->createCommand($totalSql)->queryAll();
        foreach ($starAll as  $starkey => $starInfo) {
            if($starInfo['star'] >= 4) $rows['Praise'] += $starInfo['num'];
            if($starInfo['star'] == 3) $rows['middle'] += $starInfo['num'];
            if($starInfo['star'] <= 2) $rows['bad'] += $starInfo['num'];
            $rows['total'] += $starInfo['num'];
        }

        //查成份
        $compSql = "SELECT C.id,C.name,C.risk_grade,C.is_active,C.is_pox,C.component_action,C.description 
                    FROM {{%product_relate}} R LEFT JOIN {{%product_component}}  C ON  R.component_id = C.id
                    WHERE R.product_id = '$id' ORDER BY R.id ASC";

        $componentList = Yii::$app->db->createCommand($compSql)->queryAll();

        $rows['componentList']  = $componentList;
        //如果用户登录，判断适合和不适合用户成份
        if($userId){
            $userSkinAll = self::getUserRecommendSkin($rows['cate_id'],$userId);
            $rows['recommend']      = [];
            $rows['notRecommend']   = [];
        }

        //功效成份
        $rows['effect']         = [];

        //标签
        $tagArr = [];
        $sql    = " SELECT cti.itemid,ct.tagname 
                    FROM {{%common_tagitem}} cti 
                    LEFT JOIN {{%common_tag}} ct ON cti.tagid = ct.tagid  
                    WHERE cti.idtype = 1 AND itemid = '$id'";

        $taglist= Yii::$app->db->createCommand($sql)->queryAll();
 
        foreach ($taglist as $k => $v) {
            $tagArr[] = $v['tagname'];
        }
        //获取功效列表
        $efficacyList = Efficacy::getEfficacyList($componentList,$rows['cate_name']);

        //没有特征标签用成份标签
        if($tagArr) {
            $rows['tagList'] = $tagArr;
        }elseif($componentList){
            //功效特征
            $list = [];
            if($efficacyList['function_list']){
                foreach($efficacyList['function_list'] as $k=>$v){
                    if(count($v) > 0){
                        $list[$k] = count($v);
                    }
                }
            }
            //(成分数最多的3个功效名)
            arsort($list);
            $list = array_keys($list);
            $list = array_slice($list,0,3);
            $rows['tagList'] = $list;  
        }
        //安全成份
        $security = ['香精' => [], '防腐剂' => [], '风险成分'=> [], '孕妇慎用' => []];
        $rows['product_img']  = Functions::get_image_path($rows['product_img'],1);
        $rows['brand_img']    = Functions::get_image_path($rows['brand_img']);
        $effectNum = 0;
        $functionList = $efficacyList['function_list'];

        foreach ($functionList as $k => $v) {
            $function = ['name' => $k, 'id' => []];
            foreach ($v as $k1 => $v1) {
                $function['id'][] = (string)$k1;
                $effectNum ++;
            }
            $rows['effect'][] = $function;
            unset($function);
        }
        $safeLlist = $efficacyList['safe_list'];

        foreach ($safeLlist as $k2 => $v2) {
            $safe = ['name' => $k2, 'id' => []];
            foreach ($v2 as $k3 => $v3) {
                $safe['id'][] = (string)$k3;
            }
            $rows['security'][] = $safe;
            unset($safe);
        }
        foreach ($componentList as $key => $value) {
            $objective = explode("，",$value['component_action']);
            //用户推荐与不推荐成份
            if($userId && in_array($value['id'],$userSkinAll['recommend'])){
                if(in_array($value['id'],$userSkinAll['recommend'])){
                    $rows['recommend'][] = $value['id'];
                }
                 if(in_array($value['id'],$userSkinAll['notRecommend'])){
                    $rows['notRecommend'][] = $value['id'];
                }
            }
        }
        
        $rows['effectNum']      = $effectNum;
        return $rows;
    }
    /**
     * [searchProduct 搜索产品]
     * @return [type] [description]
     */
    PUBLIC STATIC function searchProduct($param){
        $search     =   Functions::checkStr($param['search']);
        $brandId    =   isset($param['brandId'])    ? intval($param['brandId'])     : 0 ;
        $categroyId =   isset($param['categroyId']) ? intval($param['categroyId'])  : 0 ;
        $star       =   isset($param['star'])       ? intval($param['star'])        : 0 ;
        $effect     =   isset($param['effect'])     ? Functions::checkStr($param['effect']) : '';
        $pageSize   =   isset($param['pageSize'])   ? intval($param['pageSize']) : 20 ;
        $page       =   isset($param['page'])       ? intval($param['page'])  : 1 ;
        //先搜索字符
        $idStr      =   '';
        $idArr      =  [];
        $rows       =  [];
        $brandArr   =  (object)[];

        if($search){
            $query      = new Query();
            $brandRows  = $query->select('id')->from('brand')->match($search)->all();
            //搜索品牌
            if($brandRows){
                foreach ($brandRows as $key => $value) {
                    $idArr[] = $value['id'];
                }
                $idStr      = Functions::db_create_in($idArr,'id');
                $brandSql   = "SELECT id,name,img,hot
                               FROM {{%brand}}
                               WHERE $idStr
                               ORDER BY is_recommend DESC,hot DESC
                               LIMIT 1";

                $brandArr   = Yii::$app->db->createCommand($brandSql)->queryOne();  
                if($brandArr){
                    $brandArr['img']  = Functions::get_image_path($brandArr['img'],1);
                }
            }
            $idStr      =   '';
            $idArr      =  [];
            //搜索产品
            $rows       = $query->select('id')->from('product')->match($search)->limit(1000)->all();
            foreach ($rows as $key => $value) {
                $idArr[] = $value['id'];
            }
            $idStr = Functions::db_create_in($idArr,'P.id');   
        }

        //再搜索条件
        $whereStr   = ' P.status = 1 ';
        $whereStr  .= $idStr ? ' AND '.$idStr : '';
        $whereStr  .= $brandId      ? " AND P.brand_id = '$brandId' " : '';
        $whereStr  .= $star         ? " AND P.star = '$star' " : '';
        $whereStr  .= $categroyId   ? " AND P.cate_id = '$categroyId' " : '';
        $whereStr  .= $effect       ? ' AND FIND_IN_SET(\''.$effect.'\',P.effect_id) ' : '';

        $orderBy    = "P.comment_num DESC,P.is_recommend DESC,P.is_complete DESC,P.has_img DESC,P.has_price DESC,P.has_brand DESC,P.created_at DESC,P.id DESC";
        /* 分页 */
        $sql        = "SELECT COUNT(*) FROM {{%product_details}} P WHERE $whereStr";
        $num        = Yii::$app->db->createCommand($sql)->queryScalar();

        //分页判断
        $maxPage = ceil($num/$pageSize) + 1;
        $page > $maxPage  ? $page = $maxPage  : '';
        $offset  = ($page - 1) * $pageSize;

        $productSql = "SELECT P.id, P.product_name,P.price,P.form,P.star,P.product_img
                       FROM {{%product_details}} P 
                       WHERE $whereStr
                       ORDER BY $orderBy
                       LIMIT $offset,$pageSize";
        $productArr = Yii::$app->db->createCommand($productSql)->queryAll();
        if($productArr){
            foreach ($productArr as $key => $value) {
                $productArr[$key]['product_img']  = Functions::get_image_path($value['product_img'],1);
            }
        }else{
            $productArr =  [];
        }
        $rows = ['brand' => $brandArr,'product' => $productArr];
        
        return [ 'status' => 1,'msg' => $rows ,'pageTotal' => $num, 'page'=>$page ];
    }
    /**
     * [productComponentList 成份产品]
     * @param  [type]  $id [成份ID]
     * @param  integer $page    [页数]
     * @param  integer $pageSize [每页条数]
     * @return [type]          [description]
    */
    PUBLIC STATIC function productComponentList($params){
        //参数
        $id         = isset($params['id']) ? intval($params['id']) : '';
        $pageSize   = isset($params['pageSize']) ? intval($params['pageSize']) : 20;
        $page       = isset($params['page']) ? intval($params['page']) : 1;

        $idStr          = '';
        $return         = [];
        //先搜索产品
        $relateSql      = "SELECT product_id FROM {{%product_relate}}  WHERE component_id = '$id' ";
        $componentList  = Yii::$app->db->createCommand($relateSql)->queryAll();
        foreach ($componentList as $key => $value) {
            $idArr[]    = $value['product_id'];
        }
        $idStr          = Functions::db_create_in($idArr,'P.id');

        //分页判断
        $pageMin = ($page - 1) * $pageSize;
        $orderBy = "P.comment_num DESC,P.is_recommend DESC,P.is_complete DESC,P.has_img DESC,P.has_price DESC,P.has_brand DESC,P.created_at DESC,P.id DESC";
        $whereStr   = " P.status = '1' ";
        $whereStr  .= $idStr ? " AND $idStr " : '';
        /* 分页 */
        $rows   = array();
        $sql    = "SELECT COUNT(*) AS num FROM {{%product_details}} P WHERE $whereStr";
        $num    = Yii::$app->db->createCommand($sql)->queryScalar();

        $sql    = "SELECT P.id,B.id AS brand_id,B.name AS brand_name,B.rule,P.product_name,P.product_img,P.price,P.form,P.star,B.name AS brand_name
                  FROM {{%product_details}} P
                  LEFT JOIN {{%brand}} B ON P.brand_id = B.id
                  WHERE $whereStr 
                  ORDER BY  $orderBy 
                  LIMIT $pageMin,$pageSize";

        $rows   = Yii::$app->db->createCommand($sql)->queryAll();
        foreach ($rows as $key => $value) {
            $rows[$key]['product_img'] = Functions::get_image_path($value['product_img'],1);
        }
        $return = ['data' => $rows,'total' => $num];
        return $return;
    }
    /**
     * [saveSkinRecommendProduct 肤质推荐产品]
     * @param  [type] $skin         [肤质]
     * @param  [type] $category_id  [分类ID]
     * @return [type]               [推荐内容]
     */
    PUBLIC STATIC function saveSkinRecommendProduct($skin,$category_id = '')
    {
        //验证
        $skinList   =  self::getSkin();
        if(!$skin  || !array_key_exists($skin,$skinList)) return ['status' => -1, 'msg' => '肤质未找到'];

        //获取信息
        $error      =  '';
        if($category_id){
            $sql        = "SELECT category_id,reskin,skin_id FROM {{%skin_recommend}} WHERE skin_name = '$skin' AND category_id = '$category_id'";
        }else{
            $sql        = "SELECT category_id,reskin,skin_id FROM {{%skin_recommend}} WHERE skin_name = '$skin'";
        }
        $reskinList = Yii::$app->db->createCommand($sql)->queryAll();

        foreach ($reskinList as $key => $value) {
            $categroyId = $value['category_id'];
            $skinId     = $value['skin_id'];

            $reskinStr  = Functions::db_create_in($value['reskin'],'R.component_id');

            $sql    = " SELECT P.id,P.product_name FROM {{%product_details}} P  
                        LEFT JOIN {{%product_relate}} R ON P.id = R.product_id 
                        WHERE P.status = '1' AND P.cate_id = '$categroyId' AND $reskinStr 
                        GROUP BY  P.id"; 

            $list   = Yii::$app->db->createCommand($sql)->queryAll();

            foreach ($list as $k => $v) {
                $product_id     =  $v['id'];
                try {
                    $insertSql  = "INSERT IGNORE INTO  {{%skin_recommend_product}}(skin_id,skin_name,cate_id,product_id)  VALUES('$skinId','$skin','$categroyId','$product_id')";
                    Yii::$app->db->createCommand($insertSql)->execute();
                } catch (Exception $e) {
                    $error .= $product_id."\n";
                }
                unset($product_id);
            }
            unset($categroyId);
            unset($skinId);
            unset($reskinStr);
            unset($list);
            usleep(100);
        }
        return $error ? ['status' => 1, 'msg' => '执行失败产品'.$error] : ['status' => 1, 'msg' => '执行成功'];
    }
    /**
     * [skinProduct 肤质成份删除推荐产品]
     * @param  [type] $skin         [肤质]
     * @param  [type] $category_id  [分类ID]
     * @param  [type] $relate       [成份]
     * @return [type]               [推荐内容]
     */
    PUBLIC STATIC function deleteSkinRecommendProduct($params)
    {

        $skin       =  isset($params['skin']) ? $params['skin'] : '';
        $categoryId =  isset($params['categoryId']) ? $params['categoryId'] : '';
        $relate     =  isset($params['relate']) ? $params['relate'] : '';
        //验证
        $skinList   =  self::getSkin();
        if(!$skin  || !array_key_exists($skin,$skinList)) return ['status' => -1, 'msg' => '肤质未找到'];

        //获取信息
        if(!$relate){
            return ['status' => -1, 'msg' => '成份为空'];
        }

        $reskinStr  = Functions::db_create_in($relate,'R.component_id');

        $sql    = " SELECT P.id,P.product_name FROM {{%product_details}} P  
                    LEFT JOIN {{%product_relate}} R ON P.id = R.product_id 
                    WHERE P.status = '1' AND P.cate_id = '$categoryId' AND $reskinStr 
                    GROUP BY  P.id";

        $list   = Yii::$app->db->createCommand($sql)->queryAll();

        //开始删除
        $skinId = $skinList[$skin]['skin_id'];
        $delStr = '1 = 1';
        $delArr = [];
        foreach ($list as $k => $v) {
            $delArr[] = $v['id'];
        }
        
        if(!empty($delArr)){
            $delStr  =  Functions::db_create_in($delArr,'product_id');
            $deleteSql  = "DELETE  FROM {{%skin_recommend_product}} WHERE skin_id = '$skinId'  AND cate_id = '$categoryId' AND $delStr";
            Yii::$app->db->createCommand($deleteSql)->execute();
        }

        return ['status' => 1, 'msg' => '执行成功'];
    }
    /**
     * [insertSkinRecommendProduct 肤质成份添加推荐产品]
     * @param  [type] $skin         [肤质]
     * @param  [type] $category_id  [分类ID]
     * @param  [type] $relate       [成份]
     * @return [type]               [推荐内容]
     */
    PUBLIC STATIC function insertSkinRecommendProduct($params)
    {

        $skin       =  isset($params['skin']) ? $params['skin'] : '';
        $categoryId =  isset($params['categoryId']) ? $params['categoryId'] : '';
        $relate     =  isset($params['relate']) ? $params['relate'] : '';
        //验证
        $skinList   =  self::getSkin();
        if(!$skin  || !array_key_exists($skin,$skinList)) return ['status' => -1, 'msg' => '肤质未找到'];

        $error      =  '';
        $skinId     = $skinList[$skin]['skin_id'];
        //获取信息
        if(!$relate){
            return ['status' => -1, 'msg' => '成份为空'];
        }

        $reskinStr  = Functions::db_create_in($relate,'R.component_id');

        $sql    = " SELECT P.id,P.product_name FROM {{%product_details}} P  
                    LEFT JOIN {{%product_relate}} R ON P.id = R.product_id 
                    WHERE P.status = '1' AND P.cate_id = '$categoryId' AND $reskinStr 
                    GROUP BY  P.id";

        $list   = Yii::$app->db->createCommand($sql)->queryAll();

        //开始添加
        foreach ($list as $k => $v) {
            $product_id     =  $v['id'];
            try {
                $insertSql  = "INSERT IGNORE INTO  {{%skin_recommend_product}}(skin_id,skin_name,cate_id,product_id)  VALUES('$skinId','$skin','$categoryId','$product_id')";
                Yii::$app->db->createCommand($insertSql)->execute();
            } catch (Exception $e) {
                $error .= $product_id."\n";
            }
            unset($product_id);
        }

        return $error ? ['status' => 1, 'msg' => '执行失败产品'.$error] : ['status' => 1, 'msg' => '执行成功'];
    }
}