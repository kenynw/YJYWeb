<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\functions;
require_once '../../vendor/jpush/autoload.php';

use common\models\NoticeUser;
use common\models\Notice;
use common\models\Post;
use common\models\ScoreConfig;
use common\models\Group;
use common\models\Comment;
use common\models\NoticeSystem;
use Yii;
use common\models\ProductDetails;

/**
 * Description of NoticeFunctions
 *
 * @author Administrator
 */
class NoticeFunctions
{

    public static $SIGN_UP = 1;
    public static $ADD_STAR = 3;
//     public static $ADD_TOP = 3;
//     public static $REWARD = 4;
//     public static $PUNISHMENT = 5;
//     public static $GROUPER = 6;
//     public static $LEVEL_1 = 11;
//     public static $LEVEL_2 = 12;
//     public static $LEVEL_3 = 13;
//     public static $LEVEL_4 = 14;
//     public static $LEVEL_5 = 15;
//     public static $LEVEL_6 = 16;
//     public static $LEVEL_7 = 17;
//     public static $LEVEL_8 = 18;
//     public static $LEVEL_9 = 19;
    public static $PUNISH_HEAD = 21;
//     public static $SHUTUP_1_DAY = 22;
//     public static $SHUTUP_3_DAY = 23;
    public static $SHUTUP_7_DAY = 24;
//     public static $SHUTUP_15_DAY = 25;
    public static $KICK = 26;
//     public static $POST_DELETED = 27;
    public static $PRODUCT_COMMENT_DELETED = 28;
    public static $ARTICLE_COMMENT_DELETED = 29;
    public static $PRODUCT_OUTDATE = 30;
    public static $ASK_REPLY = 31;
    public static $VIDEO_COMMENT_DELETED = 32;
    public static $HUODONG   = 99;

    // 通过typeid分类通知类型
    private static $typeCat = [
        //'Default'     => [1, 11, 12, 13, 14, 15, 16, 17, 18, 19, 21, 23, 24, 25, 26],
        'overdueTime'   => [2],
        //'PostMoney'   => [4],
        //'Post'        => [27],
        //'GroupMoney'  => [5],
        'Default'       => [1, 21, 24, 26],
        'Comment'       => [28,29,32],
        'Product'       => [30],
        'Ask'           => [31],
        'CommentScore'  => [3],
        'Huodong'       => [99],
    ];

    // 通知的入口方法。
    public static function notice($userId, $type, $id = 0,$relation_id = 0)
    {
        // 通过type获取方法。
        $func = 'get' . self::getType($type);
        // 获取通知信息
        $message = self::getNoticeMessage($type, self::$func($id, $type));
        // 保存通知信息
        self::save([
            'user_id' => $userId,
            'type' => $type,
            'content' => $message,
            'relation_id' => $relation_id,
        ]);
    }

    // 保存通知消息
    private static function save($data)
    {
        $notice_user = new NoticeUser();
        $notice_user->setAttributes($data);
        $notice_user->status = 0;
        if (!$notice_user->save()) {
            throw new \yii\db\IntegrityException('通知信息插入错误');
        }
    }

    // 获取通知的类型
    private static function getType($type)
    {
        foreach (self::$typeCat as $key => $types) {
            if (in_array($type, $types)) {
                return $key;
                break;
            }
        }
        throw new \yii\web\BadRequestHttpException('错误的通知类型');
    }

    // 获取通知参数的方法。
    private static function getDefault($id)
    {
        return [];
    }

    // 获取通知参数的方法。
    private static function getOverdueTime($id, $type)
    {
        Yii::info('getOverdueTime');

        $time           = time();
        $selectSql      = "SELECT * FROM {{%user_product}}  WHERE id = '$id'";
        $productInfo    = Yii::$app->db->createCommand($selectSql)->queryOne();
        $overdueTime    = floor(($productInfo['overdue_time'] - $time)/(24*3600));

        return [
            'product'     => mb_substr($productInfo['product'], 0, 10, "UTF-8"),
            'overdueTime' => $overdueTime,
        ];
    }

    // 获取通知参数的方法。
    private static function getCommentScore($id)
    {
        Yii::info('getCommentScore');
        $Comment = self::findModel(Comment::className(), $id);
        $product = ProductDetails::findOne($Comment->post_id);
        return [
            'product' => $product->product_name,
        ];
    }

    // 获取通知参数的方法。
    private static function getComment($id)
    {
        Yii::info('getComment');
        $comment = self::findModel(Comment::className(), $id);
        return [
            'post' => mb_substr($comment->comment, 0, 10, "UTF-8"),
        ];
    }
    
    // 获取通知参数的方法。
    private static function getProduct($id)
    {
        Yii::info('getProduct');
        $product = self::findModel(ProductDetails::className(), $id);
        return [
            'product' => $product->product_name,
        ];
    }

    // 获取通知参数的方法。
    private static function getAsk($id)
    {
        Yii::info('getAsk');
        $Ask = self::findModel(Ask::className(), $id);
        return [
            'subject' => $Ask->subject,
        ];
    }

    // 获取通知参数的方法。
    private static function getGroupMoney($id, $type)
    {
        Yii::info('getGroupMoney');
        $group = self::findModel(Group::className(), $id);
        $config = self::findModel(ScoreConfig::className(), ['type' => $type]);
        return [
            'group' => $group->name,
            'money' => $config->money,
        ];
    }
    // 获取活动标题的方法。
    private static function getHuodong($id)
    {
        Yii::info('getHuodong');
        $selectSql      = "SELECT notice FROM {{%huodong_special_config}}  WHERE id = '$id'";
        $notice         = Yii::$app->db->createCommand($selectSql)->queryScalar();

        return [
            'notice' => $notice,
        ];
    }
    // 获取通知信息。
    private static function getNoticeMessage($type, $params = [])
    {
        // 参数必须为数组
        if (!is_array($params)) {
            throw new \yii\web\NotFoundHttpException('添加通知错误');
        }
        $content = self::findModel(Notice::className(), ['type' => $type])->content;
        $message = '';
        // 参数为空，则不需要替换。反之，替换通知信息
        if (count($params) != 0) {
            $replace = array_values($params);
            $search = array_map(function ($param) {
                return '{$' . $param . '}';
            }, array_keys($params));
            $message = str_replace($search, $replace, $content);
        } else {
            $message = $content;
        }

        return $message;
    }

    // 查询。
    private static function findModel($modelName, $where)
    {
        if (($model = $modelName::findOne($where)) !== null) {
            return $model;
        } else {
            throw new \yii\web\NotFoundHttpException("NoticeFunctions请求通知错误：".$modelName."无找到或查询出错");
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
    public static function JPushOne($param = [])
    {
        $production         =   Yii::$app->params['environment'] == 'Production' ? true : false;

        $param['Alias']     =   (string)$param['Alias'];
        $param['type']      =   (string)$param['type'];
        $param['id']        =   (string)$param['id'];
        $param['option']    =   (string)$param['option'];
        $param['relation']  =   isset($param['relation']) ? (string)$param['relation'] : '';

        //系统通知
        $content['comment_delete'] = '亲，不好意思，由于你的评论不符合“她们说”社区规范，评论已被删，下次评论注意咯~~';//评论被删除
        $content['overdueProduct'] = 'xxx';
        $content['upgrade']        = '恭喜您，成功变身xxx。新的一级有更多特权，快来看看吧~，点击打开个人中心。';//升级（xxx为等级名称）
        $content['imgDisable']     = '妹子，你的头像涉嫌违规，已被禁用了，请更换头像~';//头像被禁用
        $content['digest']            = '恭喜！您关于产品xxx的评论被选为精华评论，颜值分增加50分！';//评论被加精
        //消息通知
        $content['comment_likes']  = '有人给你的评论点赞了哦~，点击打开消息通知。';//评论被点赞
        $content['comment_reply']  = '有人回复了你的评论，还不快点开看看？点击打开消息通知。';//评论被回复
        $content['feedback']       = '回复了你的反馈：xxx';//反馈被回复
        $content['ask']            = '您提问的xxx，已有用户回答咯，点击查看吧';//问题被评论
        $content['communicate']       = 'xxx';

        $token  = Functions::getUserToken($param['Alias']);
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
            $client = new \JPush\Client(Yii::$app->params['Jpush']['app_key'], Yii::$app->params['Jpush']['master_secret']);
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

    /**
    * 推送所有人接口
    * @para array  $param     参数
    * @para str    content    推送文字
    * @para int    id         ID
    * @para string type       1为普通帖，2为投票帖，3为用户,4为系统通知
    * @return 操作成功，失败返回错误
    */
    // 极光推送。NoticeFunctions::JPushAll(['content'=>'推送的内容','id'=>'测试id','type'=>'1']);
    public static function JPushAll($param = [])
    {
        $production   =   Yii::$app->params['environment'] == 'Production' ? true : false;
        $environment  =   $production ? 'Production' : 'Development';

        $id      = intval($param['id']);
        $content = (string)$param['content'];

        $noticeInfo = NoticeSystem::findOne($id)->toArray();
        $title      = $noticeInfo['title'] ? $noticeInfo['title'] : '颜究院';
        if(!$noticeInfo || !$content){
            return false;
        }
        //参数
        $extras = [
            'id'        => $noticeInfo['id'],
            'type'      => $noticeInfo['type'],
            'relation'  => $noticeInfo['relation'],
        ];
        $message = array(
            'title'         => $title,
            'content_type'  => 'text',
            'extras'        => $extras
        );
        $iosParams  = [
            'title'  => $title,
            'body'   => $content
        ];
        try {
            $client = new \JPush\Client(Yii::$app->params['Jpush']['app_key'], Yii::$app->params['Jpush']['master_secret']);
            $client->push()
                ->setPlatform('all')
                //->addAllAudience()//推送所有用户
                ->addTag($environment)
                ->setNotificationAlert($content)
                ->iosNotification($iosParams, array(
                    'content-available' => true
                ))
                ->androidNotification($content, array(
                    'title'  => $title
                ))
                ->message($content, $message)
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
            // try something here
            return $e;
        }
        return 'OK';
    }
}
