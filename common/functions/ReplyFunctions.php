<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\functions;
use Yii;
use common\models\Pms;
use common\models\Post;
use common\models\Comment;
use common\models\UserFeedback;
use common\models\PmsLog;
use common\models\Ask;
/**
 * Description of ReplyFunctions
 *
 * @author Administrator
 */
class ReplyFunctions {
    //ReplyFunctions::reply($userId,$commentId,ReplyFunctions::$REPLY_COMMENT, $postId,$comment);
    
    public static $REPLY_COMMENT        = 0;
    public static $REPLY_POST           = 1;
    public static $REPLY_LIKE_COMMENT   = 2;
    public static $REPLY_LIKE_POST      = 3;
    public static $USER_FEED_BACK       = 4;
    public static $USER_ASK             = 5;
    public static $USER_ASK_REPLY       = 6;
    public static $REPLY_OTHER_COMMENT  = 7;
    public static $USER_COMMUNICATE     = 8;
    public static $USER_ASK_REPLY_LIKE  = 9;
    public static $POST_LIKE  = 10;
    
    // 通知的入口方法。
    public static function reply($userId,$relation_id,$type,$message = '',$receive_id = '',$log_id = ''){
        $pms = new Pms();
        $data = [];

        $data['status']         = 0;
        $data['from_id']        = $userId;
        $data['type']           = $type;
        $data['relation_id']    = $relation_id;
        $data['message']        = ($type == 4 || 8) ? $message : mb_substr($message, 0, 100, "UTF-8");
        $data['log_id']         = $log_id;

        switch ($type) {
            case self::$REPLY_COMMENT:
                $data['receive_id'] = self::getComment($relation_id)['receive_id'];
                break;
            // case self::$REPLY_POST:
            //     $data = self::getPost($relation_id);
            //     break;
            case self::$REPLY_LIKE_COMMENT:
                $data['receive_id'] = self::getComment($relation_id)['receive_id'];
                $isExsit = self::isExsit($userId,$data['receive_id'],$relation_id,self::$REPLY_LIKE_COMMENT);//判断是否重复点赞
                if($isExsit) return;
                break;
            // case self::$REPLY_LIKE_POST:
            //     $data = self::getPost($relation_id);
            //     break;
            case self::$USER_FEED_BACK:
                $data['receive_id'] = self::getUserFeedback($relation_id)['receive_id'];
                break;
            case self::$USER_ASK:
                $data['receive_id'] = $receive_id;
                $data['created_at'] = time();
                self::addPmsLog($data);
                break;
            case self::$USER_ASK_REPLY:
                $data['receive_id'] = self::getAsk($relation_id)['receive_id'];
                break; 
            case self::$REPLY_OTHER_COMMENT:
                $relation_info = self::getComment($relation_id);
                $data['receive_id'] = $relation_info['receive_id'];
                $data['relation_id']= $relation_info['first_id'];
                break;  
            case self::$USER_COMMUNICATE:
                $data['receive_id'] = $receive_id;
                break;
            case self::$USER_ASK_REPLY_LIKE:
                $isExsit = self::isExsit($userId,$receive_id,$relation_id,self::$USER_ASK_REPLY_LIKE,$log_id);//判断是否重复点赞
                if($isExsit) return;
                $data['receive_id'] = $receive_id;
                break;
            case self::$POST_LIKE:
                $data['receive_id'] = $receive_id;
                break;
            case self::$REPLY_POST:
                $relation_info = self::getPost($relation_id);
                $data['receive_id'] = $relation_info['receive_id'];
                break;
            default:
                break;
        }

        if($data['receive_id'] == $data['from_id'])  return;//自己给自己操作的不用放在消息列表

        $pms->setAttributes($data);
        $pms->save();
    }
    
    private static function getAsk($id){
        $Ask = Ask::findOne($id);
        return [
            'receive_id' => $Ask->user_id,
        ];
    }

    private static function getComment($id){
        $comment = Comment::findOne($id);
        return [
            'receive_id' => $comment->user_id,
            'first_id' => $comment->first_id ? $comment->first_id : $id,
        ];
    }
    private static function getUserFeedback($id){
        $feedback = UserFeedback::findOne($id);
        return [
            'receive_id' => $feedback->user_id,
        ];
    }
    private static function getPost($id){
        $post = Post::findOne($id);
        return [
            'receive_id' => $post->user_id,
        ];
    }
    private static function addPmsLog($data){
        $PmsLog = new PmsLog();
        $PmsLog->setAttributes($data);
        $PmsLog->save(); 
        return ;
    }
    //对应帖子或者评论的点赞是否已读
    public static function isRead($userId,$postId,$type){

        $sql    = "SELECT id FROM {{%pms}} WHERE reply_id = '$postId' AND receive_id = '$userId' AND type = '$type' AND status = 0"; 
        $status = Yii::$app->db->createCommand($sql)->queryAll();
        
        if($status){
            return false;
        }else{
            return true;
        }
    }

    //对应帖子或者评论的点赞是否已读
    public static function isExsit($userId,$receiveId,$relationId,$type,$log_id=0){
        if(!$userId || !$receiveId || !$relationId || !$type) return true;
        $str = empty($log_id) ? 'relation_id = '.$relationId : 'log_id = '.$log_id;
        $whereStr = "from_id = '$userId' AND receive_id = '$receiveId' AND '$str' AND type = '$type'";
        $sql    = "SELECT id FROM {{%pms}} WHERE $whereStr"; 
        $status = Yii::$app->db->createCommand($sql)->queryAll();
        
        if($status){
            $del_sql    = "delete FROM {{%pms}} WHERE $whereStr"; 
            Yii::$app->db->createCommand($del_sql)->execute();
            return true;
        }else{
            return false;
        }
    }

}
