<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\functions;

use Yii;
use common\functions\Functions;
/**
 * Description of Functions
 *
 * @author Administrator
 */
class ApiFunctions {
    /**
     * [isExistInventory 是否存在清单]
     * @param  [type] $id [清单ID]
     * @param  [type] $user_id [用户ID]
     * @return [type]      [description]
     */
    static function getInventory($id,$userId = ''){
        if(!$id) return false;
        //参数
        $id         = intval($id);
        $userId     = $userId  ?  intval($userId) : '';
        //排序
        $whereStr   = " id = '$id' AND status = '1'";
        $whereStr  .= $userId ? " AND user_id = '$userId' " : '';

        $sql        = "SELECT * FROM {{%user_inventory}} WHERE $whereStr";
        $info       =  Yii::$app->db->createCommand($sql)->queryOne();

        return $info;
    }
    /**
     * [isExistInventoryProduct 是否存在清单产品]
     * @param  [type] $id [清单ID]
     * @param  [type] $productId [产品ID]
     * @return [type]      [description]
     */
    static function isExistInventoryProduct($id,$productId){
        if(!$id || !$productId) return false;
        //参数
        $id         = intval($id);
        $productId  = $productId  ?  intval($productId) : '';
        //排序
        $whereStr   = " invent_id = '$id' AND product_id = '$productId' ";

        $sql        = "SELECT id FROM {{%user_inventory_product}} WHERE $whereStr";
        $isExist    =  Yii::$app->db->createCommand($sql)->queryScalar();

        return $isExist ? true : false;
    }
    /**
     * [isExistInventory 是否存在清单]
     * @param  [type] $id [清单ID]
     * @param  [type] $productId [产品ID]
     * @return [type]      [description]
     */
    static function isExistInventory($uid,$title){
        if(!$uid || !$title) return false;
        //参数
        $uid        = intval($uid);
        $title      = Functions::checkStr($title);
        //排序
        $whereStr   = " user_id = '$uid' AND title = '$title' ";

        $sql        = "SELECT id FROM {{%user_inventory}} WHERE $whereStr";
        $isExist    =  Yii::$app->db->createCommand($sql)->queryScalar();

        return $isExist ? true : false;
    }
    /**
     * [addInventoryProduct 添加清单产品]
     * @param  [type] $id [清单ID]
     * @param  [type] $productId [产品ID]
     * @return [type]      [description]
     */
    static function addInventoryProduct($id,$productId,$desc){
        if(!$id || !$productId) return false;
        //参数
        $id             = intval($id);
        $productId      = intval($productId);
        $desc           = $desc ? Tools::userTextEncode($desc,1) : '';

        $inventoryInfo  = self::getInventory($id);
        if(!$inventoryInfo) return false;
        $productInfo    = self::getProductInfo($productId);
        if(!$productInfo) return false;
        //添加
        $userId         = $inventoryInfo['user_id'];
        $sql            = " INSERT INTO {{%user_inventory_product}} (`product_id`,`invent_id`,`desc`) 
                    VALUES('$productId','$id','$desc')";
        Yii::$app->db->createCommand($sql)->execute();
        //修改清单图
        $updateSql      = " UPDATE  {{%user_inventory}} SET picture = '$productInfo[product_img]' WHERE id = '$id'";
        Yii::$app->db->createCommand($updateSql)->execute();
        return true;
    }
    /**
     * [userInventoryList 用户清单列表]
     * @param  [type] $userId [用户ID]
     * @return [type]      [description]
     */
    static function userInventoryList($userId,$page = '1' ,$pageSize = '10'){
        if(!$userId) return false;
        //参数
        $id         = intval($userId);
        $pageSize   = intval($pageSize);
        $page       = intval($page);
        $pageMin    = ($page - 1) * $pageSize;

        $sql        =  " SELECT id,title,picture,add_time FROM {{%user_inventory}} 
                        WHERE user_id = '$userId' AND status = '1'  
                        ORDER BY id DESC
                        LIMIT $pageMin,$pageSize";

        $data       =  Yii::$app->db->createCommand($sql)->queryAll();

        foreach ($data as $key => $value) {
            $data[$key]['title']    = $value['title']   ? Tools::userTextDecode($value['title']) : '';
            $data[$key]['picture']  = $value['picture'] ? Functions::get_image_path($value['picture']) : '';
            $data[$key]['add_time'] = $value['add_time'] ? strtotime($value['add_time']) : '';
            $data[$key]['num']      = self::statisticsInventory($value['id']);
        }

        return $data;
    }
    /**
     * [updateInventoryPicture 操作用户清单图片]
     * @param  [type] $userId [清单详情id]
     * @return [type]      [description]
     */
    static function updateInventoryPicture($uip_id){
        if(!$uip_id) return false;
        //参数
        $id         = intval($uip_id);

        $sql        =  " SELECT P2.*,PD.product_img FROM {{%user_inventory_product}}  P1
                        LEFT JOIN {{%user_inventory_product}} P2 ON P1.invent_id = P2.invent_id
                        LEFT JOIN {{%product_details}} PD ON P2.product_id = PD.id
                        WHERE P1.id = '$uip_id'  
                        ORDER BY P2.order ASC,P2.add_time DESC";
        $data       =  Yii::$app->db->createCommand($sql)->queryAll();
        $invent_id  =  $data['0']['invent_id'];
        if(count($data) == 1){
            $field_str = '';
        }elseif($data['0']['id'] == $uip_id){
            $field_str = $data['1']['product_img'];
        }else{
            $field_str = $data['0']['product_img'];
        }

        if(isset($field_str)){
            $updateSql      = " UPDATE  {{%user_inventory}} SET picture = '$field_str' WHERE id = '$invent_id'";
            Yii::$app->db->createCommand($updateSql)->execute();
        }

        return ;
    }
    /**
     * [addInventory 添加清单]
     * @param  [type] $userId [用户ID]
     * @param  [type] $title [标题]
     * @param  [type] $picture [图片]
     * @return [type]      [description]
     */
    static function addInventory($userId,$title,$picture = '' ){
        if(!$userId || !$title) return ['status' => 0,'msg' => '参数不足'];
        //参数
        $userId  = intval($userId);
        $title   = $title ? Functions::checkStr($title) : '';
        $picture = $picture ? Functions::checkStr($picture) : '';

        $isExist = self::isExistInventory($userId,$title);
        if($isExist){
            return ['status' => 0,'msg' => '清单名重复'];
        }

        $sql     = " INSERT INTO {{%user_inventory}} (`user_id`,`title`,`picture`) 
                    VALUES('$userId','$title','$picture')";
        Yii::$app->db->createCommand($sql)->execute();
        $insertId= Yii::$app->db->getLastInsertId();
        return ['status' => 1,'msg' => $insertId];
    }
    /**
     * [statisticsInventory 统计清单产品个数]
     * @param  [type] $id [清单ID]
     * @return [type]      [description]
     */
    static function statisticsInventory($id){
        if(!$id) return 0;
        //参数
        $id     = intval($id);
        $sql    = "SELECT COUNT(*) FROM {{%user_inventory_product}} WHERE invent_id = '$id'";
        $num    = Yii::$app->db->createCommand($sql)->queryScalar();

        return $num ? $num : 0 ;
    }
    /**
     * [getProductInfo 产品详情]
     * @param  [type] $id [产品ID]
     * @return [type]      [description]
     */
    static function getProductInfo($id){
        if(!$id) return false;
        //参数
        $id     = intval($id);
        $sql    = "SELECT id,product_img,product_name FROM {{%product_details}} WHERE id = '$id' AND status = '1'";
        $info   = Yii::$app->db->createCommand($sql)->queryOne();
        return $info;
    }
    /**
     * [postList 话题帖列表]
     * @param  [type] $params [参数]
     * @return [type]         [description]
     */
    static function postList($params){
        $id         =   intval($params['id']);
        $userId     =   isset($params['userId']) ? intval($params['userId']) : 0;
        $orderBy    =   isset($params['orderBy']) ? intval($params['orderBy']) : 1;
        $page       =   isset($params['page']) ? intval($params['page']) : 1;
        $pageSize   =   isset($params['pageSize']) ? intval($params['pageSize']) : 10;

        $pageMin    =  ($page - 1) * $pageSize;
        $whereStr   =  "T.status = '1' AND topic_id = '$id'";
        switch ($orderBy) {
            case '1':
                $orderBy = 'T.created_at DESC';
                break;
            case '2':
                $orderBy = 'T.like_num DESC,T.created_at DESC';
                break;
            default:
                $orderBy = 'T.created_at DESC';
                break;
        }
        $selectSql  = " SELECT `id`,`user_id`,`topic_id`,`content`,`like_num`,`picture`,`ratio`,`created_at` FROM {{%post}} T
                        WHERE $whereStr  ORDER BY $orderBy LIMIT $pageMin,$pageSize";

        $rows       = Yii::$app->db->createCommand($selectSql)->queryAll();

        foreach ($rows as $key => $value) {
            $rows[$key]['user'] = [];
            $userInfo           = Functions::getUserInfo($value['user_id']);
            $rows[$key]['user']['user_id']      = $userInfo['id'];
            $rows[$key]['user']['username']     = $userInfo['username'];
            $rows[$key]['user']['user_img']     = $userInfo['img'];
            $attachment               = Functions::getPostAttachment($value['id']);
            $rows[$key]['picture']    = $attachment ? $attachment['0'] : '';
            $rows[$key]['isLike']     = Functions::postIsLike($userId,$value['id']) ? 1 : 0;
            unset($userInfo);
            unset($rows[$key]['user_id']);
        }
        return $rows;
    }
}