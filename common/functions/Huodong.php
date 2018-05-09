<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\functions;

use Yii;
class Huodong{
    /**
     * [getCosmetics 获取统计详情]
     * @param  [type] $relationId [关联ID]
     * @param  [type] $type[关联类型]
     * @return [type]     [description]
     */
    PUBLIC STATIC function getCosmetics($relationId,$type){
        //参数
        $relationId  = intval($relationId);
        $type        = intval($type);
        $time        = time();
        
        if(!$relationId || !$type) return false;

        $sql        = " SELECT id FROM {{%huodong_special_config}}
                        WHERE relation = '$relationId' AND type = '$type' AND status = '1' AND starttime <= '$time' AND endtime >= '$time'
                        ORDER BY id DESC";
        $huodongId  = Yii::$app->db->createCommand($sql)->queryScalar();

        return $huodongId;
    }
    /**
     * [getCosmeticsAll 获取所有统计]
     * @return [type]     [description]
     */
    PUBLIC STATIC function getCosmeticsAll(){
        //参数
        $time        = time();

        $sql        = " SELECT id,relation FROM {{%huodong_special_config}}
                        WHERE  status = '1' AND starttime <= '$time' AND endtime >= '$time'
                        ORDER BY id DESC";
        $huodongAll = Yii::$app->db->createCommand($sql)->queryAll();

        return $huodongAll;
    }
    /**
     * [huodongCosmetics 活动统计]
     * @param  [type] $relationId [关联ID]
     * @param  [type] $type[关联类型]
     * @param  [type] $userId[用户ID]
     * @param  [type] $referer[来源渠道]
     * @return [type]     [description]
     */
    PUBLIC STATIC function huodongCosmetics($relationId,$type,$userId = '0',$referer = 'H5'){
        //参数
        $relationId = intval($relationId);
        $type       = intval($type);
        $ip         = Tools::getip();
        $userId     = intval($userId);
        $referer    = Functions::checkStr($referer);
        
        $huodongId  = self::getCosmetics($relationId,$type);

        if(!$huodongId || !$userId) return false;

        $insertSql  = " INSERT INTO {{%huodong_statistics_click}}(`hid`,`type`,`relation_id`,`user_id`,`ip`,`referer`) 
                        VALUES('$huodongId','$type','$relationId','$userId','$ip','$referer')";
        Yii::$app->db->createCommand($insertSql)->execute();
        return true;
    }
    /**
     * [checkCosmetics 验证是否有资格参加]
     * @param  [type] $relationId [关联ID]
     * @param  [type] $type[关联类型]
     * @param  [type] $userId[用户ID]
     * @param  [type] $commentId[评论ID]
     * @return [type]     [description]
     */
    PUBLIC STATIC function checkCosmetics($relationId,$type,$userId,$commentId){
        //参数
        $relationId = intval($relationId);
        $type       = intval($type);
        $userId     = intval($userId);
        $commentId  = intval($commentId);
        $ip         = Tools::getip();
        //验证是否存在活动
        $huodongId  = self::getCosmetics($relationId,$type);

        if(!$huodongId || !$userId || !$commentId) return false;
        //验证资格
        $userInfo   = Functions::getUserInfo($userId);
        if(!$userInfo) return false;

        //是否参与
        $sql        = "SELECT id FROM {{%huodong_statistics_played}} WHERE hid = '$huodongId' AND user_id = '$userId'";
        $isPlay     = Yii::$app->db->createCommand($sql)->queryScalar();

        if(!$isPlay && $userInfo['rank_points'] >= 350){
            $commentInfo = Functions::getCommentInfo($commentId);
            if($commentInfo && $commentInfo['attachment']){
                $insertSql = "INSERT INTO {{%huodong_statistics_played}}(`hid`,`user_id`,`ip`) VALUES('$huodongId','$userId','$ip')";
                Yii::$app->db->createCommand($insertSql)->execute();
                // 参与成功通知
                NoticeFunctions::notice($userId, NoticeFunctions::$HUODONG,$huodongId);
            }
        }
        return true;
    }
    /**
     * [checkUserCosmetics 验证用户是否有没参加的活动]
     * @param  [type] $userId[用户ID]
     * @return [type]     [description]
     */
    PUBLIC STATIC function getHuodongComment($articleId,$userId){
        //参数
        $articleId  = intval($articleId);
        $userId     = intval($userId);
        if(!$articleId || !$userId) return false;

        //排序
        $whereStr   = " C.first_id = '0' AND C.type = '2' AND C.post_id = '$articleId' AND user_id = '$userId' AND C.status = '1' AND A.attachment !='' ";

        $sql    = "SELECT C.id
                   FROM {{%comment}} C 
                   LEFT JOIN {{%attachment}} A ON A.cid = C.id
                   WHERE $whereStr";

        $commentInfo   =    Yii::$app->db->createCommand($sql)->queryOne();

        return $commentInfo ? true : false;
    }
    /**
     * [checkUserCosmetics 验证用户是否有没参加的活动]
     * @param  [type] $userId[用户ID]
     * @return [type]     [description]
     */
    PUBLIC STATIC function checkUserCosmetics($userId){
        //参数
        $userId     = intval($userId);
        $ip         = Tools::getip();
        //验证是否存在活动
        $huodongAll = self::getCosmeticsAll();
        if(!$userId || empty($huodongAll)) return false;

        //验证资格
        $userInfo   = Functions::getUserInfo($userId);
        if(!$userInfo || $userInfo['rank_points'] < 350) return false;


        //未参与则判断是否有参与资格
        foreach ($huodongAll as $key => $value) {
            $huodongId = $value['id'];
            $articleId = $value['relation'];
            //是否参与
            $sql        = "SELECT id FROM {{%huodong_statistics_played}} WHERE hid = '$huodongId' AND user_id = '$userId'";
            $isPlay     = Yii::$app->db->createCommand($sql)->queryScalar();
            if($isPlay) continue; 

            $isComment = self::getHuodongComment($articleId,$userId);
            if($isComment){
                $insertSql = "INSERT INTO {{%huodong_statistics_played}}(`hid`,`user_id`,`ip`) VALUES('$huodongId','$userId','$ip')";
                Yii::$app->db->createCommand($insertSql)->execute();
                // 参与成功通知
                NoticeFunctions::notice($userId, NoticeFunctions::$HUODONG,$huodongId);
            }

            unset($huodongId);
            unset($articleId);
        }

        return true;
    }
    /**
     * [huodongCosmetics 活动统计]
     * @param  [type] $relationId [关联ID]
     * @param  [type] $type[关联类型]
     * @param  [type] $userId[用户ID]
     * @param  [type] $referer[来源渠道]
     * @return [type]     [description]
     */
    PUBLIC STATIC function downLoadCosmetics($huodongId,$relationId,$type,$userId = '0',$referer = 'H5'){
        //参数
        $relationId = intval($relationId);
        $type       = intval($type);
        $ip         = Tools::getip();
        $userId     = intval($userId);
        $referer    = Functions::checkStr($referer);
        
        // $huodongId  = self::getCosmetics($relationId,'2');

        if(!$huodongId) return false;

        $insertSql  = " INSERT INTO {{%log_download_click}}(`hid`,`type`,`relation_id`,`user_id`,`ip`,`referer`) 
                        VALUES('$huodongId','$type','$relationId','$userId','$ip','$referer')";
        Yii::$app->db->createCommand($insertSql)->execute();
        return true;
    }
    /**
     * [getProbability 获取概率值]
     * @param  [type] $money  [总值]
     * @param  [type] $people [分配次数]
     * @param  [type] $min    [最小分配]
     * @param  [type] $max    [最大分配]
     * @return [type]         [返回概率]
     */
    PUBLIC STATIC function getProbability($money, $people, $min, $max)
    {
        $result = [];
        for ($i=0; $i < $people; $i++) { 
            do {
                // 1.进行本次分配
                $result[$i] = mt_rand($min, $max); 
                // 2.本次分配后，剩余人数
                $restPeople = $people - ($i+1);    
                // 3.本次分配后，剩余值
                $restMoney  = $money - array_sum(array_slice($result, 0, $i+1)); 
                // 4.本次分配后，剩余值是否在合理范围？ 不在则重新分配
            } while ($restMoney > $restPeople * $max || $restMoney < $restPeople * $min);
        }
        return $result;
    }
}
